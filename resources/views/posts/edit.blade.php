{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')
{{-- タイトルを「投稿編集」に設定する --}}
@section('title', '投稿編集')
{{-- メインコンテンツの開始 --}}
@section('content')
<h2>投稿編集</h2>
{{-- 投稿編集フォーム --}}
<form action="{{ route('posts.update', $post->id)}}" method="POST" enctype="multipart/form-data">
	{{-- CSRFトークン（セキュリティ対策） --}}
	@csrf
	{{-- PUTメソッドを擬似的に送る --}}
	@method('PUT')

	{{-- タイトル入力欄 --}}
	<div>
		<label for="title">タイトル</label>
		<input type="text" name="title" id="title" value="{{ $post->title }}">
	</div>

	{{-- 本文入力欄 --}}
	<div>
		<label for="post-body">本文</label>
		<textarea name="body" id="post-body">{{ $post->body}}</textarea>
	</div>

	{{-- 公開設定 --}}
	<div>
		<label>
			<input type="checkbox" name="is_public" value="1"
				{{ $post->is_public ? 'checked' : ''}}>
			公開する
		</label>
	</div>

	@php
	use Illuminate\Support\Facades\Storage;
	@endphp
	@if($post->image_path)
	<div>
		<p>現在の画像</p>
		<img src="{{ Storage::url($post->image_path)}}" alt="{{$post->title}}" width="200">
	</div>
	@endif

	<div>
		<label for="image">画像を変更する場合は選択してください</label>
		<input type="file" name="image" id="image" accept="image/*">
	</div>

	{{-- タグ選択欄 --}}
	<div>
		<label>タグ</label>
		@foreach($tags as $tag)
		<label>
			<input type="checkbox" name="tags[]" value="{{ $tag->id}}"
				{{-- この投稿にすでにタグが紐付いていればcheckedをつける --}}
				{{ $post->tags->contains($tag->id) ? 'checked' : ''}}>
			{{ $tag->name }}
		</label>
		@endforeach
	</div>

	{{-- 更新ボタン --}}
	<button type="submit">更新する</button>
</form>

{{-- 詳細ページに戻るリンク --}}
<a href="{{ route('posts.show', $post->id)}}">戻る</a>
@endsection