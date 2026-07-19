@extends('layouts.app')

@section('title',$year.'年'.$month.'月のカレンダー')

@section('content')
<h2>{{ $year }}年{{ $month}}月</h2>

{{-- 前月・次月のナビゲーション --}}
<div>
	<a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month'=>$prevMonth->month]) }}">
		←前月
	</a>
	<a href="{{ route('calendar.index',['year'=>$nextMonth->year,'month'=>$nextMonth->month])}}">
		次月→
	</a>
</div>

{{-- カレンダー --}}
@php
 // ここで空マスの数を計算（$変数=計算式）
// 月初の曜日を取得
$blankCells = $startOfMonth->dayOfWeek;
@endphp

<table>
<thead>
	<tr>
		<th>日</th>
		<th>月</th>
		<th>火</th>
		<th>水</th>
		<th>木</th>
		<th>金</th>
		<th>土</th>
	</tr>
</thead>
<tbody>
<tr>
{{-- 初期化、条件、更新の値 --}}
@for($WeekDay = 0; $WeekDay < $blankCells; $WeekDay++)
<td></td>
@endfor

@for($day = 1; $day <= $startOfMonth->daysInMonth; $day++)
 @php
  $date = $startOfMonth->copy()->day($day)->format('Y-m-d');
 @endphp
<td>{{ $day }}
@if($posts->has($date))
<div>
⚫︎
</div>
@endif
</td>
@if(($blankCells + $day) % 7 == 0)
</tr><tr>
@endif
@endfor
</tr>
</tbody>
</table>

<style>
	table{
		border-collapse;
	}
	th,td{
		border:1px solid #ccc;
		width: 100px;
		height: 80px;
		text-align: center;
		vertical-align: top;
	}
</style>