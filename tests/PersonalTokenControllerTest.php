<?php

use App\Models\Auth\AccessToken;
use App\Models\Auth\PersonalToken;
use App\Models\Auth\Plan;
use App\Models\Auth\Subscription;
use App\Models\Auth\UserLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class PersonalTokenControllerTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /** @test */
    public function AuthorizeCreateTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 0);

        $this->TestPermissions($accessToken, $key, 'POST', "/sys-bin/admin/personal-tokens/{$sub->id}", [
            'personal-tokens:*' => 201,
            '' => 401,
            'personal-tokens:create' => 201
        ], [
            'permissions' => array('*'),
            'whitelist_range' => array('127.0.0.0'),
            'hours_to_expire' => 500,
        ]);
    }

    private function GenerateAccessToken($key)
    {
        $salt = Str::random(16);
        return AccessToken::factory()
            ->create(['key' => substr($key, 0, 32),
                'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => array('personal-tokens:*')]);
    }

    private function GenerateSub($userID, $tokenCount, $logs = 50)
    {
        $sub = Subscription::factory()
            ->has(PersonalToken::factory()->count($tokenCount))
            ->create(['user_id' => $userID, 'plan_id' => Plan::query()->first()->id]);

            UserLog::factory()->count($logs)->create([
                'subscription_id' => $sub->id
            ]);

        return $sub;
    }

    private function TestPermissions($token, $key, $verb, $route, $permissions, $input = [])
    {
        foreach ($permissions as $permissionName => $permissionResult) {
            $token->permissions = array($permissionName);
            $token->save();

            $request = $this->json($verb, $route, $input, [
                'Authorization' => "Bearer $key",
            ]);
            self::assertResponseStatus($permissionResult);
        }
    }

    /** @test */
    public function CreateToken()
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 0);

        $request = $this->json('POST', "/sys-bin/admin/personal-tokens/{$sub->id}", [
            'permissions' => array('*'),
            'whitelist_range' => array('127.0.0.0'),
            'hours_to_expire' => 500,
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(201);
        self::assertSame($sub->id, json_decode($request->response->getContent())->subscription->id);
        self::assertSame(array('*'), json_decode($request->response->getContent())->permissions);
        self::assertSame(array('127.0.0.0'), json_decode($request->response->getContent())->whitelist_range);
        self::assertSame(Carbon::createFromTimeString((json_decode($request->response->getContent())->token_status->activated_at))->addHours(500)->format('Y-m-d H:i:s'), json_decode($request->response->getContent())->token_status->expires_at);
    }

    /** @test */
    public function AuthorizeUpdateTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();

        $this->TestPermissions($accessToken, $key, 'PUT', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            'personal-tokens:*' => 200,
            'personal-tokens:update' => 200,
            '' => 401
        ], [
            ]
        );
    }

    /** @test */
    public function UpdateToken()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();


        $request = $this->json('PUT', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            'permissions' => array('1'),
            'whitelist_range' => array('128.0.0.0'),
            'hours_to_expire' => 1000,
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame($sub->id, json_decode($request->response->getContent())->subscription->id);
        self::assertSame(array('1'), json_decode($request->response->getContent())->permissions);
        self::assertSame(array('128.0.0.0'), json_decode($request->response->getContent())->whitelist_range);
        $expires_at = json_decode($request->response->getContent())->token_status->expires_at;
        $activated_at = json_decode($request->response->getContent())->token_status->activated_at;
        self::assertSame(Carbon::createFromTimeString($activated_at)->addHours(1000)->timestamp, Carbon::createFromTimeString($expires_at)->timestamp);

    }

    /** @test */
    public function AuthorizeResetTokenPermissions()
    {
        $key = Str::random(64);
        $accessToken = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();

        $this->TestPermissions($accessToken, $key, 'PUT', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}/reset", [
            'personal-tokens:*' => 200,
            'personal-tokens:reset' => 200,
            '' => 401
        ], [
            ]
        );
    }

    /** @test */
    public function ResetToken()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();
        $oldKey = $personalToken->key;

        $request = $this->json('PUT', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}/reset", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertNotSame($oldKey, json_decode($request->response->getContent())->key);
    }

    /** @test */
    public function AuthorizeDeleteTokenPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 3);

        $personalToken = $sub->personalTokens()->first();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            'personal-tokens:*' => 204,
        ]);

        $personalToken = $sub->personalTokens()->first();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            'personal-tokens:delete' => 204,
        ]);

        $personalToken = $sub->personalTokens()->first();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            '' => 401
        ]);
    }

    /** @test */
    public function DeleteToken()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();

        $request = $this->json('DELETE', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(204);
    }

    /** @test */
    public function AuthorizeGetTokenPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 1);
        $personalToken = $sub->personalTokens()->first();

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:view' => 200
        ]);
    }

    /** @test */
    public function GetToken()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 3);
        $personalToken = $sub->personalTokens()->first();

        $request = $this->json('GET', "/sys-bin/admin/personal-tokens/{$sub->id}/{$personalToken->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame($personalToken->id, json_decode($request->response->getContent())->id);
    }

    /** @test */
    public function AuthorizeGetTokensPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 3);

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/personal-tokens/{$sub->id}", [
            'personal-tokens:*' => 200,
            '' => 401,
            'personal-tokens:view-all' => 200
        ]);
    }

    /** @test */
    public function GetTokens()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0, 3);

        $request = $this->json('GET', "/sys-bin/admin/personal-tokens/{$sub->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(3, json_decode($request->response->getContent())->items);

        $request = $this->json('GET', "/sys-bin/admin/personal-tokens/{$sub->id}?search=xxqsqeqeqw", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(0, json_decode($request->response->getContent())->items);


        $request = $this->json('GET', "/sys-bin/admin/personal-tokens/{$sub->id}?limit=2", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(2, json_decode($request->response->getContent())->items);

    }

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->count(3)->create();
    }
}