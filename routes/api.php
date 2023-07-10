<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthenticationController;

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::post('/posts',[PostController::class,'store']);
    Route::patch('/posts/{id}',[PostController::class,'update']);
    Route::delete('/posts/{id}', [PostController::class,'destroy']);
    
});

Route::get('/posts',[PostController::class,'index']);
Route::get('/posts/{id}',[PostController::class,'show']);

Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {
    Route::post('register', [AuthController::class,'register']);
    Route::post('login', [AuthController::class,'login']);
    Route::post('logout',  [AuthController::class,'logout']);
    Route::post('refresh',  [AuthController::class,'refresh']);
    Route::post('me',  [AuthController::class,'me']);
    
});