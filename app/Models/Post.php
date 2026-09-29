<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    // 入力を許可するカラムを指定（Mass Assignment（一括代入）対策）
    protected $fillable = [
        // この4つだけ外から値を入れられる
        'user_id',
        'title',
        'body',
        // image_pathカラムを新規追加
        'image_path',
        'is_public',
    ];

    protected static function booted(): void
    {
        // 投稿を削除するときは、紐づく画像ファイルも削除する（Web・APIどちらの削除でも共通）
        static::deleting(function (Post $post) {
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
        });
    }

    // postsテーブルはusersテーブルに属している（多対一）
    public function user()
    {
        // Post（投稿）はUser（ユーザー）に属している
        return $this->belongsTo(User::class);
    }

    //　投稿は複数のタグを持っている（多対多）
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
