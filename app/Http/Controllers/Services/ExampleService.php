<?php

namespace App\Http\Controllers\Services;

use Illuminate\Http\Request;

class ExampleService extends Controller
{
    public function Example(Request $request)
    {
        return response()->json(['hehe']);
    }
}
