{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')
{{-- タイトルを「投稿編集」に設定する --}}
@section('title', '投稿編集')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿編集</h2>
@endsection

{{-- メインコンテンツの開始 --}}
@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 投稿編集フォーム --}}
                    <form action="{{ route('posts.update', $post->id) }}" method="POST" enctype="multipart/form-data"
                        class="space-y-6">
                        {{-- CSRFトークン（セキュリティ対策） --}}
                        @csrf
                        {{-- PUTメソッドを擬似的に送る --}}
                        @method('PUT')

                        {{-- タイトル入力欄 --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                            <input type="text" name="title" id="title" value="{{ $post->title }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- 本文入力欄 --}}
                        <div>
                            <label for="post-body" class="block text-sm font-medium text-gray-700">本文</label>
                            <textarea name="body" id="post-body" rows="6"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $post->body }}</textarea>
                        </div>

                        {{-- 公開設定 --}}
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_public" value="1"
                                    {{ $post->is_public ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-700">公開する</span>
                            </label>
                        </div>

                        @php
                            use Illuminate\Support\Facades\Storage;
                        @endphp
                        @if ($post->image_path)
                            <div>
                                <p class="text-sm text-gray-700">現在の画像</p>
                                <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}"
                                    class="mt-2 w-48 rounded-md">
                            </div>
                        @endif

                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">画像を変更する場合は選択してください</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-700">
                        </div>

                        {{-- タグ選択欄 --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">タグ</label>
                            <div class="mt-2 flex flex-wrap gap-4">
                                @foreach ($tags as $tag)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                            {{-- この投稿にすでにタグが紐付いていればcheckedをつける --}} {{ $post->tags->contains($tag->id) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 更新ボタン --}}
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            更新する
                        </button>
                    </form>

                    {{-- 詳細ページに戻るリンク --}}
                    <div class="mt-6">
                        <a href="{{ route('posts.show', $post->id) }}"
                            class="text-sm text-gray-500 hover:text-gray-700 hover:underline">← 戻る</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
