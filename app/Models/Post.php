<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //入力を許可するカラムを指定（Mass Assignment（一括代入）対策）
    protected $fillable = [
        // この4つだけ外から値を入れられる
        'user_id',
        'title',
        'body',
        'is_public',
    ];

    // postsテーブルはusersテーブルに属している（多対一）
    public function user()
    {
        // Post（投稿）はUser（ユーザー）に属している
        return $this->belongsTo(User::class);
    }
}
