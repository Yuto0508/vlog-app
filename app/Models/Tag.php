<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    // 入力を許可するカラムを指定
    protected $fillable = ['name'];

    // タグは複数の投稿に属している(多対多の関係)
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
