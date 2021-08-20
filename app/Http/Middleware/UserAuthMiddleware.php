<?php

namespace App\Http\Middleware;

use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RandomLib\Factory;
use SecurityLib\Strength;

class UserAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $personalKeys = PersonalKeys::query()->where('key', $request->bearerToken())->first();

        if (empty($personalKeys)) {
            return response()->json([
                'error' => [
                    'type' => 'xInvalidToken',
                    'info' => 'No token was specified or an invalid token was specified.'
                ]
            ], 403);
        }

        $logs = Logs::query()->where('key_id', $personalKeys->id)->whereMonth('created_at', Carbon::now()->month)->get()->toArray();

        if ($personalKeys->expires_at != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($personalKeys->expires_at))) {
            return response()->json([
                'error' => [
                    'type' => 'xSubscriptionExpired',
                    'info' => 'Your subscription is already expired. Please renew or upgrade your plan.'
                ]
            ], 403);
        }

        if ($personalKeys->max_count != -1 && count($logs) >= $personalKeys->max_count) {
            return response()->json([
                'error' => [
                    'type' => 'xUsageLimitReached',
                    'info' => 'The maximum allowed amount of monthly API requests has been reached. Please upgrade your plan.'
                ]
            ], 429);
        }

        $randomRayID = Str::uuid();

        $log = [
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ];

        Logs::query()->create([
            'key_id' => $personalKeys->id,
            'request_id' => $randomRayID,
            'request_info' => $log,
            'access_ip' => $request->getClientIp()
        ]);

        $response = $next($request);
        $response->header('X-Request-ID', $randomRayID);

        return $response;
    }
}
