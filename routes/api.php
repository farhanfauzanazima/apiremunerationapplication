<?php

use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Controller;

Route::get('/test', function () {
    return response()->json(['message' => 'API is ready']);
});