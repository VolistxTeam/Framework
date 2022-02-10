<?php

namespace App\ValidationRules\Auth;

use App\Facades\Messages;
use App\Repositories\Auth\UserLogRepository;
use App\ValidationRules\ValidationRuleBase;
use Carbon\Carbon;
use GuzzleHttp\Client;

class RequestsCountValidationRule extends ValidationRuleBase
{
    public function Validate(): bool|array
    {
        $sub_id = $this->inputs['token']->subscription()->first()->id;
        $plan = $this->inputs['plan'];
        $planRequestsLimit = $plan['data']['requests']?? null;

        if (config('log.adminLogMode') === 'local') {
            $repository = new UserLogRepository();
            $requestsMadeCount = $repository->FindLogsBySubscriptionCount($sub_id, Carbon::now());

        } else {
            $httpURL = config('log.userLogHttpUrl');
            $remoteToken = config('log.userLogHttpToken');
            $client = new Client();
            $response = $client->get("$httpURL/$sub_id/count", [
                'headers' => [
                    'Authorization' => "Bearer $remoteToken",
                ],
            ]);


            if (!$planRequestsLimit || ($planRequestsLimit != -1 && json_decode($response->getBody()) >= $planRequestsLimit)) {
                return [
                    'message' => Messages::E429(),
                    'code' => 429
                ];
            }
        }





        if ($response->getStatusCode() != 201) {
            //WE SEE WHAT WE DO
        }

        return true;
    }
}