<?php

namespace App\Http\Middleware\Auth;

use App\Facades\Messages;
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
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'subscription_id' => $request->X_PERSONAL_TOKEN->subscription()->first()->id
            ];

            if (config('log.userLogMode') === 'local') {
                $this->logUserToLocalDB( $inputs);
            } else {
                $this->logUserToRemoteDB($inputs);
            }
        }
        else if ($request->X_ACCESS_TOKEN) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'access_token_id' => $request->X_ACCESS_TOKEN->id
            ];
            if (config('log.adminLogMode') === 'local') {
                $this->logAdminToLocalDB($inputs);
            } else {
                $this->logAdminToRemoteDB($inputs);
            }
        }
    }

    private function logUserToLocalDB($inputs)
    {
        $this->userLogRepository->Create($inputs);
    }

    private function logUserToRemoteDB($inputs)
    {
        $httpURL = config('log.userLogHttpUrl');
        $token = config('log.userLogHttpToken');

        $client = new Client();
        $request = $client->post($httpURL, [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => "application/json"
            ],
            'body' => json_encode($inputs)
        ]);


        if ($request->getStatusCode() != 201) {
            //WE SEE WHAT WE DO
        }
    }

    private function logAdminToLocalDB($inputs)
    {
        $this->adminLogRepository->Create($inputs);
    }

    private function logAdminToRemoteDB($inputs)
    {
        $httpURL = config('log.adminLogHttpUrl');
        $token = config('log.adminLogHttpToken');

        $client = new Client();
        $request = $client->post($httpURL, [
            'headers' => [
                'Authorization' => "Bearer $token",
                'Content-Type' => "application/json"
            ],
            'body' => json_encode($inputs)
        ]);

        if ($request->getStatusCode() != 201) {
            //WE SEE WHAT WE DO
        }
    }
}
