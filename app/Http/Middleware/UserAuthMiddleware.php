<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\AdminLog;
use App\Repositories\AdminLogRepository;
use App\Repositories\UserLogRepository;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Wikimedia\IPSet;

class UserAuthMiddleware
{
    private UserLogRepository $logRepository;
    public function __construct(UserLogRepository $logRepository)
    {
        $this->logRepository= $logRepository;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = PermissionsCenter::getUserAuthKey($request->bearerToken());

        if (!$key || ($key->expires_at && Carbon::now()->greaterThan(Carbon::createFromTimeString($key->expires_at)))) {
            return response()->json(MessagesCenter::E403(), 403);
        }
//TODO: IP CHECKS
//        $ipSet = new IPSet($key->whitelist_range);
//        if (!empty($key->whitelist_range) && !$ipSet->match($request->getClientIp())) {
//            return response()->json(MessagesCenter::E403("Not allowed in your location"), 403);
//        }

//TODO: REQUESTS CHECKS
//        $requestsMadeCount = $this->logRepository->FindLogsBySubscriptionCount($key->subscription()->get->id,Carbon::now());
//        $planRequestsLimit = $key->subscription()->get()->plan()->get()->requests;
//        if ($planRequestsLimit != -1 && $requestsMadeCount >= $planRequestsLimit) {
//            return response()->json(MessagesCenter::E429(), 429);
//        }

        $request->merge([
            'X-PERSONAL-TOKEN'=>$key,
        ]);

      return $next($request);
    }
}
