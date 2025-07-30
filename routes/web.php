<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['name' => 'Codante API', 'version' => '1.0.0'];
});

Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
});
