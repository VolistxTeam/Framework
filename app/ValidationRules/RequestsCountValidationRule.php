<?php

namespace App\ValidationRules;

use App\Facades\Messages;
use App\Repositories\UserLogRepository;
use Carbon\Carbon;

class RequestsCountValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];
        $plan = $this->inputs['plan'];

        $repository = new UserLogRepository();
        $requestsMadeCount = $repository->FindLogsBySubscriptionCount($token->subscription()->first()->id, Carbon::now());
        $planRequestsLimit = $plan->requests;
        if ($planRequestsLimit != -1 && $requestsMadeCount >= $planRequestsLimit) {
            return [
                'message' => Messages::E429(),
                'code' => 429
            ];
        }
        return true;
    }
}