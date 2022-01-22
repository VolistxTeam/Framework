<?php

namespace App\Http\Middleware;

use App\Repositories\AdminLogRepository;
use App\Repositories\UserLogRepository;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Symfony\Component\HttpFoundation\Response;

class RequestLoggingMiddleware
{
    private AdminLogRepository $adminLogRepository;
    private UserLogRepository $userLogRepository;

    public function __construct(AdminLogRepository $adminLogRepository, UserLogRepository $userLogRepository)
    {
        $this->adminLogRepository = $adminLogRepository;
        $this->userLogRepository = $userLogRepository;
    }


    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }


    public function terminate(Request $request, Response $response)
    {

        $header = (array)$request->header();
        unset($header['authorization']);

        $inputs = [
            'url' => $request->fullUrl(),
            'request_method' => $request->method(),
            'request_body' => $request->getContent(),
            'request_header' => json_encode($header),
            'ip' => $request->ip(),
            'response_code' => $response->getStatusCode(),
            'response_body' => $response->getContent(),
        ];


        if ($request->input('X-PERSONAL-TOKEN')) {
            if (config('log.userLogMode') === 'local') {
                $this->logUserToLocalDB($request->input('X-PERSONAL-TOKEN'), $inputs);
            } else {
                $this->logUserToRemoteDB($request->input('X-PERSONAL-TOKEN'), $inputs);
            }
        } else if ($request->input('X-ACCESS-TOKEN')) {
            if (config('log.adminLogMode') === 'local') {
                $this->logAdminToLocalDB($request->input('X-ACCESS-TOKEN'), $inputs);
            } else {
                $this->logAdminToRemoteDB($request, $response);
            }
        }
    }

    private function logAdminToLocalDB($key, $inputs)
    {
        $this->adminLogRepository->Create($key->id, $inputs);
    }

    private function logUserToLocalDB($key, $inputs)
    {
        $this->userLogRepository->Create($key->id, $inputs);
    }

    private function logUserToRemoteDB($key, $inputs)
    {
        $httpURL = config('log.userLogHttpUrl');
        $token = config('log.userLogHttpToken');

        $client = new Client();
        $request = $client->post($httpURL, [
            'headers'=> ['Authorization' => $token],
            'body' => json_encode($inputs)
        ]);

        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode()!= 200){
            $this->logUserToLocalDB($key,$inputs);
        }
    }

    private function logAdminToRemoteDB($key, $inputs)
    {
        $httpURL = config('log.adminLogHttpUrl');
        $token = config('log.adminLogHttpToken');

        $client = new Client();
        $request = $client->post($httpURL, [
            'headers'=> ['Authorization' => $token],
            'body' => json_encode($inputs)
        ]);

        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode()!= 200){
            $this->logAdminToLocalDB($key,$inputs);
        }
    }
}
