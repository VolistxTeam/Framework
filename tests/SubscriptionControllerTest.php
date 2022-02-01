<?php

use App\Models\AccessToken;
use App\Models\PersonalToken;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UserLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class SubscriptionControllerTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    protected function setUp(): void
    {
        parent::setUp();
        Plan::factory()->count(3)->create();

    }


    /** @test */
    public function AuthorizeCreateSubPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $this->TestPermissions($token, $key, 'POST', '/sys-bin/admin/subscriptions/', [
            'subscriptions:*' => 201,
            'subscriptions:create' => 201,
            '' => 401
        ], [
            "plan_id" => Plan::query()->first()->id,
            "user_id" => 1,
            "plan_activated_at" => \Carbon\Carbon::now(),
            "plan_expires_at"=> \Carbon\Carbon::now()->addHours(50)
        ]);
    }

    /** @test */
    public function CreateSub()
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);

        $request = $this->json('POST', '/sys-bin/admin/subscriptions/', [
            "plan_id" => Plan::query()->first()->id,
            "user_id" => 1,
            "plan_activated_at" => \Carbon\Carbon::now(),
            "plan_expires_at"=> \Carbon\Carbon::now()->addHours(50)
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(201);
        self::assertSame('1', json_decode($request->response->getContent())->user_id);
        self::assertSame(Plan::query()->first()->id, json_decode($request->response->getContent())->plan->id);
    }



    /** @test */
    public function AuthorizeUpdateSubPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $this->TestPermissions($token, $key, 'PUT', "/sys-bin/admin/subscriptions/{$sub->id}", [
            'subscriptions:*' => 200,
            'subscriptions:update' => 200,
            '' => 401
        ], [
            ]
        );
    }

    /** @test */
    public function UpdateSub()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $request = $this->json('PUT', "/sys-bin/admin/subscriptions/{$sub->id}", [
            'plan_id' => Plan::query()->skip(1)->first()->id,
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame('0', json_decode($request->response->getContent())->user_id);
        self::assertSame(Plan::query()->skip(1)->first()->id, json_decode($request->response->getContent())->plan->id);
    }



    /** @test */
    public function AuthorizeDeleteSubPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $sub = $this->GenerateSub(0);
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/subscriptions/{$sub->id}", [
            'subscriptions:*' => 204,
        ]);

        $sub = $this->GenerateSub(0);
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/subscriptions/{$sub->id}", [
            'subscriptions:delete' => 204,
        ]);

        $sub = $this->GenerateSub(0);
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/subscriptions/{$sub->id}", [
            '' => 401
        ]);
    }

    /** @test */
    public function DeleteSub()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $request = $this->json('DELETE', "/sys-bin/admin/subscriptions/{$sub->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(204);
    }



    /** @test */
    public function AuthorizeGetSubPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/subscriptions/{$sub->id}", [
            'subscriptions:*' => 200,
            '' => 401,
            'subscriptions:view' => 200
        ]);
    }

    /** @test */
    public function GetSub()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $request = $this->json('GET', "/sys-bin/admin/subscriptions/{$sub->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame('0', json_decode($request->response->getContent())->user_id);
    }


    /** @test */
    public function AuthorizeGetSubsPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/subscriptions/", [
            'subscriptions:*' => 200,
            '' => 401,
            'subscriptions:view-all' => 200
        ]);
    }

    /** @test */
    public function GetSubs()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);
        $sub = $this->GenerateSub(0);

        $request = $this->json('GET', "/sys-bin/admin/subscriptions/", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(2, json_decode($request->response->getContent())->items);

        $request = $this->json('GET', "/sys-bin/admin/subscriptions/?limit=1", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(1, json_decode($request->response->getContent())->items);
    }

    /** @test */
    public function AuthorizeGetSubLogs()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/subscriptions/{$sub->id}/logs", [
            'subscriptions:*' => 200,
            '' => 401,
            'subscriptions:logs' => 200
        ]);
    }

    /** @test */
    public function GetSubLogs()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GenerateSub(0);
        $request = $this->json('GET', "/sys-bin/admin/subscriptions/{$sub->id}/logs", [], [
            'Authorization' => "Bearer $key",
        ]);

        ray(json_decode($request->response->getContent()));
        self::assertResponseStatus(200);
        self::assertCount(25, json_decode($request->response->getContent())->items);


        $request = $this->json('GET', "/sys-bin/admin/subscriptions/{$sub->id}/logs/?limit=10", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(10, json_decode($request->response->getContent())->items);
    }



    /** @test */
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

    private function GenerateAccessToken($key)
    {
        $salt = Str::random(16);
        return AccessToken::factory()
            ->create(['key' => substr($key, 0, 32),
                'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => array('subscriptions:*')]);
    }

    private function GenerateSub($userID)
    {
        return Subscription::factory()
            ->has(PersonalToken::factory()->count(5)->has(UserLog::factory()->count(5))
        )->create(['user_id' => $userID, 'plan_id' => Plan::query()->first()->id]);
    }
}