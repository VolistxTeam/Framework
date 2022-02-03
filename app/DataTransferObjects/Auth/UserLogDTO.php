<?php

namespace App\DataTransferObjects\Auth;

use App\DataTransferObjects\DataTransferObjectBase;

class UserLogDTO extends DataTransferObjectBase
{
    public string $id;
    public string $url;
    public string $ip;
    public string $method;
    public ?string $user_agent;
    public string $created_at;

    public static function fromModel($userLog): self
    {
        return new self($userLog);
    }

    public function GetDTO(): array
    {
        return [
            'id' => $this->id,
            'personal_token' => PersonalTokenDTO::fromModel($this->entity->personalToken()->first())->GetDTO(),
            'url' => $this->url,
            'ip' => $this->ip,
            'method' => $this->method,
            'user_agent' => $this->user_agent,
        ];
    }
}