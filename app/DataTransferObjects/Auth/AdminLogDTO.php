<?php

namespace App\DataTransferObjects\Auth;

use App\DataTransferObjects\DataTransferObjectBase;

class AdminLogDTO extends DataTransferObjectBase
{
    public string $id;
    public string $access_token_id;
    public string $url;
    public string $ip;
    public string $method;
    public ?string $user_agent;
    public string $created_at;

    public static function fromModel($adminLog): self
    {
        return new self($adminLog);
    }

    public function GetDTO(): array
    {
        return [
            'id' => $this->id,
            'access_token' => [
                'id' => $this->access_token_id
            ],
            'url' => $this->url,
            'ip' => $this->ip,
            'method' => $this->method,
            'user_agent' => $this->user_agent,
        ];
    }
}