@extends('layouts.app')

@section('title', $year . '年' . $month . '月のカレンダー')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $year }}年{{ $month }}月</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- 前月・次月のナビゲーション --}}
                    <div class="flex justify-between mb-4">
                        <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                            class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
                            ←前月
                        </a>
                        <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                            class="text-sm text-gray-500 hover:text-gray-700 hover:underline">
                            次月→
                        </a>
                    </div>

                    {{-- カレンダー --}}
                    @php
                        // ここで空マスの数を計算（$変数=計算式）
                        // 月初の曜日を取得
                        $blankCells = $startOfMonth->dayOfWeek;
                    @endphp

                    <table class="border-collapse w-full">
                        <thead>
                            <tr>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">日</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">月</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">火</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">水</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">木</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">金</th>
                                <th class="border border-gray-300 w-24 h-20 text-center align-top">土</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                {{-- 初期化、条件、更新の値 --}}
                                @for ($WeekDay = 0; $WeekDay < $blankCells; $WeekDay++)
                                    <td class="border border-gray-300 w-24 h-20 text-center align-top"></td>
                                @endfor

                                @for ($day = 1; $day <= $startOfMonth->daysInMonth; $day++)
                                    @php
                                        $carbon = $startOfMonth->copy()->day($day);
                                        $date = $carbon->format('Y-m-d');
                                    @endphp
                                    <td
                                        class="border border-gray-300 w-24 h-20 text-center align-top @if ($carbon->isToday()) bg-yellow-100 @endif">
                                        <span
                                            class="@if ($carbon->dayOfWeek == 0) text-red-500 @elseif($carbon->dayOfWeek == 6) text-blue-500 @endif">
                                            {{ $day }}
                                        </span>
                                        @if ($posts->has($date))
                                            @foreach ($posts[$date] as $post)
                                                <div>
                                                    <a href="{{ route('posts.show', $post) }}"
                                                        class="text-blue-600 hover:underline text-xs">
                                                        {{ $post->title }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                    @if (($blankCells + $day) % 7 == 0)
                            </tr>
                            <tr>
                                @endif
                                @endfor
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
