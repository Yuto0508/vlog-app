@extends('layouts.app')

@section('title','投稿作成')

@section('content')
<h2>投稿作成</h2>

{{--投稿作成フォーム--}}
<form action="{{ route('posts.store')}}" method="POST">

	@csrf

	{{-- タイトル入力欄 --}}
	<div>
		<label for="title">タイトル</label>
		<input type="text" name="title" id="title">
	</div>

	{{-- 本文入力欄 --}}
	<div>
		<label for="post-body">本文</label>
		<textarea name="body" id="post-body"></textarea>
	</div>

	{{-- 公開設定 --}}
	<div>
		<label>
			<input type="checkbox" name="is_public" value="1">
			公開する
		</label>
	</div>

	{{-- 送信ボタン --}}
	<button type="submit">投稿する</button>
</form>

@endsection