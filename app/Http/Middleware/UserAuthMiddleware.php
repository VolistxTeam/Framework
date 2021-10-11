<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Wikimedia\IPSet;

class UserAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        $key = PersonalKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v,$k) use ($token){
                return Hash::check(substr($token, 32), $v->secret);
            })->first();

        if (!$key) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $logs = Logs::query()->where('key_id', $key->id)->whereMonth('created_at', Carbon::now()->month)->get()->toArray();

        if ($key->expires_at != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($key->expires_at))) {
            return response()->json(MessagesCenter::Error('xSubscriptionExpired', 'Your subscription is already expired. Please renew or upgrade your plan.'), 403);
        }

        if ($key->max_count != -1 && count($logs) >= $key->max_count) {
            return response()->json(MessagesCenter::Error('xUsageLimitReached', 'The maximum allowed amount of monthly API requests has been reached. Please upgrade your plan.'), 429);
        }

        $ipSet = new IPSet($key->whitelist_range);
        if (!$ipSet->match($request->getClientIp())) {
            return response()->json(MessagesCenter::Error('xUserFirewallBlocked', 'This IP is not listed on a whitelist IP list.'), 403);
        }

        $randomRayID = Str::uuid();

        $log = [
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ];

        Logs::query()->create([
            'key_id' => $key->id,
            'request_id' => $randomRayID,
            'request_info' => $log,
            'access_ip' => $request->getClientIp()
        ]);

        $response = $next($request);
        $response->header('X-Request-ID', $randomRayID);

        return $response;
    }
}
