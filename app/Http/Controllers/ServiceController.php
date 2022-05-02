<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Volistx\FrameworkKernel\Facades\Permissions;
use Volistx\FrameworkKernel\Facades\PersonalTokens;
use Volistx\FrameworkKernel\Http\Controllers\Controller;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->module = 'example';
    }

    public function Ping(Request $request): JsonResponse
    {
ray(PersonalTokens::getToken());
        if (!Permissions::check(PersonalTokens::getToken(), $this->module, 'permission-name')) {
            // do something if permissions failed
        }

        return response()->json(['pong']);
    }
}
