<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});

test('deleting an account also deletes the image files of its posts', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $ownPaths = [
        UploadedFile::fake()->create('a.jpg', 100, 'image/jpeg')->store('images', 'public'),
        UploadedFile::fake()->create('b.jpg', 100, 'image/jpeg')->store('images', 'public'),
    ];
    foreach ($ownPaths as $path) {
        Post::factory()->create(['user_id' => $user->id, 'image_path' => $path]);
    }
    $otherPath = UploadedFile::fake()->create('c.jpg', 100, 'image/jpeg')->store('images', 'public');
    $otherPost = Post::factory()->create(['user_id' => $otherUser->id, 'image_path' => $otherPath]);

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertNull($user->fresh());
    $this->assertDatabaseMissing('posts', ['user_id' => $user->id]);
    foreach ($ownPaths as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    // 他のユーザーの投稿と画像は残る
    $this->assertModelExists($otherPost);
    Storage::disk('public')->assertExists($otherPath);
});

test('deleting an account without posts still works', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])
        ->assertRedirect('/');

    $this->assertNull($user->fresh());
});
