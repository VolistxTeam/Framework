<?php

namespace App\Http\Controllers\Services;

use App\Facades\Permissions;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\DocBlock\Tags\Example;

class ExampleService extends Controller
{
    public function __construct()
    {
        $this->module = 'example';
    }

    public function Ping(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!Permissions::check($request->X_PERSONAL_TOKEN, $this->module,'permission-name')) {
            // do something if permissions failed
        }

        return response()->json(['pong']);
    }
}
