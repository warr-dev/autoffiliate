<?php

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected when visiting analytics page', function () {
    $response = $this->get(route('analytics.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the analytics page with rich metrics and timeline', function () {
    $user = User::factory()->create();

    AiUsageLog::logUsage(
        null,
        'openai',
        'gpt-4o-mini',
        'viral_ai',
        100,
        150,
        250,
        450,
        'manual_draft',
        'success',
        true
    );

    $response = $this->actingAs($user)->get('/analytics');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Analytics/Index')
            ->has('analytics.summary')
            ->has('analytics.timeline')
            ->has('analytics.by_provider')
            ->has('analytics.by_model')
            ->has('analytics.by_style')
            ->has('analytics.by_source')
            ->has('analytics.recent_activity')
            ->where('analytics.summary.total_generations', 1)
            ->where('analytics.summary.total_tokens_used', 250)
            ->where('analytics.summary.avg_execution_time_ms', 450)
        );
});

test('api analytics endpoint returns filtered data by period, provider, and style', function () {
    $user = User::factory()->create();

    // Log OpenAI record
    AiUsageLog::logUsage(
        null,
        'openai',
        'gpt-4o-mini',
        'viral_ai',
        100,
        200,
        300,
        320,
        'manual_draft'
    );

    // Log DeepSeek record
    AiUsageLog::logUsage(
        null,
        'deepseek',
        'deepseek-chat',
        'specs_tech',
        120,
        180,
        300,
        510,
        'automated_workflow'
    );

    // Filter by provider=deepseek
    $response = $this->actingAs($user)->getJson('/api/analytics/ai?provider=deepseek');
    $response->assertOk()
        ->assertJsonPath('summary.total_generations', 1)
        ->assertJsonPath('by_provider.0.provider', 'deepseek');

    // Filter by style=viral_ai
    $styleResp = $this->actingAs($user)->getJson('/api/analytics/ai?style=viral_ai');
    $styleResp->assertOk()
        ->assertJsonPath('summary.total_generations', 1)
        ->assertJsonPath('by_style.0.style', 'viral_ai');
});

test('export endpoint generates downloadable CSV file with header and rows', function () {
    $user = User::factory()->create();

    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'Ergonomic Desk Chair',
        'affiliate_url' => 'https://shopee.ph/chair-item',
        'status' => 'draft',
    ]);

    AiUsageLog::logUsage(
        $post->id,
        'gemini',
        'gemini-1.5-flash',
        'aesthetic',
        90,
        160,
        250,
        280,
        'manual_draft'
    );

    $response = $this->actingAs($user)->get('/analytics/export?format=csv');

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    
    $content = $response->streamedContent();
    expect($content)->toContain('Log ID')
        ->toContain('Ergonomic Desk Chair')
        ->toContain('gemini')
        ->toContain('gemini-1.5-flash')
        ->toContain('aesthetic');
});

test('export endpoint generates downloadable JSON file', function () {
    $user = User::factory()->create();

    AiUsageLog::logUsage(
        null,
        'openai',
        'gpt-4o',
        'urgency_flash',
        80,
        120,
        200
    );

    $response = $this->actingAs($user)->get('/analytics/export?format=json');

    $response->assertOk();
    $response->assertHeader('content-type', 'application/json');

    $content = $response->streamedContent();
    $json = json_decode($content, true);
    expect($json)->toHaveKey('record_count')
        ->toHaveKey('logs');
    expect($json['record_count'])->toBe(1);
    expect($json['logs'][0]['provider'])->toBe('openai');
});

test('clear endpoint prunes old logs or resets all logs', function () {
    $user = User::factory()->create();

    AiUsageLog::logUsage(null, 'openai', 'gpt-4o-mini', 'viral', 100, 100, 200);
    AiUsageLog::logUsage(null, 'gemini', 'gemini-1.5-flash', 'specs', 100, 100, 200);

    expect(AiUsageLog::count())->toBe(2);

    $response = $this->actingAs($user)->postJson('/api/analytics/ai/clear', [
        'older_than_days' => 0,
    ]);

    $response->assertOk()
        ->assertJson(['success' => true, 'deleted_count' => 2]);

    expect(AiUsageLog::count())->toBe(0);
});

test('cost calculation accuracy across various providers and models', function () {
    // OpenAI GPT-4o-mini (prompt $0.15/M, completion $0.60/M)
    $miniCost = AiUsageLog::calculateCost('openai', 'gpt-4o-mini', 1000, 1000);
    expect($miniCost)->toEqualWithDelta(0.00075, 0.00001);

    // DeepSeek Chat (prompt $0.14/M, completion $0.28/M)
    $dsCost = AiUsageLog::calculateCost('deepseek', 'deepseek-chat', 1000, 1000);
    expect($dsCost)->toEqualWithDelta(0.00042, 0.00001);

    // Gemini Flash (prompt $0.075/M, completion $0.30/M)
    $geminiCost = AiUsageLog::calculateCost('gemini', 'gemini-1.5-flash', 1000, 1000);
    expect($geminiCost)->toEqualWithDelta(0.000375, 0.00001);

    // Fallback template (0 cost)
    $fallbackCost = AiUsageLog::calculateCost('internal', 'dynamic-engine', 1000, 1000, false);
    expect($fallbackCost)->toBe(0.0);
});

test('api draft generate endpoint records AI usage log', function () {
    $user = User::factory()->create();
    $tokenResult = $user->createToken('test_runner');
    $token = $tokenResult->plainTextToken;

    $post = Post::create([
        'id' => 'post_'.Str::random(12),
        'product_title' => 'RGB Gaming Keyboard',
        'affiliate_url' => 'https://shopee.ph/keyboard-xyz',
        'status' => 'draft',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/draft/generate', [
            'post_id' => $post->id,
            'caption_style' => 'viral_ai',
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'post_id',
            'caption',
            'tags',
            'ai_usage' => [
                'log_id',
                'provider',
                'model',
                'prompt_tokens',
                'completion_tokens',
                'total_tokens',
                'estimated_cost',
            ],
        ]);

    expect(AiUsageLog::where('post_id', $post->id)->exists())->toBeTrue();
});
