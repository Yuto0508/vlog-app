<?php

// ルーティングの読み込み
use Illuminate\Support\Facades\Route;
// HomeControllerの読み込み
use App\Http\Controllers\HomeController;
// PostControllerの読み込み
use App\Http\Controllers\PostController;

// トップページ（/）にアクセスしたらwelcomeビューを表示する
Route::get('/', function () {
    return view('welcome');
});

// /homeにアクセスしたらHomeControllerのindexメソッドを実行する
Route::get('/home', [HomeController::class, 'index']);

// 投稿のCRUDルートを一括で定義する
Route::resource('posts', PostController::class);
