<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/ping', [ServiceController::class, 'Ping']);
});
