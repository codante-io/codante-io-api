<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('challenge', 'ChallengeCrudController');
    Route::crud('instructor', 'InstructorCrudController');
    Route::crud('lesson', 'LessonCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('track', 'TrackCrudController');
    Route::crud('user', 'UserCrudController');
    Route::crud('workshop', 'WorkshopCrudController');
    Route::crud('blog-post', 'BlogPostCrudController');
}); // this should be the absolute last line of this file