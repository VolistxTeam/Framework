<?php

namespace App\Repositories\Auth;

use App\Models\Auth\AdminLog;
use App\Repositories\Auth\Interfaces\IAdminLogRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Schema;

class RemoteAdminLogRepository implements IAdminLogRepository
{
    private Client $client;
    private string $httpBaseUrl;
    private string $remoteToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->httpBaseUrl = config('log.adminLogHttpUrl');
        $this->remoteToken = config('log.adminLogHttpToken');
    }

    public function Create(array $inputs)
    {
       $this->client->post($this->httpBaseUrl, [
            'headers' => [
                'Authorization' => "Bearer {$this->remoteToken}",
                'Content-Type' => "application/json"
            ],
            'body' => json_encode($inputs)
        ]);
    }

    public function Find($log_id)
    {
        $response = $this->client->get("$this->httpBaseUrl/{$log_id}", [
            'headers' => [
                'Authorization' => "Bearer {$this->remoteToken}",
                'Content-Type' => "application/json"
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }

    public function FindAll($needle, $page, $limit)
    {
        $response = $this->client->get("$this->httpBaseUrl", [
            'headers' => [
                'Authorization' => "Bearer {$this->remoteToken}",
                'Content-Type' => "application/json"
            ],
            [
                'query' => [
                    'search' => $needle,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
}