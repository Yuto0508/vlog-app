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
            //任意、画像ファイル・2MB以内
            'image' => 'nullable|image|max:2048',
        ]);

        //画像が送られてきた場合は保存する
        $imagePath = null;
        if ($request->hasFile('image')) {
            // storage/app/public/images/に保存
            $imagePath = $request->file('image')->store('images', 'public');
        }

        // 投稿をDBに保存する
        Post::create([
            // ログイン中のユーザーIDを取得
            'user_id' => auth()->id(),
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $imagePath,
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
        // IDで投稿を取得する
        $post = Post::findOrFail($id);

        //post/show.bladephpに投稿データを渡して表示
        return view('posts.show', compact('post'));
    }

    /**
     * 投稿編集フォームを表示する
     */
    public function edit(string $id)
    {
        //IDで投稿を取得する
        $post = Post::findOrFail($id);

        // posts/edit.blade.phpに投稿データを渡して表示
        return view('posts.edit', compact('post'));
    }

    /**
     * 投稿をDBに更新保存する
     */
    public function update(Request $request, string $id)
    {
        // バリデーション（入力値の検証）
        $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        // IDで投稿を取得する
        $post = Post::findOrFail($id);

        //投稿を更新する
        $post->update([
            'title' => $request->title,
            'body' =>  $request->body,
            'is_public' => $request->has('is_public'),
        ]);
        //詳細ページにリダイレクト
        return redirect()->route('posts.show', $post->id);
    }

    /**
     * 投稿をDBから削除する
     */
    public function destroy(string $id)
    {
        // IDで投稿を取得する
        $post = Post::findOrFail($id);

        // 投稿を削除する
        $post->delete();

        //投稿一覧ページにリダイレクト
        return redirect()->route('posts.index');
    }
}
