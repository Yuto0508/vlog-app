{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')

{{-- タイトルを「ホーム」に設定する --}}
@section('title', 'ホーム')

{{-- メインコンテンツの開始 --}}
@section('content')
<h2>ようこそ！</h2>
<p>vlogへようこそ！</p>
@endsection
{{-- メインコンテンツの終了 --}}