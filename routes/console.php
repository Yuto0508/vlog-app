<?php

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("ユーザーが見つかりません: {$email}");

        return Command::FAILURE;
    }

    $user->is_admin = true;
    $user->save();

    $this->info("{$user->name} ({$user->email}) を管理者にしました。");
})->purpose('指定したメールアドレスのユーザーを管理者にする');
