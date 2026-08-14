<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 投稿一覧を返す
     */
    public function index()
    {
        $posts = Post::where('is_public', true)
            ->latest()
            ->get();

        return response()->json($posts);
    }

    /**
     * 投稿を新規作成する
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'is_public' => 'boolean',
        ]);

        $post = $request->user()->posts()->create($validated);

        return response()->json($post, 201);
    }

    /**
     * 投稿の詳細を返す
     */
    public function show(Request $request, Post $post)
    {
        // 非公開投稿は投稿者本人以外は閲覧不可
        // このルートには auth:sanctum ミドルウェアがかからないため、
        // デフォルトガード(web)ではなく明示的に sanctum ガードでユーザーを解決する
        if (! $post->is_public && $request->user('sanctum')?->id !== $post->user_id) {
            abort(404);
        }

        return response()->json($post);
    }

    /**
     * 投稿を更新する
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'is_public' => 'boolean',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    /**
     * 投稿を削除する
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(null, 204);
    }
}