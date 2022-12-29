<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.user')->group(function () {
    Route::get('/ping', [ServiceController::class, 'Ping']);
});
