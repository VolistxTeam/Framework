<?php

namespace App\Http\Middleware\Auth;

use App\Repositories\Auth\AdminLogRepository;
use App\Repositories\Auth\UserLogRepository;
use Closure;
use Exception;
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


        if ($request->X_PERSONAL_TOKEN) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' =>  $_SERVER['HTTP_USER_AGENT']?? null,
                'personal_token_id' => $request->X_PERSONAL_TOKEN->id
        ];

            if (config('log.userLogMode') === 'local') {
                $this->logUserToLocalDB($request->X_PERSONAL_TOKEN, $inputs);
            } else {
                $this->logUserToRemoteDB($request->X_PERSONAL_TOKEN, $inputs);
            }
        } else if ($request->X_ACCESS_TOKEN) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' =>  $_SERVER['HTTP_USER_AGENT']?? null,
                'access_token_id' => $request->X_ACCESS_TOKEN->id
        ];
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

        try {
            $client = new Client();
            $request = $client->post($httpURL, [
                'headers' => [
                    'Authorization' =>"Bearer $token",
                    'Content-Type' => "application/json"
                ],
                'body' => json_encode($inputs)
            ]);
        } catch (Exception $ex){
            $this->logUserToLocalDB($key, $inputs);
        }

        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode() != 201) {
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

        try {
            $client = new Client();
            $request = $client->post($httpURL, [
                'headers' => [
                    'Authorization' =>"Bearer $token",
                    'Content-Type' => "application/json"
                ],
                'body' => json_encode($inputs)
            ]);
        } catch (Exception $ex){
            $this->logAdminToLocalDB($key, $inputs);
        }
        //Handle failure to log remotely, currently, it logs locally
        if ($request->getStatusCode() != 201) {
            $this->logAdminToLocalDB($key, $inputs);
        }
    }
}
