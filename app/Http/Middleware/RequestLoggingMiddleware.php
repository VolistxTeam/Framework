<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\AdminLogRepository;
use App\Repositories\UserLogRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Wikimedia\IPSet;

class RequestLoggingMiddleware
{
    private AdminLogRepository $adminLogRepository;
    private UserLogRepository $userLogRepository;

    public function __construct(AdminLogRepository $adminLogRepository,UserLogRepository $userLogRepository)
    {
        $this->adminLogRepository = $adminLogRepository;
        $this->userLogRepository = $userLogRepository;
    }


    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }


    public function terminate(Request $request, JsonResponse $response)
    {
        $header= (array) $request->header();
        unset($header['authorization']);

        if($request->input('X-PERSONAL-TOKEN')){
            $this->userLogRepository->Create($request->input('X-PERSONAL-TOKEN')->id,[
                'url' => $request->fullUrl(),
                'request_method' =>$request->method(),
                'request_body' => $request->getContent(),
                'request_header' =>json_encode($header) ,
                'ip' =>$request->ip(),
                'response_code' =>$response->getStatusCode(),
                'response_body'=>$response->getContent(),
            ]);
        }
        else if($request->input('X-ACCESS-TOKEN')){
            $this->adminLogRepository->Create($request->input('X-ACCESS-TOKEN')->id,[
                'url' => $request->fullUrl(),
                'request_method' =>$request->method(),
                'request_body' => $request->getContent(),
                'request_header' =>json_encode($header) ,
                'ip' =>$request->ip(),
                'response_code' =>$response->getStatusCode(),
                'response_body'=>$response->getContent(),
            ]);
        }
    }
}
