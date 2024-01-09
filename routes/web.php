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
