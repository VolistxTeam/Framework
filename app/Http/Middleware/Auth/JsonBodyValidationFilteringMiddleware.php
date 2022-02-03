<?php

namespace App\Http\Middleware\Auth;

use App\Facades\Messages;
use Closure;
use Illuminate\Http\Request;
use function response;

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