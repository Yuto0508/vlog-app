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
	<h3>{{ $post->title}}</h3>
	<p>{{ $post->body}}</p>
	<p>{{ $post->created_at->format('Y/m/d') }}</p>
</div>
@endforeach
@endif
@endsection