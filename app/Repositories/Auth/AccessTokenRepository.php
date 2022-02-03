<?php

namespace App\Repositories\Auth;

use App\Models\Auth\AccessToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AccessTokenRepository
{
    public function Create($subscription_id, array $inputs)
    {
        return AccessToken::query()->create([
            'key' => substr($inputs['key'], 0, 32),
            'secret' => Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]),
            'secret_salt' => $inputs['salt'],
            'permissions' => $inputs['permissions'],
            'whitelist_range' => $inputs['whitelist_range']
        ]);
    }

    public function Update($token_id, array $inputs)
    {
        $token = $this->Find($token_id);

        if (!$token) {
            return null;
        }

        $permissions = $inputs['permissions'] ?? null;
        $whitelistRange = $inputs['whitelist_range'] ?? null;

        if (!$permissions && !$whitelistRange) {
            return $token;
        }


        if ($permissions) $token->permissions = json_encode($permissions);

        if ($whitelistRange) $token->whitelist_range = json_encode($whitelistRange);

        $token->save();

        return $token;
    }

    public function Find($token_id)
    {
        return AccessToken::query()->where('id', $token_id)->first();
    }

    public function Reset($token_id, $inputs)
    {
        $token = $this->Find($token_id);

        if (!$token) {
            return null;
        }

        $token->key = substr($inputs['key'], 0, 32);
        $token->secret = Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]);
        $token->secret_salt = $inputs['salt'];
        $token->save();

        return $token;
    }

    public function Delete($token_id)
    {
        $toBeDeletedToken = $this->Find($token_id);

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
        $columns = Schema::getColumnListing('AccessToken');
        $query = AccessToken::query();

        foreach ($columns as $column) {
            $query->orWhere("access_tokens.$column", 'LIKE', "%$needle%");
        }
        return $query->paginate($limit, ['*'], 'page', $page);
    }

    public function AuthAccessToken($token)
    {
        return AccessToken::query()->where('key', substr($token, 0, 32))
            ->get()->filter(function ($v) use ($token) {
                return Hash::check(substr($token, 32), $v->secret, ['salt' => $v->secret_salt]);
            })->first();
    }
}