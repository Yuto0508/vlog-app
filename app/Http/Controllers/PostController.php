<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Postモデルを読み込む
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Tag;

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
        // タグ一覧を取得してViewに渡す
        $tags = Tag::all();
        return view('posts.create', compact('tags'));
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
        $post = Post::create([
            // ログイン中のユーザーIDを取得
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $imagePath,
            'is_public' => $request->has('is_public'),
        ]);

        // タグを紐付ける
        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

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

        //タグ一覧を取得する(tagsテーブルから全タグを取得)
        $tags = Tag::all();

        // posts/edit.blade.phpに投稿データを渡して表示(editビューにpostsとtagsを渡す)
        return view('posts.edit', compact('post', 'tags'));
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
            'image' => 'nullable|image|max:2048',
        ]);

        // IDで投稿を取得する
        $post = Post::findOrFail($id);

        // 新しい画像が送られてきた場合は保存する
        // 既存の画像パスを保持
        $imagePath = $post->image_path;
        if ($request->hasFile('image')) {
            //古い画像を削除する
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            //新しい画像を保存する
            $imagePath = $request->file('image')->store('images', 'public');
        }

        //投稿を更新する
        $post->update([
            'title' => $request->title,
            'body' =>  $request->body,
            'image_path' => $imagePath,
            'is_public' => $request->has('is_public'),
        ]);

        //タグを更新する（既存タグを削除して新しいタグを紐づける）
        // $request->tagsは [1, 3, 5] のような配列
        // sync()はこの配列のIDと一致するタグだけを紐付ける
        $post->tags()->sync($request->tags ?? []);

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
