<?php

use App\Http\Controllers\BugsnagWebhookController;
use App\Http\Controllers\PagarmeController;
use App\Http\Controllers\PagarmeWebhooks;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserResource;
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

Route::get("/user", function (Request $request) {
    if ($request->user()) {
        // o json deve estar aqui para retirar o wrapper "data": https://stackoverflow.com/a/66464348
        return response()->json(new UserResource($request->user()));
    }

    return ["message" => "Não autenticado"];
})->middleware("auth:sanctum");

Route::put("/user", [UserController::class, "update"])->middleware(
    "auth:sanctum"
);

Route::post("/user/discord", [
    UserController::class,
    "updateDiscord",
])->middleware("auth:sanctum");

Route::get("/user/subscriptions", function (Request $request) {
    return response()->json(new UserResource($request->user()));
})->middleware("auth:sanctum");

Route::get("/workshops", function () {
    return \App\Models\Workshop::all();
});

Route::get("/workshops/{slug}", function ($slug) {
    return \App\Models\Workshop::where("slug", $slug)->firstOrFail();
});

//BugsnagWebhook
Route::post("bugsnag/notification", [
    BugsnagWebhookController::class,
    "notify",
]);

// Pagarme Webhook
Route::post("pagarme/notification", [PagarmeWebhooks::class, "handleWebhook"]);

Route::get("/pagarme/get-link", [
    PagarmeController::class,
    "createOrderAndGetCheckoutLink",
])->middleware("auth:sanctum");

Route::get("/pagarme/get-subscription-by-order-id/{pagarmeOrderID}", [
    PagarmeController::class,
    "getSubscriptionByPagarmeOrderId",
])->middleware("auth:sanctum");
