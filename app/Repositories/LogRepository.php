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

    public function Find($personal_token_id, $log_id)
    {
        return Log::query()->where('id', $log_id)->where('personal_token_id', $personal_token_id)->first();
    }

    public function FindAll($personal_token_id,$needle,$page,$limit)
    {
        $columns = Schema::getColumnListing('logs');
        $query = Log::query();

        foreach($columns as $column) {
            $query->orWhere("logs.$column", 'LIKE', "%$needle%");
        }
        return $query->where('personal_token_id', $personal_token_id)->paginate($limit, ['*'], 'page', $page);
    }
}