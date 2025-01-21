<?php

use App\Http\Controllers\AdminApi\WorkshopAdminController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\BugsnagWebhookController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CustomTestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\PagarmeController;
use App\Http\Controllers\PagarmeWebhooks;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TechnicalAssessmentController;
use App\Http\Controllers\TrackableController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TrackItemController;
use App\Http\Controllers\WorkshopController;
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

Route::get('/user', function (Request $request) {
    if ($request->user()) {
        // o json deve estar aqui para retirar o wrapper "data": https://stackoverflow.com/a/66464348
        return response()->json(new UserResource($request->user()));
    }

    return ['message' => 'Não autenticado'];
})->middleware('auth:sanctum');

Route::post('/user/discord', [
    DiscordController::class,
    'handleDiscordLoginButton',
])->middleware('auth:sanctum');

Route::get('/user/subscriptions', function (Request $request) {
    return response()->json(new UserResource($request->user()));
})->middleware('auth:sanctum');

Route::post('/workshops/{slug}/joined', [
    WorkshopController::class,
    'userJoined',
]);

//BugsnagWebhook
Route::post('bugsnag/notification', [
    BugsnagWebhookController::class,
    'notify',
]);

// Pagarme Webhook
Route::post('pagarme/notification', [PagarmeWebhooks::class, 'handleWebhook']);

Route::get('/pagarme/get-link', [
    PagarmeController::class,
    'createOrderAndGetCheckoutLink',
])->middleware('auth:sanctum');

Route::get('/pagarme/get-subscription-by-order-id/{pagarmeOrderID}', [
    PagarmeController::class,
    'getSubscriptionByPagarmeOrderId',
])->middleware('auth:sanctum');

Route::get('/workshops', [WorkshopController::class, 'index'])->middleware(
    'cache.headers:public;max_age=120;etag'
);

Route::get('/workshops/{slug}', [WorkshopController::class, 'show']);
Route::get('/lessons/{slug}', [LessonController::class, 'show']);

Route::get('/instructors', [InstructorController::class, 'index']);
Route::get('/instructors/{slug}', [InstructorController::class, 'show']);

Route::get('/reactions', [ReactionController::class, 'getReactions']);
Route::post('/reactions', [ReactionController::class, 'toggle'])->middleware(
    'auth:sanctum'
);

Route::post('/certificates', [
    CertificateController::class,
    'create',
])->middleware('auth:sanctum');
Route::get('/certificates/{id}', [CertificateController::class, 'showById']);
Route::get('/challenges/{slug}/certificate', [
    CertificateController::class,
    'show',
])->middleware('auth:sanctum');

Route::get('/challenges', [ChallengeController::class, 'index']);
Route::get('/challenges/featured', [
    ChallengeController::class,
    'getFeaturedChallenge',
]);

Route::get('/challenges/{slug}', [ChallengeController::class, 'show']);

// Submit with Deploy URL
Route::post('/challenges/{slug}/submit', [
    ChallengeController::class,
    'submit',
])->middleware('auth:sanctum');

// Submit with Image
Route::post('/challenges/{slug}/submit-without-deploy', [
    ChallengeController::class,
    'submitWithoutDeploy',
])->middleware('auth:sanctum');

Route::put('/challenges/{slug}/submit', [
    ChallengeController::class,
    'updateSubmission',
])->middleware('auth:sanctum');

Route::get('/challenges/{slug}/submissions', [
    ChallengeController::class,
    'getSubmissions',
]);

Route::get('/challenges/{slug}/submissions/{github_user}', [
    ChallengeController::class,
    'getSubmissionFromGithubUser',
]);

Route::get('/challenges/{slug}/joined', [
    ChallengeController::class,
    'userJoined',
])->middleware('auth:sanctum');
Route::get('/challenges/{slug}/forked', [
    ChallengeController::class,
    'hasForkedRepo',
])->middleware('auth:sanctum');
Route::get('/challenges/{slug}/participants', [
    ChallengeController::class,
    'getChallengeParticipantsBanner',
]);
Route::post('/challenges/{slug}/join', [
    ChallengeController::class,
    'join',
])->middleware('auth:sanctum');
Route::put('/challenges/{slug}', [
    ChallengeController::class,
    'updateChallengeUser',
])->middleware('auth:sanctum');

Route::get('/tracks', [TrackController::class, 'index']);
Route::get('/tracks/{slug}', [TrackController::class, 'show']);

Route::get('/track-items', [TrackItemController::class, 'index']);
Route::get('/track-items/{slug}', [TrackItemController::class, 'show']);

Route::post('/trackables/{trackableId}/complete', [
    TrackableController::class,
    'markAsCompleted',
])->middleware('auth:sanctum');

Route::get('/home', [HomeController::class, 'index']);

Route::post('/dashboard/change-name', [
    DashboardController::class,
    'changeUserName',
]);
Route::post('/dashboard/change-password', [
    DashboardController::class,
    'changePassword',
]);
Route::post('/dashboard/change-linkedin-url', [
    DashboardController::class,
    'changeLinkedinUser',
]);
Route::post('/dashboard/update-settings', [
    DashboardController::class,
    'updateSettings',
]);

Route::get('/dashboard/show-data', [
    DashboardController::class,
    'getDashboardData',
])->middleware('auth:sanctum');

Route::get('/dashboard/workshops', [
    DashboardController::class,
    'getWorkshops',
]);

Route::post('/dashboard/change-avatar', [
    DashboardController::class,
    'changeAvatar',
]);

Route::post('/lessons/{lesson}/completed', [
    LessonController::class,
    'setCompleted',
])->middleware('auth:sanctum');
Route::post('/lessons/{lesson}/uncompleted', [
    LessonController::class,
    'setUncompleted',
])->middleware('auth:sanctum');

Route::get('/upcoming', [CalendarController::class, 'showCalendar']);
Route::get('/custom-test', [CustomTestController::class, 'handle']);

Route::get('/blog-posts', [BlogPostController::class, 'index']);
Route::get('/blog-posts/{slug}', [BlogPostController::class, 'show']);
Route::get('/pages/{slug}', [BlogPostController::class, 'showPage']);

Route::get('/technical-assessments', [
    TechnicalAssessmentController::class,
    'index',
]);
Route::get('/technical-assessments/{slug}', [
    TechnicalAssessmentController::class,
    'show',
]);

Route::get('/ranking', [RankingController::class, 'getRanking']);

Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
Route::get('/my-subscription', [
    SubscriptionController::class,
    'showSubscription',
]);

Route::get('plan-details', [SubscriptionController::class, 'getPlanDetails']);

Route::post('/comments', [CommentController::class, 'create'])->middleware(
    'auth:sanctum'
);
Route::put('/comments', [CommentController::class, 'update'])->middleware(
    'auth:sanctum'
);
Route::delete('/comments', [CommentController::class, 'delete'])->middleware(
    'auth:sanctum'
);

Route::post('/leads', [LeadsController::class, 'create']);

Route::get('/sitemap', [HomeController::class, 'sitemap']);

// Rotas de admin
Route::post('/get-unused-slug', [LessonController::class, 'getUnusedSlug']);
Route::post('/get-admin-api-token', []);
Route::put('/custom-admin/workshops/{id}', [
    WorkshopAdminController::class,
    'editWorkshop',
]);

require __DIR__.'/auth.php';

Route::fallback(function () {
    return response()->json(['message' => 'Not Found'], 404);
});

Route::post('/send-discord-notification', [
    DiscordController::class,
    'sendMessage',
]);
