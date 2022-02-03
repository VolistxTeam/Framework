<?php

use App\Models\Auth\AccessToken;
use App\Models\Auth\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

class PlanControllerTest extends BaseTestCase
{
    use DatabaseMigrations;

    public function createApplication(): Application
    {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    /** @test */
    public function AuthorizeCreatePlanPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $this->TestPermissions($token, $key, 'POST', '/sys-bin/admin/plans/', [
            'plans:*' => 201,
            '' => 401,
            'plans:create' => 201
        ], [
            "name" => "name",
            "description" => "description",
            "data" => array('requests' => 50)
        ]);
    }

    private function GenerateAccessToken($key)
    {
        $salt = Str::random(16);
        return AccessToken::factory()
            ->create(['key' => substr($key, 0, 32),
                'secret' => Hash::make(substr($key, 32), ['salt' => $salt]),
                'secret_salt' => $salt,
                'permissions' => array('plans:*')]);
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

    /** @test */
    public function CreatePlan()
    {
        $key = Str::random(64);
        $this->GenerateAccessToken($key);

        $request = $this->json('POST', '/sys-bin/admin/plans/', [
            "name" => "name",
            "description" => "description",
            "data" => array('requests' => 50)
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(201);
        self::assertSame("name", json_decode($request->response->getContent())->name);
        self::assertSame("description", json_decode($request->response->getContent())->description);
        self::assertSame(50, json_decode($request->response->getContent())->data->requests);
    }

    /** @test */
    public function AuthorizeUpdatePlanPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $plan = $this->GeneratePlan();

        $this->TestPermissions($token, $key, 'PUT', "/sys-bin/admin/plans/{$plan->id}", [
            'plans:*' => 200,
            'plans:update' => 200,
            '' => 401
        ], [
            ]
        );
    }

    private function GeneratePlan()
    {
        return Plan::factory()->create();
    }

    /** @test */
    public function UpdatePlan()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $plan = $this->GeneratePlan();

        $request = $this->json('PUT', "/sys-bin/admin/plans/{$plan->id}", [
            'name' => "UpdatedName"
        ], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertSame("UpdatedName", json_decode($request->response->getContent())->name);
    }

    /** @test */
    public function AuthorizeDeletePlanPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);

        $plan = $this->GeneratePlan();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/plans/{$plan->id}", [
            'plans:*' => 204,
        ]);

        $plan = $this->GeneratePlan();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/plans/{$plan->id}", [
            'plans:delete' => 204,
        ]);

        $plan = $this->GeneratePlan();
        $this->TestPermissions($token, $key, 'DELETE', "/sys-bin/admin/plans/{$plan->id}", [
            '' => 401
        ]);
    }

    /** @test */
    public function DeletePlan()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $plan = $this->GeneratePlan();

        $request = $this->json('DELETE', "/sys-bin/admin/plans/{$plan->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(204);
    }

    /** @test */
    public function AuthorizeGetPlanPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $plan = $this->GeneratePlan();

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/plans/{$plan->id}", [
            'plans:*' => 200,
            '' => 401,
            'plans:view' => 200
        ]);
    }

    /** @test */
    public function GetPlan()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $plan = $this->GeneratePlan();

        $request = $this->json('GET', "/sys-bin/admin/plans/{$plan->id}", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertNotEmpty(json_decode($request->response->getContent())->name);
    }

    /** @test */
    public function AuthorizeGetPlansPermissions()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $sub = $this->GeneratePlan();

        $this->TestPermissions($token, $key, 'GET', "/sys-bin/admin/plans/", [
            'plans:*' => 200,
            '' => 401,
            'plans:view-all' => 200
        ]);
    }

    /** @test */
    public function GetPlans()
    {
        $key = Str::random(64);
        $token = $this->GenerateAccessToken($key);
        $this->GeneratePlan();
        $this->GeneratePlan();

        $request = $this->json('GET', "/sys-bin/admin/plans/", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(2, json_decode($request->response->getContent())->items);

        $request = $this->json('GET', "/sys-bin/admin/plans/?limit=1", [], [
            'Authorization' => "Bearer $key",
        ]);

        self::assertResponseStatus(200);
        self::assertCount(1, json_decode($request->response->getContent())->items);
    }
}