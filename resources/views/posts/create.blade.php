@extends('layouts.app')

@section('title','投稿作成')

@section('content')
<h2>投稿作成</h2>

{{--投稿作成フォーム--}}
<form action="{{ route('posts.store')}}" method="POST" enctype="multipart/form-data">

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

	{{-- 画像アップロード欄 --}}
	<div>
		<label for="image">画像</label>
		<input type="file" name="image" id="image" accept="image/*">
	</div>

	<div>
		<label>タグ</label>
		@foreach($tags as $tag)
		<label>
			<input type="checkbox" name="tags[]" value="{{$tag->id}}">
			{{ $tag->name}}
		</label>
		@endforeach
	</div>

	{{-- 送信ボタン --}}
	<button type="submit">投稿する</button>
</form>

@endsection