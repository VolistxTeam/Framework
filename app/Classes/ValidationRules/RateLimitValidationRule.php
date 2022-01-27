<?php

namespace App\Classes\ValidationRules;

use App\Classes\MessagesCenter;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];
        $request = $this->inputs['request'];


        $executed = RateLimiter::attempt(
            $token->subscription_id,
            $perMinute = 5,
            function () {
            }
        );


        if (!$executed) {
            return [
                'message' => MessagesCenter::E429(),
                'code' => 429
            ];
        }

        return true;
    }
}