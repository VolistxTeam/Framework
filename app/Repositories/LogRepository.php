<?php

namespace App\Repositories;

use App\Models\Log;
use App\Models\PersonalToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LogRepository
{
    public function Create($personal_token_id,array $inputs)
    {
        return Log::query()->create([
            'personal_token_id' => $personal_token_id,
            'key' => $inputs['key'],
            'value' =>$inputs['value'],
            'type' =>$inputs['type']
        ]);
    }

    public function FindById($personal_token_id, $log_id)
    {
        return Log::query()->where('id', $log_id)->where('personal_token_id', $personal_token_id)->first();
    }

    public function FindLogsByToken($personal_token_id,$needle,$page,$limit)
    {
        $columns = Schema::getColumnListing('logs');
        $query = Log::query();

        foreach($columns as $column) {
            $query->orWhere("logs.$column", 'LIKE', "%$needle%");
        }
        return $query->where('personal_token_id', $personal_token_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function FindLogsByTokenCount($personal_token_id,Carbon $date): int
    {
        return Log::query()->where('personal_token_id',$personal_token_id)
            ->whereMonth('created_at', $date->format('m'))
            ->whereYear('created_at', $date->format('Y'))            ->count();
    }

    public function FindLogsBySubscription($subscription_id,$needle,$page,$limit)
    {
        $columns = Schema::getColumnListing('logs');
        $query = Log::query();

        foreach($columns as $column) {
            $query->orWhere("logs.$column", 'LIKE', "%$needle%");
        }
        return $query->join('personal_tokens', 'personal_tokens.id', '=', 'logs.personal_token_id')
            ->where('personal_tokens.subscription_id', $subscription_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }

    public function FindLogsBySubscriptionCount($subscription_id, $date): int
    {
        return Log::query()->join('personal_tokens', 'personal_tokens.id', '=', 'logs.personal_token_id')
            ->where('personal_tokens.subscription_id', $subscription_id)
            ->whereMonth('created_at', $date->format('m'))
            ->whereYear('created_at', $date->format('Y'))
            ->count();
    }
}