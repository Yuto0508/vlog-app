<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        // 表示する年月を取得・検証する（未指定または不正値の場合は今月）
        $request->validate([
            'year' => 'nullable|integer|min:1900|max:2200',
            'month' => 'nullable|integer|min:1|max:12',
        ]);
        $year = $request->integer('year', Carbon::now()->year);
        $month = $request->integer('month', Carbon::now()->month);

        // 月の最初と最後の日を取得
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        //その月の公開投稿を取得
        $posts = Post::where('is_public', true)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            // created_atが$startOfMonthから$endOfMonthの間のデータを取得
            ->get()
            ->groupBy(function ($post) {
                return $post->created_at->format('Y-m-d');
            });
        // 日付ごとにグループ分けする
        // 例：['2026-06-01' => [投稿A], '2026-06-05' => [投稿B, 投稿C]]

        // 前月・次月の年月を計算
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        return view('calendar.index', compact(
            'year',
            'month',
            'posts',
            'startOfMonth',
            'prevMonth',
            'nextMonth'
        ));
    }
}
