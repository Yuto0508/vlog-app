<?php

use App\Models\Tag;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Hash;

test('AdminSeeder creates an admin from the configured email and password', function () {
    config(['admin.name' => '管理者', 'admin.email' => 'admin@example.com', 'admin.password' => 'secret-pass-123']);

    $this->seed(AdminSeeder::class);

    $admin = User::where('email', 'admin@example.com')->firstOrFail();
    expect($admin->is_admin)->toBeTruthy();
    expect($admin->name)->toBe('管理者');
    expect(Hash::check('secret-pass-123', $admin->password))->toBeTrue();
    expect($admin->email_verified_at)->not->toBeNull();
});

test('AdminSeeder does nothing when the email or password is not configured', function () {
    config(['admin.email' => null, 'admin.password' => null]);
    $this->seed(AdminSeeder::class);

    config(['admin.email' => 'admin@example.com', 'admin.password' => null]);
    $this->seed(AdminSeeder::class);

    expect(User::count())->toBe(0);
});

test('AdminSeeder promotes an existing user and does not create a duplicate', function () {
    $existing = User::factory()->create(['email' => 'admin@example.com']);
    config(['admin.email' => 'admin@example.com', 'admin.password' => 'secret-pass-123']);

    $this->seed(AdminSeeder::class);
    $this->seed(AdminSeeder::class);

    expect(User::where('email', 'admin@example.com')->count())->toBe(1);
    expect($existing->refresh()->is_admin)->toBeTruthy();
});

test('AdminSeeder does not overwrite the name or password of an existing admin', function () {
    $existing = User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'name' => 'アプリ内で変えた名前',
        'password' => 'changed-in-the-app-1',
    ]);
    config(['admin.name' => '環境変数の名前', 'admin.email' => 'admin@example.com', 'admin.password' => 'from-env-password']);

    $this->seed(AdminSeeder::class);

    $existing->refresh();
    expect($existing->name)->toBe('アプリ内で変えた名前');
    expect(Hash::check('changed-in-the-app-1', $existing->password))->toBeTrue();
    expect(Hash::check('from-env-password', $existing->password))->toBeFalse();
    expect($existing->is_admin)->toBeTruthy();
});

test('TagSeeder can run repeatedly without creating duplicates', function () {
    $this->seed(TagSeeder::class);
    $count = Tag::count();

    $this->seed(TagSeeder::class);

    expect($count)->toBeGreaterThan(0);
    expect(Tag::count())->toBe($count);
});

test('DatabaseSeeder seeds the tags and the admin', function () {
    config(['admin.email' => 'admin@example.com', 'admin.password' => 'secret-pass-123']);

    $this->seed(DatabaseSeeder::class);

    expect(Tag::count())->toBeGreaterThan(0);
    expect(User::where('email', 'admin@example.com')->value('is_admin'))->toBeTruthy();
});
