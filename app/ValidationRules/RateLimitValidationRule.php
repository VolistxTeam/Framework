<?php

namespace App\ValidationRules;

use App\Facades\Messages;
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
                'message' => Messages::E429(),
                'code' => 429
            ];
        }

        return true;
    }
}