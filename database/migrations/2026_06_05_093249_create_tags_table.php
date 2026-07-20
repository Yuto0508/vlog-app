<?php

use Illuminate\Database\Migrations\Migration;
// Migrationクラスを読み込む（マイグレーションの基底クラス）
use Illuminate\Database\Schema\Blueprint;
// Blueprintクラスを読み込む（テーブルのカラム定義に使う）
use Illuminate\Support\Facades\Schema;

// Schemaクラスを読み込む（テーブルの作成・削除に使う）

return new class extends Migration
    // 無名クラスでMigrationを継承して返す
{
    /**
     * Run the migrations.
     */
    public function up(): void
    // マイグレーション実行時（php artisan migrate）に呼ばれるメソッド
    {
        Schema::create('tags', function (Blueprint $table) {
            // tagsテーブルを作成する
            $table->id();
            // idカラムを作る（主キー・自動採番）

            $table->string('name')->unique();
            // nameカラムを作る（文字列型・重複不可）
            // unique()をつけることで同じタグ名を2つ作れないようにする
            $table->timestamps();
            // created_at・updated_atカラムを自動生成
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    // マイグレーション取り消し時（php artisan migrate:rollback）に呼ばれるメソッド
    {
        Schema::dropIfExists('tags');
        // tagsテーブルが存在する場合は削除する
    }
};
