<?php

namespace App\Http\Controllers\Services;

use App\Facades\Permissions;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExampleService extends Controller
{
    public function __construct()
    {
        $this->module = 'example';
    }

    public function Ping(Request $request): JsonResponse
    {
        if (!Permissions::check($request->X_PERSONAL_TOKEN, $this->module, 'permission-name')) {
            // do something if permissions failed
        }

        return response()->json(['pong']);
    }
}
