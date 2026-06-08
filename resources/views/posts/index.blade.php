@php
use Illuminate\Support\Facades\Storage;
@endphp

{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')
{{-- タイトルを「投稿一覧」に設定する --}}
@section('title','投稿一覧')
{{-- メインコンテンツの開始 --}}
@section('content')
<h2>投稿一覧</h2>

{{-- 投稿が1件もない場合 --}}
@if($posts->isEmpty())
<p>まだ投稿がありません</p>

{{-- 投稿がある場合はループして表示 --}}
@else
@foreach($posts as $post)
<div>
	<h3>
		<a href="{{ route('posts.show', $post->id)}}">{{$post->title}}</a>
	</h3>

	{{-- 画像がある場合は表示する --}}
	@if($post->image_path)
	<img src="{{Storage::url($post->image_path)}}" alt="{{ $post->title }}" width="300">
	@endif

	<p>{{ $post->body}}</p>
	{{-- タグの表示 --}}
	@if($post->tags->isNotEmpty())
	<div>
		@foreach($post->tags as $tag)
		<span>{{ $tag->name}} </span>
		@endforeach
	</div>
	@endif
	<p>{{ $post->created_at->format('Y/m/d') }}</p>
</div>
@endforeach
@endif
@endsection