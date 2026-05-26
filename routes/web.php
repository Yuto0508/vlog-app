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

// 投稿一覧は誰でも見られる
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// 投稿の作成・編集・削除はログイン済みユーザーのみ（ワイルドカードより前に定義）
Route::middleware('auth')->group(function () {
    Route::resource('posts', PostController::class)->except(['index', 'show']);
});

// ワイルドカードルートは最後に定義（/posts/create などに先にマッチさせないため）
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');

// プロフィール編集
Route::middleware('auth')->group(function () {
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ログイン後のダッシュボード
Route::get('/dashboard', function () {
    return redirect()->route('posts.index');
})->middleware('auth')->name('dashboard');

require __DIR__ . '/auth.php';
