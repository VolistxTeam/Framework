<?php

namespace App\Http\Controllers;

use App\Models\AccessKeys;
use App\Models\Logs;
use App\Models\PersonalKeys;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function Home(Request $request)
    {
        return view('index');
    }
}
