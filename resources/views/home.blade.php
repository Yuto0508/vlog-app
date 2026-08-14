{{-- layouts/app.blade.phpを親レイアウトとして使う --}}
@extends('layouts.app')

{{-- タイトルを「ホーム」に設定する --}}
@section('title', 'ホーム')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">ようこそ！</h2>
@endsection

{{-- メインコンテンツの開始 --}}
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <p class="p-6 text-gray-900">vlogへようこそ！</p>
            </div>
        </div>
    </div>
@endsection
{{-- メインコンテンツの終了 --}}
