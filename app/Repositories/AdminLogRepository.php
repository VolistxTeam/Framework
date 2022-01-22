<?php

namespace App\Repositories;

use App\Models\AdminLog;
use Illuminate\Support\Facades\Schema;

class AdminLogRepository
{
    public function Create($access_token_id, array $inputs)
    {
        AdminLog::query()->create([
            'access_token_id' => $access_token_id,
            'url' => $inputs['url'],
            'request_method' => $inputs['request_method'],
            'request_body' => empty($inputs['request_body']) ? null : $inputs['request_body'],
            'request_header' => $inputs['request_header'],
            'ip' => $inputs['ip'],
            'response_code' => $inputs['response_code'],
            'response_body' => $inputs['response_body'],
        ]);
    }

    public function Find($access_token_id, $log_id)
    {
        return AdminLog::query()->where('id', $log_id)->where('access_token_id', $access_token_id)->first();
    }

    public function FindAll($access_token_id, $needle, $page, $limit)
    {
        $columns = Schema::getColumnListing('admin_logs');
        $query = AdminLog::query();

        foreach ($columns as $column) {
            $query->orWhere("admin_logs.$column", 'LIKE', "%$needle%");
        }
        return $query->where('access_token_id', $access_token_id)
            ->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }
}