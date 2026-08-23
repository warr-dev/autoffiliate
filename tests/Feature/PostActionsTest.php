<?php

use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

test('drafts page can be rendered for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/drafts');

    $response->assertStatus(200);
});

test('user can create a new post draft', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/drafts', [
        'product_title' => 'Wireless Gaming Mouse RGB',
        'product_description' => 'Ultra-lightweight 2.4GHz wireless gaming mouse with RGB backlight.',
        'product_price' => '₱899',
        'shop_name' => 'Tech Deals Official Store',
        'affiliate_url' => 'https://shopee.ph/product-deal-12345',
        'caption' => 'Check out this awesome gaming mouse!',
        'tags' => '#TechSulitDeals #ShopeePH #GamingMouse',
    ]);

    $response->assertRedirect('/drafts');

    $this->assertDatabaseHas('posts', [
        'product_title' => 'Wireless Gaming Mouse RGB',
        'affiliate_url' => 'https://shopee.ph/product-deal-12345',
        'status' => 'draft',
    ]);
});

test('user can create a custom post draft', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/posts/custom', [
        'title' => 'Weekly Community Q&A',
        'caption' => 'Drop your questions below about our latest tech findings!',
        'tags' => '#TechSulitDeals #Community',
    ]);

    $response->assertSessionHas('success');

    $this->assertDatabaseHas('posts', [
        'product_title' => 'Weekly Community Q&A',
        'status' => 'draft',
    ]);
});

test('user can update post caption and tags', function () {
    $user = User::factory()->create();
    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Mechanical Keyboard',
        'affiliate_url' => 'https://shopee.ph/keyboard-123',
        'caption' => 'Initial caption',
        'tags' => '#Keyboard',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($user)->patch("/drafts/{$post->id}", [
        'caption' => 'Updated high-converting caption with emojis 🔥',
        'tags' => '#Keyboard #ShopeeDeals',
        'status' => 'draft',
    ]);

    $response->assertSessionHas('success');

    $post->refresh();
    expect($post->caption)->toBe('Updated high-converting caption with emojis 🔥');
    expect($post->tags)->toBe('#Keyboard #ShopeeDeals');
});

test('user can approve a post draft', function () {
    $user = User::factory()->create();
    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Fast Charging Power Bank',
        'affiliate_url' => 'https://shopee.ph/powerbank-456',
        'caption' => 'Ready to publish',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($user)->post("/drafts/{$post->id}/approve");

    $response->assertSessionHas('success');

    $post->refresh();
    expect($post->status)->toBe('approved');
});

test('user can generate caption with different styles', function () {
    $user = User::factory()->create();
    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Smart Watch Fitness Tracker',
        'product_price' => '₱1,299',
        'affiliate_url' => 'https://shopee.ph/smartwatch-789',
        'caption' => '',
        'tags' => '#SmartWatch',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($user)->post("/drafts/{$post->id}/generate-caption", [
        'caption_style' => 'viral',
    ]);

    $response->assertSessionHas('success');

    $post->refresh();
    expect($post->caption)->toContain('SUPER SALE ALERT');
    expect($post->caption)->toContain('Smart Watch Fitness Tracker');
    expect($post->caption)->toContain('₱1,299');
});

test('user can publish a post to Facebook and dispatch outbound webhook', function () {
    Http::fake([
        'https://graph.facebook.com/*' => Http::response([
            'id' => '1184127881441932_999888777',
        ], 200),
        'http://example.com/n8n-webhook' => Http::response([
            'status' => 'received',
        ], 200),
    ]);

    Setting::set('n8n_outbound_webhook', 'http://example.com/n8n-webhook');

    SocialAccount::create([
        'id' => (string) Str::uuid(),
        'platform' => 'facebook',
        'name' => 'Tech Sulit Deals',
        'account_id' => '1184127881441932',
        'access_token' => 'EAATestToken12345',
        'extra_config' => [
            'is_affiliate' => true,
            'disclosure' => 'Affiliate link. Price and availability may change anytime.',
        ],
        'is_enabled' => true,
        'status' => 'active',
    ]);

    $user = User::factory()->create();
    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'ANC Wireless Earbuds',
        'product_price' => '₱549',
        'affiliate_url' => 'https://shopee.ph/earbuds-999',
        'caption' => 'Clear sound and active noise cancellation!',
        'tags' => '#TechSulitDeals #Earbuds',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($user)->post("/drafts/{$post->id}/publish");

    $response->assertSessionHas('success');

    $post->refresh();
    expect($post->status)->toBe('published');
    expect($post->facebook_post_id)->toBe('1184127881441932_999888777');
    expect($post->facebook_post_url)->toBe('https://facebook.com/1184127881441932_999888777');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'graph.facebook.com');
    });

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'example.com/n8n-webhook');
    });
});

test('user can delete a post draft', function () {
    $user = User::factory()->create();
    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Item to Delete',
        'affiliate_url' => 'https://shopee.ph/item-to-delete',
        'status' => 'draft',
    ]);

    $response = $this->actingAs($user)->delete("/drafts/{$post->id}");

    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

test('history page renders published and approved posts', function () {
    $user = User::factory()->create();
    Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Published Item',
        'affiliate_url' => 'https://shopee.ph/published',
        'status' => 'published',
    ]);

    $response = $this->actingAs($user)->get('/history');

    $response->assertStatus(200);
});

test('user can send test post to a connected social account', function () {
    Http::fake([
        'https://graph.facebook.com/*' => Http::response([
            'id' => '1184127881441932_555444333',
        ], 200),
    ]);

    $account = SocialAccount::create([
        'id' => (string) Str::uuid(),
        'platform' => 'facebook',
        'name' => 'Tech Sulit Deals',
        'account_id' => '1184127881441932',
        'access_token' => 'EAATestTokenValid123',
        'is_enabled' => true,
        'status' => 'active',
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson("/settings/social-accounts/{$account->id}/test-post");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'facebook_post_id' => '1184127881441932_555444333',
            'facebook_post_url' => 'https://facebook.com/1184127881441932_555444333',
        ]);

    $this->assertDatabaseHas('posts', [
        'facebook_post_id' => '1184127881441932_555444333',
        'status' => 'published',
    ]);
});
