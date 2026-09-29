<?php

use App\Models\Post;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

// --- web: 非公開投稿の閲覧 ---

test('guests cannot view a private post', function () {
    $post = Post::factory()->create(['is_public' => false]);

    $this->get(route('posts.show', $post))->assertNotFound();
});

test('other users cannot view a private post', function () {
    $post = Post::factory()->create(['is_public' => false]);

    $this->actingAs(User::factory()->create())
        ->get(route('posts.show', $post))
        ->assertNotFound();
});

test('the author can view their own private post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id, 'is_public' => false]);

    $this->actingAs($author)->get(route('posts.show', $post))->assertOk();
});

test('anyone can view a public post', function () {
    $post = Post::factory()->create(['is_public' => true]);

    $this->get(route('posts.show', $post))->assertOk();
});

test('a nonexistent post returns 404', function () {
    $this->get(route('posts.show', 999999))->assertNotFound();
});

// --- web: ゲストの書き込み系 ---

test('guests are redirected to login on web write endpoints', function () {
    $post = Post::factory()->create();

    $this->post(route('posts.store'), ['title' => 'a', 'body' => 'b'])->assertRedirect(route('login'));
    $this->get(route('posts.edit', $post))->assertRedirect(route('login'));
    $this->put(route('posts.update', $post), ['title' => 'x', 'body' => 'y'])->assertRedirect(route('login'));
    $this->delete(route('posts.destroy', $post))->assertRedirect(route('login'));

    $this->assertModelExists($post);
});

// --- API: 閲覧 ---

test('api: guests cannot view a private post', function () {
    $post = Post::factory()->create(['is_public' => false]);

    $this->getJson("/api/posts/{$post->id}")->assertNotFound();
});

test('api: other users cannot view a private post', function () {
    $post = Post::factory()->create(['is_public' => false]);
    Sanctum::actingAs(User::factory()->create());

    $this->getJson("/api/posts/{$post->id}")->assertNotFound();
});

test('api: the author can view their own private post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id, 'is_public' => false]);
    Sanctum::actingAs($author);

    $this->getJson("/api/posts/{$post->id}")->assertOk();
});

test('api: index does not list private posts', function () {
    $private = Post::factory()->create(['is_public' => false]);
    Post::factory()->create(['is_public' => true]);

    $this->getJson('/api/posts')
        ->assertOk()
        ->assertJsonMissing(['id' => $private->id]);
});

// --- API: 書き込み ---

test('api: guests get 401 on write endpoints', function () {
    $post = Post::factory()->create();

    $this->postJson('/api/posts', ['title' => 'a', 'body' => 'b'])->assertUnauthorized();
    $this->putJson("/api/posts/{$post->id}", ['title' => 'x'])->assertUnauthorized();
    $this->deleteJson("/api/posts/{$post->id}")->assertUnauthorized();

    $this->assertModelExists($post);
});

test('api: non-admin users cannot create, update or delete posts', function () {
    $post = Post::factory()->create();
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/posts', ['title' => '不正作成', 'body' => 'b'])->assertForbidden();
    $this->putJson("/api/posts/{$post->id}", ['title' => '改ざん'])->assertForbidden();
    $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

    $this->assertDatabaseMissing('posts', ['title' => '不正作成']);
    expect($post->refresh()->title)->not->toBe('改ざん');
    $this->assertModelExists($post);
});

test('api: admin can update and delete a post authored by another user', function () {
    $post = Post::factory()->create();
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->putJson("/api/posts/{$post->id}", ['title' => '管理者が更新'])->assertOk();
    expect($post->refresh()->title)->toBe('管理者が更新');

    $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();
    $this->assertModelMissing($post);
});
