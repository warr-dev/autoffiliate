<?php

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard with AI analytics props', function () {
    $user = User::factory()->create();

    AiUsageLog::logUsage(
        null,
        'openai',
        'gpt-4o-mini',
        'viral',
        120,
        80,
        200
    );

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('posts')
            ->has('totalCount')
            ->has('draftsCount')
            ->has('publishedCount')
            ->has('aiAnalytics.summary')
            ->has('aiAnalytics.by_provider')
            ->has('aiAnalytics.by_style')
            ->has('aiAnalytics.recent_activity')
            ->where('aiAnalytics.summary.total_generations', 1)
            ->where('aiAnalytics.summary.total_tokens_used', 200)
        );
});

test('api analytics endpoint returns summary, breakdown by provider and style, and recent activity', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Sample Wireless Mouse',
        'affiliate_url' => 'https://shopee.ph/item-123',
        'status' => 'draft',
    ]);

    AiUsageLog::logUsage(
        $post->id,
        'deepseek',
        'deepseek-chat',
        'specs',
        150,
        100,
        250
    );

    $response = $this->actingAs($user)->getJson('/api/analytics/ai');

    $response->assertOk()
        ->assertJsonStructure([
            'summary' => [
                'total_generations',
                'prompt_tokens_total',
                'completion_tokens_total',
                'total_tokens_used',
                'estimated_cost_usd',
                'active_provider',
                'active_model',
            ],
            'by_provider',
            'by_style',
            'recent_activity',
        ])
        ->assertJsonPath('summary.total_generations', 1)
        ->assertJsonPath('summary.total_tokens_used', 250);
});
