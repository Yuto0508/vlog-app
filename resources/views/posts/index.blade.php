@php
    use Illuminate\Support\Facades\Storage;
@endphp

{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')
{{-- タイトルを「投稿一覧」に設定する --}}
@section('title', '投稿一覧')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">投稿一覧</h2>
        @auth
            @if (Auth::user()->is_admin)
                <a href="{{ route('posts.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('新規投稿') }}
                </a>
            @endif
        @endauth
    </div>
@endsection

{{-- メインコンテンツの開始 --}}
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- 投稿が1件もない場合 --}}
            @if ($posts->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <p class="p-6 text-gray-900">まだ投稿がありません</p>
                </div>
            @else
                {{-- 投稿がある場合はループして表示 --}}
                <ul class="space-y-6">
                    @foreach ($posts as $post)
                        <li class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-semibold">
                                    <a href="{{ route('posts.show', $post->id) }}"
                                        class="hover:underline">{{ $post->title }}</a>
                                </h3>

                                {{-- 画像がある場合は表示する --}}
                                @if ($post->image_path)
                                    <img src="{{ Storage::url($post->image_path) }}" alt="{{ $post->title }}"
                                        class="mt-4 w-full max-w-xs rounded-md">
                                @endif

                                <p class="mt-4">{{ $post->body }}</p>

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
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
