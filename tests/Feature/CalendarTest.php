<?php

use App\Models\Post;

test('calendar shows public posts on the day they were created', function () {
    $today = now();
    $publicPost = Post::factory()->create([
        'is_public' => true,
        'title' => '公開カレンダー投稿',
        'created_at' => $today,
    ]);
    $privatePost = Post::factory()->create([
        'is_public' => false,
        'title' => '非公開カレンダー投稿',
        'created_at' => $today,
    ]);

    $response = $this->get(route('calendar.index', ['year' => $today->year, 'month' => $today->month]));

    $response->assertOk();
    $response->assertSee($publicPost->title);
    $response->assertDontSee($privatePost->title);
});

test('calendar navigation links point to the previous and next month', function () {
    $response = $this->get(route('calendar.index', ['year' => 2026, 'month' => 7]));

    $response->assertOk();
    $response->assertSee(route('calendar.index', ['year' => 2026, 'month' => 6]));
    $response->assertSee(route('calendar.index', ['year' => 2026, 'month' => 8]));
});
