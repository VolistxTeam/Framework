<?php

namespace App\Classes\ValidationRules;

use App\Classes\MessagesCenter;
use Carbon\Carbon;
use Wikimedia\IPSet;

class KeyValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];

        if (!$token || ($token->expires_at && Carbon::now()->greaterThan(Carbon::createFromTimeString($token->expires_at)))) {
            return [
                'message' => MessagesCenter::E403(),
                'code' => 403
            ];
        }
        return true;
    }
}