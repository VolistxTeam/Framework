<?php

namespace App\Classes\ValidationRules;

use App\Classes\Facades\Messages;
use App\Repositories\UserLogRepository;
use Carbon\Carbon;

class RequestsCountValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];

        $repository = new UserLogRepository();
        $requestsMadeCount = $repository->FindLogsBySubscriptionCount($token->subscription()->first()->id, Carbon::now());
        $planRequestsLimit = $token->subscription()->first()->plan()->first()->requests;
        if ($planRequestsLimit != -1 && $requestsMadeCount >= $planRequestsLimit) {
            return [
                'message' => Messages::E429(),
                'code' => 429
            ];
        }
        return true;
    }
}