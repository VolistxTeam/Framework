<?php

namespace App\Classes\ValidationRules;

use App\Classes\MessagesCenter;
use Wikimedia\IPSet;

class IPValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $token = $this->inputs['token'];
        $request = $this->inputs['request'];

        $ipSet = new IPSet(json_decode($token->whitelist_range));
        if (!empty($token->whitelist_range) && !$ipSet->match($request->getClientIp())) {
            return [
                'message' => MessagesCenter::E403("Not allowed in your location"),
                'code' => 403
            ];
        }
        return true;
    }
}