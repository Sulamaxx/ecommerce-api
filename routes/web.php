<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Route::fallback(function () {
//     return response()->json(['message' => 'Route not found.'], 404);
//     // return response()->json(['message' => 'Unauthorized'], 401);
// });
