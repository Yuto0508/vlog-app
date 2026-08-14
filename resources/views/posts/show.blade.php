@php
    use Illuminate\Support\Facades\Storage;
@endphp

{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')

{{-- タイトルを投稿タイトルに設定する --}}
@section('title', $post->title)

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $post->title }}</h2>
@endsection

{{-- メインコンテンツの開始 --}}
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 画像がある場合は表示する --}}
                    @if ($post->image_path)
                        <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}"
                            class="w-full max-w-xs rounded-md">
                    @endif

                    <p class="mt-4 whitespace-pre-line">{{ $post->body }}</p>

                    {{-- タグの表示 --}}
                    @if ($post->tags->isNotEmpty())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <span
                                    class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-full">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    <p class="mt-4 text-sm text-gray-500">{{ $post->created_at->format('Y/m/d') }}</p>

                    {{-- 管理者にのみ編集・削除ボタンを表示 --}}
                    @auth
                        @if (Auth::user()->is_admin)
                            <div class="mt-6 flex items-center gap-4">
                                {{-- 編集ページへのリンク --}}
                                <a href="{{ route('posts.edit', $post->id) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    編集
                                </a>

                                {{-- 削除フォーム --}}
                                <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        削除
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth

                    {{-- 一覧に戻るリンク --}}
                    <div class="mt-6">
                        <a href="{{ route('posts.index') }}"
                            class="text-sm text-gray-500 hover:text-gray-700 hover:underline">← 一覧に戻る</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
