<?php

use App\Providers\AppServiceProvider;

// セキュリティ点検チェックリスト G1〜G3(本番設定)の設定漏れを防ぐテスト

function productionEnv(): array
{
    $values = [];

    foreach (file(base_path('.env.production.example'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $values[trim($key)] = trim($value);
    }

    return $values;
}

test('G1: the production env template disables debug mode', function () {
    $env = productionEnv();

    expect($env['APP_ENV'])->toBe('production');
    expect($env['APP_DEBUG'])->toBe('false');
    expect($env['APP_KEY'])->toBe('');
});

test('G2: the production env template encrypts session data', function () {
    expect(productionEnv()['SESSION_ENCRYPT'])->toBe('true');
});

test('G3: the production env template requires https and secure cookies', function () {
    $env = productionEnv();

    expect($env['APP_URL'])->toStartWith('https://');
    expect($env['SESSION_SECURE_COOKIE'])->toBe('true');
});

test('G3: URLs are forced to https in production', function () {
    $this->app['env'] = 'production';
    $this->app->getProvider(AppServiceProvider::class)->boot();

    expect(url('/posts'))->toStartWith('https://');
});

test('G3: URLs are not forced to https outside production', function () {
    expect(url('/posts'))->toStartWith('http://');
});
