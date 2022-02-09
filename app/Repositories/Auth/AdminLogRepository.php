<?php

namespace App\Repositories\Auth;

use App\Models\Auth\AdminLog;
use Illuminate\Support\Facades\Schema;

class AdminLogRepository
{
    public function Create(array $inputs)
    {
        AdminLog::query()->create([
            'access_token_id' => $inputs['access_token_id'],
            'url' => $inputs['url'],
            'ip' => $inputs['ip'],
            'method' => $inputs['method'],
            'user_agent' => $inputs['user_agent'],
        ]);
    }

    public function Find($log_id)
    {
        return AdminLog::query()->where('id', $log_id)->first();
    }

    public function FindAll($needle, $page, $limit)
    {
        $columns = Schema::getColumnListing('admin_logs');
        $query = AdminLog::query();

        foreach ($columns as $column) {
            $query->orWhere("admin_logs.$column", 'LIKE', "%$needle%");
        }
        return $query->orderBy('created_at', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);
    }
}