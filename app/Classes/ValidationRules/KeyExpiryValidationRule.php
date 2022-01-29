<?php

namespace App\Classes\ValidationRules;

use App\Classes\Facades\Messages;
use Carbon\Carbon;

class KeyExpiryValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];

        if ($token->expires_at && Carbon::now()->greaterThan(Carbon::createFromTimeString($token->expires_at))) {
            return [
                'message' => Messages::E403(),
                'code' => 403
            ];
        }
        return true;
    }
}