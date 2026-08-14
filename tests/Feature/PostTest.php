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

test('authenticated users see the new post button on the posts index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('posts.index'));

    $response->assertOk();
    $response->assertSee('新規投稿');
    $response->assertSee(route('posts.create'));
});

test('guests cannot access the post creation form', function () {
    $response = $this->get(route('posts.create'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can view the post creation form with seeded tags', function () {
    $this->seed(\Database\Seeders\TagSeeder::class);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('posts.create'));

    $response->assertOk();
    foreach (Tag::pluck('name') as $tagName) {
        $response->assertSee($tagName);
    }
});

test('authenticated users can create a post with tags and an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $tags = Tag::factory()->count(2)->create();
    $image = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

    $response = $this->actingAs($user)->post(route('posts.store'), [
        'title' => 'テスト投稿',
        'body' => '本文です',
        'is_public' => '1',
        'image' => $image,
        'tags' => $tags->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('posts.index'));

    $post = Post::where('title', 'テスト投稿')->firstOrFail();
    expect($post->user_id)->toBe($user->id);
    expect($post->is_public)->toBeTruthy();
    expect($post->tags->pluck('id')->sort()->values()->all())
        ->toBe($tags->pluck('id')->sort()->values()->all());

    Storage::disk('public')->assertExists($post->image_path);
});

test('users cannot edit posts they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->get(route('posts.edit', $post));

    $response->assertRedirect(route('posts.index'));
});

test('owners can update their post and its tags', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);
    $oldTag = Tag::factory()->create();
    $post->tags()->attach($oldTag);
    $newTag = Tag::factory()->create();

    $response = $this->actingAs($user)->put(route('posts.update', $post), [
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
    $user = User::factory()->create();
    $oldPath = UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg')->store('images', 'public');
    $post = Post::factory()->create(['user_id' => $user->id, 'image_path' => $oldPath]);

    $response = $this->actingAs($user)->put(route('posts.update', $post), [
        'title' => $post->title,
        'body' => $post->body,
        'image' => UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertRedirect(route('posts.show', $post));

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($post->refresh()->image_path);
});

test('users cannot delete posts they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($otherUser)->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $this->assertModelExists($post);
});

test('owners can delete their post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

    $response->assertRedirect(route('posts.index'));
    $this->assertModelMissing($post);
});
