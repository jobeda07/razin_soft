<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthAction;
use App\Http\Controllers\Api\TaskAction;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



Route::controller(AuthAction::class)->group(function () {
    Route::post('registration', 'registration');
    Route::get('login', 'login')->name('login');
    Route::post('login', 'login');
    Route::get('logout', 'logout')->middleware(['auth:sanctum']);
});


Route::middleware('auth:sanctum')->group(function () {

    // Profile Routes
    Route::prefix('profile')->controller(AuthAction::class)->group(function () {
        Route::get('/', 'getProfile');
        Route::put('/update', 'updateProfile');
        Route::post('/upload-avatar', 'profile_upload_avatar');
    });

    // Task Routes
    Route::controller(TaskAction::class)->group(function () {
        Route::resource('tasks', TaskAction::class);
        Route::get('/user-list', 'user_list_for_assign'); // for assign user
        Route::post('tasks/{id}/assign', 'task_assign');
        Route::put('tasks/{id}/status', 'task_status');
        Route::get('tasks-summary', 'task_summary');
    });
});
