<?php

namespace App\Http\Middleware;

use App\Classes\Facades\Messages;
use Closure;
use Illuminate\Http\Request;

class JsonBodyValidationFilteringMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isJson()) {
            return response()->json(Messages::E400(), 400);
        }

        return $next($request);
    }
}