<?php

use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug', function() {
    return response()->json([
        'status' => 'working',
        'message' => 'Laravel is responding'
    ]);
});
