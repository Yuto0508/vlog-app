<?php

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('dashboard redirects to the posts index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('posts.index'));
});

test('posts index only lists public posts', function () {
    $publicPost = Post::factory()->create(['is_public' => true, 'title' => '公開されている投稿']);
    $privatePost = Post::factory()->create(['is_public' => false, 'title' => '非公開の投稿']);

    $response = $this->get(route('posts.index'));

    $response->assertOk();
    $response->assertSee($publicPost->title);
    $response->assertDontSee($privatePost->title);
});

test('guests can view the posts index but do not see the new post button', function () {
    $response = $this->get(route('posts.index'));

    $response->assertOk();
    $response->assertDontSee('新規投稿');
});

test('admin users see the new post button on the posts index', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('posts.index'));

    $response->assertOk();
    $response->assertSee('新規投稿');
    $response->assertSee(route('posts.create'));
});

test('non-admin users do not see the new post button on the posts index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('posts.index'));

    $response->assertOk();
    $response->assertDontSee('新規投稿');
});

test('guests cannot access the post creation form', function () {
    $response = $this->get(route('posts.create'));

    $response->assertRedirect(route('login'));
});

test('non-admin users cannot access the post creation form', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('posts.create'));

    $response->assertForbidden();
});

test('admin users can view the post creation form with seeded tags', function () {
    $this->seed(\Database\Seeders\TagSeeder::class);
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('posts.create'));

    $response->assertOk();
    foreach (Tag::pluck('name') as $tagName) {
        $response->assertSee($tagName);
    }
});

test('admin users can create a post with tags and an image', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $tags = Tag::factory()->count(2)->create();
    $image = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($admin)->post(route('posts.store'), [
        'title' => 'テスト投稿',
        'body' => '本文です',
        'is_public' => '1',
        'image' => $image,
        'tags' => $tags->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('posts.index'));

    $post = Post::where('title', 'テスト投稿')->firstOrFail();
    expect($post->user_id)->toBe($admin->id);
    expect($post->is_public)->toBeTruthy();
    expect($post->tags->pluck('id')->sort()->values()->all())
        ->toBe($tags->pluck('id')->sort()->values()->all());

    Storage::disk('public')->assertExists($post->image_path);
});

test('non-admin users cannot create a post', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('posts.store'), [
        'title' => 'テスト投稿',
        'body' => '本文です',
    ]);

    $response->assertForbidden();
    $this->assertDatabaseMissing('posts', ['title' => 'テスト投稿']);
});

test('non-admin users cannot edit or update posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $this->actingAs($user)->get(route('posts.edit', $post))->assertForbidden();

    $response = $this->actingAs($user)->put(route('posts.update', $post), [
        'title' => '書き換え後のタイトル',
        'body' => '書き換え後の本文',
    ]);
    $response->assertForbidden();

    expect($post->refresh()->title)->not->toBe('書き換え後のタイトル');
});

test('admin can update a post and its tags even if authored by another user', function () {
    $author = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);
    $oldTag = Tag::factory()->create();
    $post->tags()->attach($oldTag);
    $newTag = Tag::factory()->create();

    $response = $this->actingAs($admin)->put(route('posts.update', $post), [
        'title' => '更新後のタイトル',
        'body' => '更新後の本文',
        'tags' => [$newTag->id],
    ]);

    $response->assertRedirect(route('posts.show', $post));

    $post->refresh();
    expect($post->title)->toBe('更新後のタイトル');
    expect($post->tags->pluck('id')->all())->toBe([$newTag->id]);
});

test('replacing a post image deletes the old file', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();
    $oldPath = UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg')->store('images', 'public');
    $post = Post::factory()->create(['user_id' => $admin->id, 'image_path' => $oldPath]);

    $response = $this->actingAs($admin)->put(route('posts.update', $post), [
        'title' => $post->title,
        'body' => $post->body,
        'image' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertRedirect(route('posts.show', $post));

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($post->refresh()->image_path);
});

test('non-admin users cannot delete posts', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

    $response->assertForbidden();
    $this->assertModelExists($post);
});

test('admin can delete a post even if authored by another user', function () {
    $author = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    $response = $this->actingAs($admin)->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $this->assertModelMissing($post);
});
