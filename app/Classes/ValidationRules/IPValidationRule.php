<?php

namespace App\Classes\ValidationRules;

use App\Classes\Facades\Messages;
use Wikimedia\IPSet;

class IPValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];
        $request = $this->inputs['request'];

        $ipSet = new IPSet($token->whitelist_range);
        if (!empty($token->whitelist_range) && !$ipSet->match($request->getClientIp())) {
            return [
                'message' => Messages::E403("Not allowed in your location"),
                'code' => 403
            ];
        }
        return true;
    }
}