@extends('layouts.app')

@section('title', '投稿作成')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿作成</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 投稿作成フォーム --}}
                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- タイトル入力欄 --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">タイトル</label>
                            <input type="text" name="title" id="title"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- 本文入力欄 --}}
                        <div>
                            <label for="post-body" class="block text-sm font-medium text-gray-700">本文</label>
                            <textarea name="body" id="post-body" rows="6"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        {{-- 公開設定 --}}
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_public" value="1"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-700">公開する</span>
                            </label>
                        </div>

                        {{-- 画像アップロード欄 --}}
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">画像</label>
                            <input type="file" name="image" id="image" accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-700">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">タグ</label>
                            <div class="mt-2 flex flex-wrap gap-4">
                                @foreach ($tags as $tag)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ms-2 text-sm text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 送信ボタン --}}
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            投稿する
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
