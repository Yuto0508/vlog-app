<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 初期タグデータを登録する
        $tags = [
            'アニメ',
            '特撮',
            'ポケモン',
            'ゲーム',
            '日常',
            'ライフスタイル',
            '技術',
            '勉強',
        ];

        foreach ($tags as $tag) {
            Tag::create(['name' => $tag]);
        }
        // 配列の中身を1つずつ取り出してループする
        // $tags配列の各タグ名でTag::create()を実行する
    }
}
