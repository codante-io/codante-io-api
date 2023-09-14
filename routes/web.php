<?php

use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CustomTestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TechnicalAssessmentController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\WorkshopController;
use App\Models\TechnicalAssessment;
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

Route::get("/", function () {
    return ["name" => "Codante API", "version" => "1.0.0"];
});

Route::get("/workshops", [WorkshopController::class, "index"]);
Route::get("/workshops/{slug}", [WorkshopController::class, "show"]);

Route::get("/instructors", [InstructorController::class, "index"]);
Route::get("/instructors/{slug}", [InstructorController::class, "show"]);

Route::get("/reactions", [ReactionController::class, "getReactions"]);
Route::post("/reactions", [ReactionController::class, "toggle"])->middleware(
    "auth:sanctum"
);

Route::get("/challenges", [ChallengeController::class, "index"]);
Route::get("/challenges/{slug}", [ChallengeController::class, "show"]);
Route::post("/challenges/{slug}/submit", [
    ChallengeController::class,
    "submit",
])->middleware("auth:sanctum");
Route::get("/challenges/{slug}/submissions", [
    ChallengeController::class,
    "getSubmissions",
]);

Route::get("/challenges/{slug}/joined", [
    ChallengeController::class,
    "userJoined",
])->middleware("auth:sanctum");
Route::get("/challenges/{slug}/forked", [
    ChallengeController::class,
    "hasForkedRepo",
])->middleware("auth:sanctum");
Route::get("/challenges/{slug}/participants", [
    ChallengeController::class,
    "getChallengeParticipantsBanner",
]);
Route::post("/challenges/{slug}/join", [
    ChallengeController::class,
    "join",
])->middleware("auth:sanctum");
Route::put("/challenges/{slug}", [
    ChallengeController::class,
    "updateChallengeUser",
])->middleware("auth:sanctum");

Route::get("/tracks", [TrackController::class, "index"]);
Route::get("/tracks/{slug}", [TrackController::class, "show"]);

Route::get("/home", [HomeController::class, "index"]);

Route::post("/dashboard/change-name", [
    DashboardController::class,
    "changeUserName",
]);
Route::post("/dashboard/change-password", [
    DashboardController::class,
    "changePassword",
]);

Route::post("/lessons/{lesson}/completed", [
    LessonController::class,
    "setCompleted",
])->middleware("auth:sanctum");
Route::post("/lessons/{lesson}/uncompleted", [
    LessonController::class,
    "setUncompleted",
])->middleware("auth:sanctum");

Route::get("/upcoming", [CalendarController::class, "showCalendar"]);
Route::get("/custom-test", [CustomTestController::class, "handle"]);

Route::get("/blog-posts", [BlogPostController::class, "index"]);
Route::get("/blog-posts/{slug}", [BlogPostController::class, "show"]);

Route::get("/technical-assessments", [
    TechnicalAssessmentController::class,
    "index",
]);
Route::get("/technical-assessments/{slug}", [
    TechnicalAssessmentController::class,
    "show",
]);

Route::get("/ranking", [RankingController::class, "getRanking"]);

Route::post('/subscribe/{plan_id}', [SubscriptionController::class, 'subscribe'])->middleware('auth:sanctum');
// Route::get('/lesson-thumbnail/{lesson}', [LessonController::class, 'getLessonThumbnail']);

require __DIR__ . "/auth.php";
