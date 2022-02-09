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
                $this->logUserToLocalDB($request->X_PERSONAL_TOKEN, $inputs);
            } else {
                $this->logUserToRemoteDB($request->X_PERSONAL_TOKEN, $inputs);
            }
        } else if ($request->X_ACCESS_TOKEN) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
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
                    'Authorization' => "Bearer $token",
                    'Content-Type' => "application/json"
                ],
                'body' => json_encode($inputs)
            ]);

            if ($request->getStatusCode() != 201) {
                response()->json(Messages::E500(), 500)->send();
                exit();
            }
        } catch (Exception $ex) {
            response()->json(Messages::E500(), 500)->send();
            exit();
        }

        //Handle failure to log remotely, currently, it logs locally

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
                    'Authorization' => "Bearer $token",
                    'Content-Type' => "application/json"
                ],
                'body' => json_encode($inputs)
            ]);

            if ($request->getStatusCode() != 201) {
                response()->json(Messages::E500(), 500)->send();
                exit();
            }
        } catch (Exception $ex) {
            response()->json(Messages::E500(), 500)->send();
            exit();
        }
    }
}
