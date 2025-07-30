<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BlogPostController;

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
    Route::patch('/posts/{id}', [BlogPostController::class, 'update']);
    Route::patch('/posts/{id}/save', [BlogPostController::class, 'savePost']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function (){
    Route::patch('/posts/{id}/approve', [BlogPostController::class, 'approve']);
});

Route::get('/posts', [BlogPostController::class, 'index']);
