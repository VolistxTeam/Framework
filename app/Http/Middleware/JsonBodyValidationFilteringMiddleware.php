<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use Closure;
use Illuminate\Http\Request;

class JsonBodyValidationFilteringMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isJson()) {
            return response()->json(MessagesCenter::E400(), 400);
        }

        return $next($request);
    }
}