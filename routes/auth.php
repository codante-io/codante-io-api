<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\GithubLoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::post("/register", [RegisteredUserController::class, "store"])
    ->middleware("guest.api")
    ->name("register");

Route::post("/login", [AuthenticatedSessionController::class, "store"])
    ->middleware("guest.api")
    ->name("login");

Route::post("/impersonate", [
    AuthenticatedSessionController::class,
    "impersonate",
])->middleware("auth:sanctum");

Route::post("github-login", [GithubLoginController::class, "githubLogin"])
    ->middleware("guest.api")
    ->name("github.login");

Route::post("/forgot-password", [PasswordResetLinkController::class, "store"])
    ->middleware("guest.api")
    ->name("password.email");

Route::post("/reset-password", [NewPasswordController::class, "store"])
    ->middleware("guest.api")
    ->name("password.store");

// Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
//   ->middleware(['auth', 'signed', 'throttle:6,1'])
//   ->name('verification.verify');

// Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//   ->middleware(['auth', 'throttle:6,1'])
//   ->name('verification.send');

Route::post("/logout", [AuthenticatedSessionController::class, "destroy"])
    ->middleware("auth:sanctum")
    ->name("logout");
