<?php

namespace App\Repositories;

use App\Models\PersonalToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PersonalTokenRepository
{
    public function Create($subscription_id, array $inputs)
    {
        return PersonalToken::query()->create([
            'subscription_id' => $subscription_id,
            'key' => substr($inputs['key'], 0, 32),
            'secret' => Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]),
            'secret_salt' => $inputs['salt'],
            'permissions' => json_encode($inputs['permissions']),
            'whitelist_range' => json_encode($inputs['whitelist_range']),
            'activated_at' => Carbon::now(),
            'expires_at' => $inputs['hours_to_expire'] != -1 ? Carbon::now()->addHours($inputs['hours_to_expire']) : null
        ]);
    }

    public function Update($subscription_id, $token_id, array $inputs)
    {
        $token = $this->Find($subscription_id, $token_id);

        if (!$token) {
            return null;
        }

        $permissions = $inputs['permissions'] ?? null;
        $whitelistRange = $inputs['whitelist_range'] ?? null;
        $hours_to_expire = $inputs['hours_to_expire'] ?? null;

        if (!$permissions && !$whitelistRange && !$hours_to_expire) {
            return $token;
        }


        if ($permissions) $token->permissions = json_encode($permissions);

        if ($whitelistRange) $token->whitelist_range = json_encode($whitelistRange);

        if ($hours_to_expire) $token->expires_at = $hours_to_expire != -1 ? Carbon::createFromTimeString($token->activated_at)->addHours($hours_to_expire) : null;

        $token->save();

        return $token;
    }

    public function Reset($subscription_id, $token_id, $inputs)
    {
        $token = $this->Find($subscription_id, $token_id);

        if (!$token) {
            return null;
        }

        $token->key = substr($inputs['key'], 0, 32);
        $token->secret = Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]);
        $token->secret_salt = $inputs['salt'];
        $token->save();

        return $token;
    }

    public function Find($subscription_id, $token_id)
    {
        return PersonalToken::query()->where('id', $token_id)->where('subscription_id', $subscription_id)->first();
    }

    public function Delete($subscription_id, $token_id)
    {
        $toBeDeletedToken = $this->Find($subscription_id, $token_id);

        if (!$toBeDeletedToken) {
            return null;
        }

        $toBeDeletedToken->delete();

        return [
            'result' => 'true'
        ];
    }

    public function FindAll($subscription_id, $needle, $page, $limit)
    {
        $columns = Schema::getColumnListing('personal_tokens');
        $query = PersonalToken::query();

        foreach ($columns as $column) {
            $query->orWhere("personal_tokens.$column", 'LIKE', "%$needle%");
        }
        return $query->where('subscription_id', $subscription_id)->paginate($limit, ['*'], 'page', $page);
    }
}