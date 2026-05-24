<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post; // Postモデルを読み込む

class PostController extends Controller
{
    /**
     * 投稿一覧を表示する
     */
    public function index()
    {
        // 公開されている投稿を新しい順に取得する
        $posts = Post::where('is_public', true)
            // 作成日時の降順（新しい順）
            ->orderBy('created_at', 'desc')
            // クエリを実行してDBから取得
            ->get();

        // posts/index.blade.phpに投稿データを渡して表示
        return view('posts.index', compact('posts'));
    }

    /**
     * 投稿作成フォームを表示する
     */
    public function create()
    {
        //投稿作成フォームを表示する
        return view('posts.create');
    }

    /**
     * 投稿をDBに保存する
     */
    public function store(Request $request)
    {
        // バリデーション（入力値の検証）
        $request->validate([
            // 必須・255文字以内
            'title' => 'required |max:255',
            // 必須
            'body' => 'required',
        ]);

        // 投稿をDBに保存する
        Post::create([
            // 仮のユーザーID（認証実装後に変更予定）
            'user_id' => 1,
            'title' => $request->title,
            'body' => $request->body,
            'is_public' => $request->has('is_public'),
        ]);

        // 投稿一覧ページにリダイレクト
        return redirect()->route('posts.index');
    }

    /**
     * 投稿の詳細を表示する
     */
    public function show(string $id)
    {
        //
    }

    /**
     * 投稿編集フォームを表示する
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * 投稿をDBに更新保存する
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * 投稿をDBから削除する
     */
    public function destroy(string $id)
    {
        //
    }
}
