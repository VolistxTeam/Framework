<?php

namespace App\Http\Middleware;

use App\ValidationRules\IPValidationRule;
use App\ValidationRules\KeyExpiryValidationRule;
use App\ValidationRules\RateLimitValidationRule;
use App\ValidationRules\RequestsCountValidationRule;
use App\ValidationRules\ValidKeyValidationRule;
use App\Repositories\PersonalTokenRepository;
use Closure;
use Illuminate\Http\Request;

class UserAuthMiddleware
{
    private PersonalTokenRepository $personalTokenRepository;
    public function __construct(PersonalTokenRepository $personalTokenRepository)
    {
        $this->personalTokenRepository = $personalTokenRepository;
    }


    public function handle(Request $request, Closure $next)
    {
        $token = $this->personalTokenRepository->AuthPersonalToken($request->bearerToken());
        $plan = $token->subscription()->first()->plan()->first();
        //prepare inputs array
        $inputs = [
            'request' => $request,
            'token' => $token,
            'plan' => $plan
        ];

        //add extra validators in the required order.
        //To be refactored to detect all classes with a base of ValidationRuleBase and create instance of them passing parameters, and ordering them by id
        $validators = [
            new ValidKeyValidationRule($inputs),
            new KeyExpiryValidationRule($inputs),
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
            'X_PERSONAL_TOKEN' => $token,
            'PLAN' => $plan
        ]);

        ray($request);
        return $next($request);
    }
}