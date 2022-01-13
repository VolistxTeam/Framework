<?php

namespace App\Repositories;

use App\Models\PersonalToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PersonalTokenRepository
{
    public function Create($inputs)
    {
         return PersonalToken::query()->create([
            'user_id' => $inputs['user_id'],
            'key' => substr($inputs['key'], 0, 32),
            'secret' => Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]),
            'secret_salt' => $inputs['salt'],
            'max_count' => $inputs['max_count'],
            'permissions' => json_decode($inputs['permissions']),
            'whitelist_range' =>json_decode($inputs['whitelist_range']),
            'activated_at' => Carbon::now(),
            'expires_at' =>  $inputs['hours_to_expire'] != -1 ? Carbon::now()->addHours($inputs['hours_to_expire']) : null
        ]);
    }
    public function Update($token_id ,$inputs){
        $token = $this->Find($token_id);

        if (!$token) {
            return null;
        }

        $permissions = $inputs['permissions']?? null;
        $max_count = $inputs['max_count']?? null;
        $whitelistRange = $inputs['whitelist_range']?? null;
        $hoursToExpire = $inputs['hoursToExpire']?? null;

        if (!$permissions && !$whitelistRange && !$hoursToExpire && !$max_count) {
            return $token;
        }


        if ($permissions) $token->permissions = json_decode($permissions);

        if($max_count) $token->max_count = $max_count;

        if ($whitelistRange) $token->whitelist_range = json_decode($whitelistRange);

        if ($hoursToExpire) $token->expires_at = $hoursToExpire != -1 ? Carbon::createFromTimeString($token->activated_at)->addHours($hoursToExpire) : null;

        $token->save();

        return $token;
    }

    public function Reset($token_id,$inputs){
        $token = $this->Find($token_id);

        if (!$token) {
            return null;
        }

        $token->key = substr($inputs['key'], 0, 32);
        $token->secret = Hash::make(substr($inputs['key'], 32), ['salt' => $inputs['salt']]);
        $token->salt = $inputs['salt'];

        $token->save();

        return $token;
    }

    public function Find($token_id)
    {
        return PersonalToken::query()->where('id', $token_id)->first();
    }

    public function FindAll(){
        return PersonalToken::query()->get();
    }

    public function Delete($tokenID)
    {
        $toBeDeletedToken = $this->Find($tokenID);

        if (!$toBeDeletedToken) {
            return null;
        }

        $toBeDeletedToken->delete();

        return [
            'result' => 'true'
        ];
    }

}