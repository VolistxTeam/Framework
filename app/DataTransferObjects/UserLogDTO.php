<?php

namespace App\DataTransferObjects;


use Carbon\Carbon;
use function Symfony\Component\Translation\t;

class UserLogDTO extends DataTransferObjectBase
{
    public string $id;
    public string $personal_token_id;
    public string $url;
    public string $request_header;
    public string $request_method;
    public ?string $request_body;
    public string $response_code;
    public string $response_body;
    public string $created_at;


    public static function fromModel($userLog): self
    {
        return new self($userLog->toArray());
    }


    public function GetDTO(): array
    {
        return [
            'id' => $this->id,
            'personal_token' => [
                'id'=> $this->personal_token_id
            ],
            'url' => $this->url,
            'request' => [
                'method' => $this->request_method,
                'header' =>json_decode($this->request_header),
                'body' =>json_decode($this->request_body),
            ],
            'response' => [
                'code' => $this->response_code,
               // 'body' =>json_decode($this->response_body)
            ]
        ];
    }
}