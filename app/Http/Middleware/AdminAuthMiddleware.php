<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Models\AccessKeys;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Wikimedia\IPSet;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        $accessKey = AccessKeys::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token){
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();

        if (empty($accessKey)) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $clientIPRange = $this->checkIPRange($request->getClientIp(), $accessKey->whitelist_range);

        if ($clientIPRange === FALSE) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        return $next($request);
    }

    protected function checkIPRange($ip, $range)
    {
        if (empty($range)) {
            return true;
        }

        $ipSet = new IPSet($range);

        return $ipSet->match($ip);
    }
}
