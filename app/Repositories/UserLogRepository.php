<?php

namespace App\Repositories;

use App\Models\PersonalToken;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserLogRepository
{
    public function Create($personal_token_id,array $inputs)
    {
        return UserLog::query()->create([
            'personal_token_id' => $personal_token_id,
            'url'               => $inputs['url'],
            'request_method'    => $inputs['request_method'],
            'request_body'      => $inputs['request_body'],
            'request_header'    => $inputs['request_header'],
            'ip'                => $inputs['ip'],
            'response_code'     => $inputs['response_code'],
            'response_body'     => $inputs['response_body'],
        ]);
    }

    public function FindById($personal_token_id, $log_id)
    {
        return UserLog::query()->where('id', $log_id)->where('personal_token_id', $personal_token_id)->first();
    }

    public function FindLogsByToken($personal_token_id,$needle,$page,$limit)
    {
        $columns = Schema::getColumnListing('user_logs');
        $query = UserLog::query();

        foreach($columns as $column) {
            $query->orWhere("user_logs.$column", 'LIKE', "%$needle%");
        }
        return $query->where('personal_token_id', $personal_token_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function FindLogsByTokenCount($personal_token_id,Carbon $date): int
    {
        return UserLog::query()->where('personal_token_id',$personal_token_id)
            ->whereMonth('created_at', $date->format('m'))
            ->whereYear('created_at', $date->format('Y'))
            ->count();
    }

    public function FindLogsBySubscription($subscription_id,$needle,$page,$limit)
    {
        $columns = Schema::getColumnListing('user_logs');
        $query = UserLog::query();

        foreach($columns as $column) {
            $query->orWhere("user_logs.$column", 'LIKE', "%$needle%");
        }
        return $query->join('personal_tokens', 'personal_tokens.id', '=', 'user_logs.personal_token_id')
            ->where('personal_tokens.subscription_id', $subscription_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function FindLogsBySubscriptionCount($subscription_id, $date): int
    {
        return UserLog::query()->join('personal_tokens', 'personal_tokens.id', '=', 'user_logs.personal_token_id')
            ->where('personal_tokens.subscription_id', $subscription_id)
            ->whereMonth('user_logs.created_at', $date->format('m'))
            ->whereYear('user_logs.created_at', $date->format('Y'))
            ->count();
    }
}