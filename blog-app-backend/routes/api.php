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

Route::middleware(['auth:sanctum', 'role:writer'])->group(function (){
    Route::post('/posts', [BlogPostController::class, 'store']);
    Route::get('/own-posts', [BlogPostController::class, 'ownPosts']);
    Route::get('/own-drafts', [BlogPostController::class, 'ownDrafts']);
    Route::patch('/posts/{id}', [BlogPostController::class, 'update']);
    Route::patch('/posts/{id}/save', [BlogPostController::class, 'savePost']);
    Route::post('/comments', [PostCommentController::class, 'store']);
    Route::post('/writer-logout', [AuthController::class, 'logout']);
    Route::post('/posts/react/{id}', [PostReactionController::class, 'toggleReaction']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function (){
    Route::patch('/posts/{id}/approve', [BlogPostController::class, 'approve']);
    Route::get('/all-posts', [BlogPostController::class, 'allPosts']);
    Route::post('/admin-logout', [AuthController::class, 'logout']);
    Route::get('/posts/pending', [BlogPostController::class, 'getPendingPosts']);
    Route::get('/admin/insights', [AdminDashboardController::class, 'getInsights']);
    Route::post('/admin/register',[AuthController::class, 'registerAdmin']);
    Route::get('/admin/list', [AdminDashboardController::class, 'listAdmins']);
    Route::post('/admin/delete', [AuthController::class, 'deleteAdmin']);
});

Route::middleware(['auth:sanctum', 'role:admin|writer'])->group(function (){
    Route::delete('/posts/{id}', [BlogPostController::class, 'destroy']);
});

Route::get('/posts', [BlogPostController::class, 'index']);
Route::get('/posts/{id}', [BlogPostController::class, 'getSinglePost']);
Route::get('/comments/{id}', [PostCommentController::class, 'getComments']);
Route::get('/posts/reactions/{id}', [PostReactionController::class, 'getReactions']);
Route::get('/admin/insights/top-liked', [BlogPostController::class, 'topLikedPosts']);
Route::get('/admin/insights/top-commented', [BlogPostController::class, 'topCommentedPosts']);
Route::post('/posts/search', [BlogPostController::class, 'searchPosts']);

Route::middleware(['auth:sanctum'])->group(function (){
    Route::delete('comments/{id}', [PostCommentController::class, 'destroyComment']);
    Route::get('/current-user', [AuthController::class, 'getCurrentUser']);
    Route::get('/user/comments', [PostCommentController::class, 'getUserComments']);
});



