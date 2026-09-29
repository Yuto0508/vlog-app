<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// セキュリティ点検チェックリスト(A〜H)のうち、自動で確認できる項目のテスト

function securityXss(): string
{
    return '<script>alert(1)</script>';
}

function securityEscapedXss(): string
{
    return '&lt;script&gt;alert(1)&lt;/script&gt;';
}

// --- A: 認証・セッション ---

test('A2: login is locked out after 5 failed attempts', function () {
    $user = User::factory()->create();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('A3: session id is regenerated on login', function () {
    $user = User::factory()->create();

    $this->get('/login');
    $before = session()->getId();

    $this->post('/login', ['email' => $user->email, 'password' => 'password']);

    expect(session()->getId())->not->toBe($before);
});

test('A4/A5: after logout, protected pages and the API reject the user', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $this->get(route('posts.create'))->assertRedirect(route('login'));
    $this->getJson('/api/user')->assertUnauthorized();
});

test('A6: a password reset token cannot be reused', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasNoErrors();

        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'another-password-456',
            'password_confirmation' => 'another-password-456',
        ])->assertSessionHasErrors('email');

        return true;
    });
});

test('A7: logging in with "remember me" sets a remember cookie', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'remember' => 'on',
    ])->assertCookie(Auth::guard('web')->getRecallerName());

    expect($user->refresh()->remember_token)->not->toBeNull();
});

test('A8: registration is rate limited', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/register', ['name' => '', 'email' => '', 'password' => '']);
    }

    $this->post('/register', ['name' => '', 'email' => '', 'password' => ''])
        ->assertStatus(429);
});

// --- B: XSS ---

test('B1: script tags in title and body are escaped on index, show and calendar', function () {
    $post = Post::factory()->create(['title' => securityXss(), 'body' => securityXss()]);

    foreach ([route('posts.index'), route('posts.show', $post), route('calendar.index')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee(securityXss(), false)
            ->assertSee(securityEscapedXss(), false);
    }
});

test('B2: a tag name with an HTML payload is escaped', function () {
    $post = Post::factory()->create();
    $tag = Tag::factory()->create(['name' => '"><img src=x onerror=alert(1)>']);
    $post->tags()->attach($tag);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});

test('B3: an HTML payload in the user name is escaped in the navigation', function () {
    $user = User::factory()->create(['name' => securityXss()]);

    $this->actingAs($user)->get(route('posts.index'))
        ->assertOk()
        ->assertDontSee(securityXss(), false)
        ->assertSee(securityEscapedXss(), false);
});

test('B4: the title cannot break out of the image alt attribute', function () {
    $post = Post::factory()->create([
        'title' => '" onerror="alert(1)',
        'image_path' => 'images/example.jpg',
    ]);

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSee('" onerror="alert(1)', false);
});

// --- C: CSRF ---

test('C2: the API group does not start a session, so cookies cannot authenticate it', function () {
    $api = app('router')->getMiddlewareGroups()['api'] ?? [];

    expect($api)->not->toContain(StartSession::class);
    expect($api)->not->toContain(EnsureFrontendRequestsAreStateful::class);
});

// --- E: ファイルアップロード ---

test('E1: a PHP or SVG file disguised as an image is rejected', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    // UploadedFile::fake() は中身ではなくファイル名から MIME を決めるため、
    // 実ファイルを使って中身の判定(MIME スニッフィング)を確認する
    $contents = [
        'shell.php.jpg' => '<?php system($_GET["c"]);',
        'evil.svg' => '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
    ];

    foreach ($contents as $name => $body) {
        $path = tempnam(sys_get_temp_dir(), 'upload');
        file_put_contents($path, $body);
        $file = new UploadedFile($path, $name, null, null, true);

        $this->actingAs($admin)->post(route('posts.store'), [
            'title' => '偽装ファイル',
            'body' => '本文',
            'image' => $file,
        ])->assertSessionHasErrors('image');

        @unlink($path);
    }

    $this->assertDatabaseMissing('posts', ['title' => '偽装ファイル']);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('E2: an uploaded image is stored under images/ with a generated name', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('posts.store'), [
        'title' => '画像テスト',
        'body' => '本文',
        'image' => UploadedFile::fake()->create('avatar.php.jpg', 100, 'image/jpeg'),
    ]);

    $path = Post::where('title', '画像テスト')->firstOrFail()->image_path;

    expect($path)->toStartWith('images/');
    expect($path)->not->toContain('..');
    expect($path)->not->toContain('php');
    Storage::disk('public')->assertExists($path);
});

test('E3: a file over 2MB is rejected', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('posts.store'), [
        'title' => '大きい画像',
        'body' => '本文',
        'image' => UploadedFile::fake()->create('big.jpg', 3000, 'image/jpeg'),
    ])->assertSessionHasErrors('image');

    $this->assertDatabaseMissing('posts', ['title' => '大きい画像']);
});

// --- F: 入力値 ---

test('F2: invalid calendar parameters return a validation error, not a server error', function () {
    $this->get('/calendar?year=abc')->assertSessionHasErrors('year');
    $this->get('/calendar?month=13')->assertSessionHasErrors('month');
    $this->get('/calendar?year=99999')->assertSessionHasErrors('year');
});

// --- H: 管理者限定投稿 ---

test('H1/H2: a new user can register and then view posts', function () {
    $post = Post::factory()->create();

    $this->post('/register', [
        'name' => '一般ユーザー',
        'email' => 'general@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $this->get(route('posts.index'))->assertOk();
    $this->get(route('posts.show', $post))->assertOk();
});

test('H9: is_admin cannot be set through registration or profile update', function () {
    $this->post('/register', [
        'name' => '攻撃者',
        'email' => 'attacker@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'is_admin' => '1',
    ]);

    $attacker = User::where('email', 'attacker@example.com')->firstOrFail();
    expect($attacker->is_admin)->toBeFalsy();

    $this->actingAs($attacker)->patch('/profile', [
        'name' => '攻撃者',
        'email' => 'attacker@example.com',
        'is_admin' => '1',
    ]);

    expect($attacker->refresh()->is_admin)->toBeFalsy();
});

test('H10: user:make-admin promotes an existing user and fails for an unknown one', function () {
    $user = User::factory()->create();

    $this->artisan('user:make-admin', ['email' => $user->email])->assertSuccessful();
    expect($user->refresh()->is_admin)->toBeTruthy();

    $this->artisan('user:make-admin', ['email' => 'nobody@example.com'])->assertFailed();
});
