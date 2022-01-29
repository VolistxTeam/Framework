<?php

namespace App\DataTransferObjects;


use Carbon\Carbon;
use function Symfony\Component\Translation\t;

class PersonalTokenDTO extends DataTransferObjectBase
{
    public string $id;
    public string $subscription_id;
    public array $permissions;
    public array $whitelist_range;
    public string $activated_at;
    public ?string $expires_at;
    public string $created_at;
    public string $updated_at;


    public static function fromModel($personal_token): self
    {
        return new self($personal_token);
    }


    public function GetDTO($key = null): array
    {
        $result = [
            'id' => $this->id,
            'subscription' => SubscriptionDTO::fromModel($this->entity->subscription()->first()),
            'key' => null,
            'permissions' => $this->permissions,
            'whitelist_range' => $this->whitelist_range,
            'status' => [
                'is_expired' => $this->expires_at != null && Carbon::now()->greaterThan(Carbon::createFromTimeString($this->expires_at)),
                'activated_at' => $this->activated_at,
                'expires_at' => $this->expires_at
            ],
            'created_at' =>$this->created_at,
            'updated_at' => $this->updated_at
        ];

        if ($key) {
            $result['key'] = $key;
        } else {
            unset($result['key']);
        }

        return $result;
    }
}