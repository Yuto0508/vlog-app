@php
use Illuminate\Support\Facades\Storage;
@endphp

{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')

{{-- タイトルを投稿タイトルに設定する --}}
@section('title', $post->title)

{{-- メインコンテンツの開始 --}}
@section('content')
<h2>{{ $post->title}}</h2>

{{-- 画像がある場合は表示する --}}
@if($post->image_path)
<img src="{{Storage::url($post->image_path)}}" alt="{{$post->title}}" width="300">
@endif

<p>{{ $post->body}}</p>
<p>{{ $post->created_at->format('Y/m/d')}}</p>

{{-- 編集・削除ボタン --}}
<div>
	{{-- 編集ページへのリンク --}}
	<a href="{{ route('posts.edit', $post->id)}}">編集</a>

	{{-- 削除フォーム --}}
	<form action="{{ route('posts.destroy',$post->id)}}" method="POST">
		@csrf
		@method('DELETE')
		<button type="submit">削除</button>
	</form>
</div>

{{-- 一覧に戻るリンク --}}
<a href="{{ route('posts.index')}}">一覧に戻る</a>
@endsection