<?php

// 起動時に管理者を自動作成するための設定（AdminSeeder が参照する）。
// 無料の Render のようにデータが消える環境で、再起動のたびに管理者を作り直すために使う。
// 値が空の場合は何も作らない。パスワードは環境変数で渡し、リポジトリには置かないこと。
return [
    'name' => env('ADMIN_NAME', 'Admin'),
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),
];
