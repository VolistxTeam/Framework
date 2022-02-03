<?php

namespace App\ValidationRules\Auth;

use App\Facades\Messages;
use App\ValidationRules\ValidationRuleBase;


class ValidKeyValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];

        if (!$token) {
            return [
                'message' => Messages::E403(),
                'code' => 403
            ];
        }
        return true;
    }
}