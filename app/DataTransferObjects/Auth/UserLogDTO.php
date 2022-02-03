<?php

namespace App\DataTransferObjects\Auth;

use App\DataTransferObjects\DataTransferObjectBase;

class UserLogDTO extends DataTransferObjectBase
{
    public string $id;
    public string $url;
    public string $ip;
    public string $request_header;
    public string $request_method;
    public ?string $request_body;
    public string $response_code;
    public string $response_body;
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
            'request' => [
                'method' => $this->request_method,
                'header' => json_decode($this->request_header),
                'body' => json_decode($this->request_body),
            ],
            'response' => [
                'code' => $this->response_code,
                //TO BE DISCUSSED WITH CRYENTAL
                // 'body' =>json_decode($this->response_body)
            ]
        ];
    }
}