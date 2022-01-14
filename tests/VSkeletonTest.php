<?php

use App\Models\AccessToken;
use App\Models\PersonalToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class VSkeletonTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function createApplication(): \Laravel\Lumen\Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /** @test */
    public function AuthorizeCreatePersonalTokenPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $this->TestPermissions($token, $key, 'POST', '/sys-bin/admin/', [
            '*' => 201,
            '' => 401,
            'key:create' => 201
        ], [
            "user_id" => 1,
            "max_count" => 1,
            "permissions" => array('*'),
            "whitelist_range" => array('127.0.0.1'),
            "hours_to_expire" => 720,
        ]);
    }

    /** @test */
    public function CreatePersonalToken()
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);

        $request = $this->json('POST', '/sys-bin/admin/', [
            "user_id" => 1,
            "max_count" => 1,
            "permissions" => array('*'),
            "whitelist_range" => array('127.0.0.1'),
            "hours_to_expire" => 720,
        ], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);

        self::assertResponseStatus(201);
        self::assertSame(1, json_decode($request->response->getContent())->user_id);
        self::assertSame(["*"], json_decode($request->response->getContent())->permissions);
        self::assertSame(["127.0.0.1"], json_decode($request->response->getContent())->whitelist_range);
    }


    /** @test */
    public function AuthorizeUpdatePersonalTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'PATCH', "/sys-bin/admin/{$personalToken->id}", [
            '*' => 200,
            'key:update' => 200,
            '' => 401
        ], [
                'max_count' => 5
            ]
        );
    }

    /** @test */
    public function UpdatePersonalToken()
    {
        $key = Str::random(64);
        $accesstoken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $request = $this->json('PATCH', "/sys-bin/admin/{$personalToken->id}", [
            'max_count' => 5,
            'permissions' => array('key:create'),
            'whitelist_range' => array('127.0.0.5')
        ], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertSame(5, json_decode($request->response->getContent())->max_count);
        self::assertSame(['key:create'], json_decode($request->response->getContent())->permissions);
        self::assertSame(['127.0.0.5'], json_decode($request->response->getContent())->whitelist_range);
    }


    /** @test */
    public function AuthorizeResetPersonalTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'PATCH', "/sys-bin/admin/{$personalToken->id}/reset", [
            '*' => 200,
            'key:reset' => 200,
            '' => 401
        ], [
            ]
        );
    }

    /** @test */
    public function ResetPersonalToken()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);
        $tokenKey = $personalToken->key;

        $request = $this->json('PATCH', "/sys-bin/admin/{$personalToken->id}/reset", [
        ], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertNotSame($tokenKey, PersonalToken::query()->first()->key);
    }


    /** @test */
    public function AuthorizeDeletePersonalTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'DELETE', "/sys-bin/admin/{$personalToken->id}", [
            '*' => 204,
        ]);

        $personalToken = $this->GeneratePersonalToken(0);
        $this->TestPermissions($accessToken, $key, 'DELETE', "/sys-bin/admin/{$personalToken->id}", [
            'key:delete' => 204,
        ]);

        $personalToken = $this->GeneratePersonalToken(0);
        $this->TestPermissions($accessToken, $key, 'DELETE', "/sys-bin/admin/{$personalToken->id}", [
            '' => 401
        ]);
    }

    /** @test */
    public function DeletePersonalToken()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $request = $this->json('DELETE', "/sys-bin/admin/{$personalToken->id}", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);

        self::assertResponseStatus(204);
    }


    /** @test */
    public function AuthorizeGetPersonalTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'GET', "/sys-bin/admin/{$personalToken->id}", [
            '*' => 200,
            '' => 401,
            'key:list' => 200
        ]);
    }

    /** @test */
    public function GetPersonalToken()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $request = $this->json('GET', "/sys-bin/admin/{$personalToken->id}", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);

        self::assertResponseStatus(200);
        self::assertSame(0, json_decode($request->response->getContent())->user_id);
    }


    /** @test */
    public function AuthorizeGetPersonalTokensPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'GET', "/sys-bin/admin/", [
            '*' => 200,
            '' => 401,
            'key:list' => 200
        ]);
    }

    /** @test */
    public function GetPersonalTokens()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $sub = $this->GeneratePersonalToken(0);
        $anotherSub = $this->GeneratePersonalToken(123456789);

        $request = $this->json('GET', "/sys-bin/admin/", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertCount(2, json_decode($request->response->getContent())->items);


        $request = $this->json('GET', "/sys-bin/admin/?limit=1", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertCount(1, json_decode($request->response->getContent())->items);


        $request = $this->json('GET', "/sys-bin/admin/?search=sdfagfsdgsadgad", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertCount(0, json_decode($request->response->getContent())->items);


        $request = $this->json('GET', "/sys-bin/admin/?page=2&limit=1", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);
        self::assertResponseStatus(200);
        self::assertSame(123456789, json_decode($request->response->getContent())->items[0]->user_id);
    }


    /** @test */
    public function AuthorizeGetPersonalTokenLogs()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'GET', "/sys-bin/admin/{$personalToken->id}/logs", [
            '*' => 200,
            'key:logs' => 200,
            '' => 401
        ], [
            ]
        );
    }

    /** @test */
    public function GetPersonalTokenLogs()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $request = $this->json('GET', "/sys-bin/admin/{$personalToken->id}/logs", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);

        self::assertResponseStatus(200);
        self::assertSame([], json_decode($request->response->getContent())->items);
    }


    /** @test */
    public function GetPersonalTokenStats()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $request = $this->json('GET', "/sys-bin/admin/{$personalToken->id}/stats", [], [
            'Authorization' => "Bearer $key",
            'Accept' => 'application/json'
        ]);

        self::assertResponseStatus(200);
    }

    /** @test */
    public function AuthorizeGetPersonalTokenStatsPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $personalToken = $this->GeneratePersonalToken(0);

        $this->TestPermissions($accessToken, $key, 'GET', "/sys-bin/admin/{$personalToken->id}/stats", [
            '*' => 200,
            'key:stats' => 200,
            '' => 401
        ], [
            ]
        );
    }


    /** @test */
    private function TestPermissions($token, $key, $verb, $route, $permissions, $input = [])
    {
        foreach ($permissions as $permissionName => $permissionResult) {
            $token->permissions = array($permissionName);
            $token->save();

            $request = $this->json($verb, $route, $input, [
                'Authorization' => "Bearer $key",
                'Accept' => 'application/json',
                'Content_Type' => 'application/json'
            ]);
            self::assertResponseStatus($permissionResult);
        }
    }

    private function GenerateAccessToken($key)
    {
        $salt = Str::random(16);

        return AccessToken::factory()
            ->create(
                [
                    'key' => substr($key, 0, 32),
                    'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
                    'secret_salt' => $salt,
                    'permissions' => array('*')
                ]
            );
    }

    private function GeneratePersonalToken($user_id)
    {
        return PersonalToken::factory()
            ->create(
                [
                    'user_id' => $user_id
                ]
            );
    }
}
