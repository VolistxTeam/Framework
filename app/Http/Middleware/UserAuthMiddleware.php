<?php

namespace App\Http\Middleware;

use App\Classes\PermissionsCenter;
use App\Classes\ValidationRules\IPValidationRule;
use App\Classes\ValidationRules\KeyValidationRule;
use App\Classes\ValidationRules\RateLimitValidationRule;
use App\Classes\ValidationRules\RequestsCountValidationRule;
use Closure;
use Illuminate\Http\Request;

class UserAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = PermissionsCenter::getUserAuthKey($request->bearerToken());

        //prepare inputs array
        $inputs = [
            'request' => $request,
            'token' => $key,
        ];

        //add extra validators in the required order.
        //To be refactored to detect all classes with a base of ValidationRuleBase and create instance of them passing parameters, and ordering them by id
        $validators = [
            new KeyValidationRule($inputs),
            new IPValidationRule($inputs),
            new RequestsCountValidationRule($inputs),
            new RateLimitValidationRule($inputs)
        ];

        foreach ($validators as $validator) {
            $result = $validator->validate();
            if ($result !== true) {
                return response()->json($result['message'], $result['code']);
            }
        }

        $request->merge([
            'X-PERSONAL-TOKEN' => $key,
        ]);

        return $next($request);
    }
}