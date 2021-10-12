<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use Closure;
use Illuminate\Http\Request;
use Wikimedia\IPSet;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $accessKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (empty($accessKey)) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        $clientIPRange = $this->checkIPRange($request->getClientIp(), $accessKey->whitelist_range);

        if ($clientIPRange === FALSE) {
            return response()->json(MessagesCenter::Error('xInvalidToken', 'Invalid token was specified or do not have permission.'), 403);
        }

        return $next($request);
    }

    protected function checkIPRange($ip, $range): bool
    {
        if (empty($range)) {
            return true;
        }

        $ipSet = new IPSet($range);

        return $ipSet->match($ip);
    }
}
