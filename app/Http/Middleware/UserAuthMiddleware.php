<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\Log;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Wikimedia\IPSet;

class UserAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = PermissionsCenter::getUserAuthKey($request->bearerToken());

        if (!$key) {
            return response()->json(MessagesCenter::E403(), 403);
        }

        if ($key->expires_at != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($key->expires_at))) {
            return response()->json(MessagesCenter::E403(), 403);
        }

        $ipSet = new IPSet($key->whitelist_range);
        if (!empty($personalKeys->whitelist_range) && !$ipSet->match($request->getClientIp())) {
            return response()->json(MessagesCenter::E403(), 403);
        }

        $requestsMadeCount = $key->logs()->whereMonth('created_at', Carbon::now()->month)->count();

        if ($key->max_count != -1 && $requestsMadeCount >= $key->max_count) {
            return response()->json(MessagesCenter::E429(), 429);
        }

        $randomRayID = Str::uuid();

        $log = [
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ];

        Log::query()->create([
            'personal_token_id' => $key->id,
            'request_id' => $randomRayID,
            'request_info' => $log,
            'access_ip' => $request->getClientIp()
        ]);

        $response = $next($request);
        $response->header('X-Request-ID', $randomRayID);

        return $response;
    }
}
