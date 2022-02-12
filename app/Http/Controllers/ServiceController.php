<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use VolistxTeam\VSkeletonKernel\Facades\Permissions;
use VolistxTeam\VSkeletonKernel\Http\Controllers\Controller;

class ServiceController extends Controller
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