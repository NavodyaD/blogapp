<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\AdminDashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login',[AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'role:writer'])->group(function () {

    Route::controller(BlogPostController::class)->group(function () {
        Route::post('/posts', 'store');
        Route::get('/own-posts', 'ownPosts');
        Route::get('/own-drafts', 'ownDrafts');
        Route::patch('/posts/{id}', 'update');
        Route::patch('/posts/{id}/save', 'savePost');
    });

    Route::controller(PostCommentController::class)->group(function () {
        Route::post('/comments', 'store');
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/writer-logout', 'logout');
    });

    Route::controller(PostReactionController::class)->group(function () {
        Route::post('/posts/react/{id}', 'toggleReaction');
    });
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::controller(BlogPostController::class)->group(function () {
        Route::patch('/posts/{id}/approve', 'approve');
        Route::get('/all-posts', 'allPosts');
        Route::get('/posts/pending', 'getPendingPosts');
    });

    Route::controller(AuthController::class)->group(function () {
        Route::post('/admin-logout', 'logout');
        Route::post('/admin/register', 'registerAdmin');
        Route::post('/admin/delete', 'deleteAdmin');
    });

    Route::controller(AdminDashboardController::class)->group(function () {
        Route::get('/admin/insights', 'getInsights');
        Route::get('/admin/list', 'listAdmins');
    });
});

Route::middleware(['auth:sanctum', 'role:admin|writer'])->group(function (){
    Route::delete('/posts/{id}', [BlogPostController::class, 'destroy']);
});

// public
Route::controller(BlogPostController::class)->group(function () {
    Route::get('/posts', 'index');
    Route::get('/posts/{id}', 'getSinglePost');
    Route::post('/posts/search', 'searchPosts');
    Route::get('/admin/insights/top-liked', 'topLikedPosts');
    Route::get('/admin/insights/top-commented', 'topCommentedPosts');
});

Route::controller(PostCommentController::class)->group(function () {
    Route::get('/comments/{id}', 'getComments');
});

Route::controller(PostReactionController::class)->group(function () {
    Route::get('/posts/reactions/{id}', 'getReactions');
});

// Authenticated - Sanctum
Route::middleware(['auth:sanctum'])->group(function (){
    Route::delete('comments/{id}', [PostCommentController::class, 'destroyComment']);
    Route::get('/current-user', [AuthController::class, 'getCurrentUser']);
    Route::get('/user/comments', [PostCommentController::class, 'getUserComments']);
});



