<?php

namespace App\Http\Middleware;

use App\Classes\MessagesCenter;
use App\Classes\PermissionsCenter;
use App\Models\Log;
use App\Repositories\LogRepository;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Wikimedia\IPSet;

class UserAuthMiddleware
{
    private LogRepository $logRepository;
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository= $logRepository;
    }

    public function handle(Request $request, Closure $next)
    {
        $key = PermissionsCenter::getUserAuthKey($request->bearerToken());

        if (!$key || ($key->expires_at && Carbon::now()->greaterThan(Carbon::createFromTimeString($key->expires_at)))) {
            return response()->json(MessagesCenter::E403(), 403);
        }

        $ipSet = new IPSet($key->whitelist_range);
        if (!empty($personalKeys->whitelist_range) && !$ipSet->match($request->getClientIp())) {
            return response()->json(MessagesCenter::E403(), 403);
        }


        $requestsMadeCount = $this->logRepository->FindLogsBySubscriptionCount($key->subscription()->get->id,Carbon::now());
        $planRequestsLimit = $key->subscription()->get()->plan()->get()->requests;
        if ($planRequestsLimit != -1 && $requestsMadeCount >= $planRequestsLimit) {
            return response()->json(MessagesCenter::E429(), 429);
        }

        //TO CHECK HOW TO WRITE LOGS LATER WITH CRYENTAL
        $log = [
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
            'body' => $request->all()
        ];

        $this->logRepository->Create($key->id,[
            'personal_token_id' => $key->id,
            'key' => "key",
            'value' =>"value",
            'type'=>"type"
        ]);

      return $next($request);
    }
}
