<?php

namespace App\Http\Middleware\Auth;

use App\Facades\Messages;
use App\Repositories\Auth\Interfaces\IAdminLogRepository;
use App\Repositories\Auth\LocalAdminLogRepository;
use App\Repositories\Auth\UserLogRepository;
use Closure;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function config;

class RequestLoggingMiddleware
{
    private IAdminLogRepository $adminLogRepository;
    private UserLogRepository $userLogRepository;

    public function __construct(IAdminLogRepository $adminLogRepository, UserLogRepository $userLogRepository)
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
            $this->userLogRepository->Create($inputs);
        } else if ($request->X_ACCESS_TOKEN) {
            $inputs = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'access_token_id' => $request->X_ACCESS_TOKEN->id
            ];
            $this->adminLogRepository->Create($inputs);
        }
    }
}
