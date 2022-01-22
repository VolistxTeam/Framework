<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Repositories\AdminLogRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Wikimedia\IPSet;

class AdminAuthMiddleware
{
    private AdminLogRepository $logRepository;

    public function __construct(AdminLogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }


    public function handle(Request $request, Closure $next)
    {
        $accessKey = PermissionsCenter::getAdminAuthKey($request->bearerToken());

        if (empty($accessKey)) {
            return response()->json(MessagesCenter::E403(), 403);
        }

        $clientIPRange = $this->checkIPRange($request->getClientIp(), $accessKey->whitelist_range);

        if ($clientIPRange === FALSE) {
            return response()->json(MessagesCenter::E403("Not allowed in your location"), 403);
        }

        $request->merge([
            'X-ACCESS-TOKEN'=> $accessKey,
        ]);
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
