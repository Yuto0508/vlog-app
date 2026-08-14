<?php

use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// 投稿一覧・詳細は誰でも見られる
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}', [PostController::class, 'show']);

// 作成・更新・削除は管理者(トークン認証済み)のみ
Route::middleware(['auth:sanctum', 'admin'])->group(function(){
    Route::post('/posts',[PostController::class, 'store']);
    Route::put('/posts/{post}',[PostController::class,'update']);
    Route::delete('/posts/{post}',[PostController::class,'destroy']);
});