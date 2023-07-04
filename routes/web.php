<?php

use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});

Route::get('/', function () {
    return response()->json(['data' => 'Hello :)']);
});
