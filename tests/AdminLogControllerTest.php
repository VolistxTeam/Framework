<?php

use App\Models\AccessToken;
use App\Models\AdminLog;
use App\Models\PersonalToken;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class AdminLogControllerTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }


    /** @test */
    public function AuthorizeGetLogPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key,1);
        $log = $token->adminLogs()->first();

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/logs/{$log->id}", [
            '*' => 200,
            '' => 401,
            'key:list' => 200
        ]);
    }

    /** @test */
    public function GetLog()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key,1);
        $log = $token->adminLogs()->first();


        $request = $this->json('GET', "/sys-bin/admin/logs/{$log->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame($token->id,json_decode($request->response->getContent())->access_token->id);
    }


    /** @test */
    public function AuthorizeGetPlansPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key,5);

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/logs/", [
            '*' => 200,
            '' => 401,
            'key:list' => 200
        ]);
    }

    /** @test */
    public function GetPlans()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key,50);


        $request = $this->json('GET', "/sys-bin/admin/logs/", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(50, json_decode($request->response->getContent())->items);

        $request = $this->json('GET', "/sys-bin/admin/logs/?limit=1", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(1, json_decode($request->response->getContent())->items);
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

    private function GenerateAccessToken($key, $logsCount)
    {
        $salt = Str::random(16);
        return AccessToken::factory()->has(AdminLog::factory()->count($logsCount))
            ->create(['key' => substr($key, 0, 32),
                'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => array('*')]);
    }
}