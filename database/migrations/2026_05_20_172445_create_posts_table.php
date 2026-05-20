<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            // user_id＝投稿したユーザーのID
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // title＝投稿タイトル
            $table->string('title');
            // body＝投稿本文
            $table->text('body');
            // is_public＝公開・非公開の切り替え
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
