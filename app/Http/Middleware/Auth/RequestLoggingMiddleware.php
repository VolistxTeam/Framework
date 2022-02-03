<?php

namespace App\Http\Middleware\Auth;

use App\Repositories\Auth\AdminLogRepository;
use App\Repositories\Auth\UserLogRepository;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function config;

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

        if ($request->X_PERSONAL_TOKEN) {
            if (config('log.userLogMode') === 'local') {
                $this->logUserToLocalDB($request->X_PERSONAL_TOKEN, $inputs);
            } else {
                $this->logUserToRemoteDB($request->X_PERSONAL_TOKEN, $inputs);
            }
        } else if ($request->X_ACCESS_TOKEN) {
            if (config('log.adminLogMode') === 'local') {
                $this->logAdminToLocalDB($request->X_ACCESS_TOKEN, $inputs);
            } else {
                $this->logAdminToRemoteDB($request->X_ACCESS_TOKEN, $inputs);
            }
        }
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
            'headers' => ['Authorization' => 'Bearer ' . $token],
            'body' => json_encode($inputs)
        ]);

        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode() != 200) {
            $this->logUserToLocalDB($key, $inputs);
        }
    }

    private function logAdminToLocalDB($key, $inputs)
    {
        $this->adminLogRepository->Create($key->id, $inputs);
    }

    private function logAdminToRemoteDB($key, $inputs)
    {
        $httpURL = config('log.adminLogHttpUrl');
        $token = config('log.adminLogHttpToken');

        $client = new Client();
        $request = $client->post($httpURL, [
            'headers' => ['Authorization' => $token],
            'body' => json_encode($inputs)
        ]);

        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode() != 200) {
            $this->logAdminToLocalDB($key, $inputs);
        }
    }
}
