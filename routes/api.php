<?php

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

    return ['message' => 'Não autenticado'];
})->middleware('auth:sanctum');

Route::get('/workshops', function () {
    return \App\Models\Workshop::all();
});

Route::get('/workshops/{slug}', function ($slug) {
    return \App\Models\Workshop::where('slug', $slug)->firstOrFail();
});
