<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'apitest@autoffiliate.ph',
        'password' => Hash::make('password123'),
    ]);
});

test('public health endpoint is accessible without token', function () {
    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJson(['status' => 'ok']);
});

test('protected API endpoint is rejected with 401 when no token is provided', function () {
    $response = $this->getJson('/api/posts');

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'status' => 401,
        ]);
});

test('user can login and receive personal access token', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'apitest@autoffiliate.ph',
        'password' => 'password123',
        'device_name' => 'pest_test_suite',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ])
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);

    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

test('user can access protected API endpoint with valid Bearer token', function () {
    $tokenResult = $this->user->createToken('test_bearer');
    $token = $tokenResult->plainTextToken;

    $post = Post::create([
        'id' => 'test-post-auth-123',
        'product_title' => 'Protected Test Product',
        'affiliate_url' => 'https://shopee.ph/product/123/456',
        'status' => 'draft',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/posts');

    $response->assertStatus(200);
    $data = $response->json();
    expect(count($data))->toBeGreaterThan(0);
});

test('user can access protected API endpoint with X-API-Key header', function () {
    $tokenResult = $this->user->createToken('n8n_bot_key');
    $token = $tokenResult->plainTextToken;

    $response = $this->withHeader('X-API-Key', $token)
        ->getJson('/api/settings');

    $response->assertStatus(200);
});

test('user can generate named API keys and revoke them', function () {
    $tokenResult = $this->user->createToken('auth_client');
    $authToken = $tokenResult->plainTextToken;

    // 1. Create a new permanent API key
    $createResp = $this->withHeader('Authorization', "Bearer {$authToken}")
        ->postJson('/api/auth/tokens', [
            'name' => 'n8n Production Webhook Key',
        ]);

    $createResp->assertStatus(201)
        ->assertJson([
            'success' => true,
            'name' => 'n8n Production Webhook Key',
        ]);

    $newApiKey = $createResp->json('token');
    $tokenId = $createResp->json('id');

    // 2. Access API with the new key
    $testResp = $this->withHeader('Authorization', "Bearer {$newApiKey}")
        ->getJson('/api/posts');
    $testResp->assertStatus(200);

    // 3. Revoke the key
    $revokeResp = $this->withHeader('Authorization', "Bearer {$authToken}")
        ->deleteJson("/api/auth/tokens/{$tokenId}");
    $revokeResp->assertStatus(200)
        ->assertJson(['success' => true]);

    // 4. Verify the revoked key is now rejected
    $revokedAttempt = $this->withHeader('Authorization', "Bearer {$newApiKey}")
        ->getJson('/api/posts');
    $revokedAttempt->assertStatus(401);
});
