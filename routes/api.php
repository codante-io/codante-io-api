<?php

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    if ($request->user()) {
        return $request->user();
    }
    return ["message" => "Não autenticado"];
})->middleware('auth:sanctum');

Route::get('/courses', function () {
    return \App\Models\Course::all();
});

Route::get('/courses/{slug}', function ($slug) {
    return \App\Models\Course::where('slug', $slug)->firstOrFail();
});
