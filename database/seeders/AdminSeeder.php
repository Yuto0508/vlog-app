<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * 環境変数（ADMIN_EMAIL / ADMIN_PASSWORD）が設定されていれば、管理者を作成または更新する。
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = config('admin.name');
        $user->password = $password;
        $user->email_verified_at ??= now();
        // is_admin は一括代入の対象外なので、直接代入する
        $user->is_admin = true;
        $user->save();
    }
}
