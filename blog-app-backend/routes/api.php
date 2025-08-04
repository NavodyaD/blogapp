<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\PostCommentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login',[AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register']);

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin/blogs', [BlogController::class, 'index']);

Route::middleware(['auth:sanctum', 'role:writer'])->group(function (){
    Route::post('/posts', [BlogPostController::class, 'store']);
    Route::delete('/posts/{id}', [BlogPostController::class, 'destroy']);
    Route::get('/own-posts', [BlogPostController::class, 'ownPosts']);
    Route::get('/own-drafts', [BlogPostController::class, 'ownDrafts']);
    Route::patch('/posts/{id}', [BlogPostController::class, 'update']);
    Route::patch('/posts/{id}/save', [BlogPostController::class, 'savePost']);
    Route::post('/comments', [PostCommentController::class, 'store']);
    Route::post('/writer-logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function (){
    Route::delete('/posts/{id}', [BlogPostController::class, 'destroy']);
    Route::patch('/posts/{id}/approve', [BlogPostController::class, 'approve']);
    Route::get('/all-posts', [BlogPostController::class, 'allPosts']);
    Route::post('/admin-logout', [AuthController::class, 'logout']);
});

Route::get('/posts', [BlogPostController::class, 'index']);
Route::get('/posts/{id}', [BlogPostController::class, 'getSinglePost']);

Route::get('/comments/{id}', [PostCommentController::class, 'getComments']);
//Route::middleware('auth:sanctum')->post('/comments', [PostCommentController::class, 'store']);





