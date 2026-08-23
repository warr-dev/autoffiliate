<?php

use App\Models\User;
use App\Models\WorkflowRule;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated user can view automated workflows page', function () {
    $user = User::factory()->create();

    WorkflowRule::create([
        'id' => 'sch_test_1',
        'name' => 'Morning Greeting Test',
        'category' => 'Connection & Community',
        'frequency' => 'daily',
        'times' => ['08:00 AM'],
        'target_page' => 'Tech Sulit Deals',
        'workflow_actions' => ['Generate Dynamic Time-Aware AI Greeting'],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get('/automated');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Automated/Index')
            ->has('workflows')
            ->has('socialAccounts')
        );
});

test('user can create a workflow rule', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/automated', [
        'name' => 'Flash Sale Alerts',
        'category' => 'Affiliate Deals',
        'frequency' => 'daily',
        'target_page' => 'Tech Sulit Deals',
        'times' => ['12:00 PM', '06:00 PM'],
        'workflow_actions' => ['Extract Shopee Media', 'Generate High-Converting Deal Hook'],
        'general_context' => 'Flash Sale Deals',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('workflow_rules', [
        'name' => 'Flash Sale Alerts',
        'category' => 'Affiliate Deals',
    ]);
});

test('user can toggle workflow rule status', function () {
    $user = User::factory()->create();

    $rule = WorkflowRule::create([
        'id' => 'sch_toggle_test',
        'name' => 'Toggle Test Rule',
        'category' => 'Brand Promotion',
        'frequency' => 'daily',
        'times' => ['08:00 AM'],
        'target_page' => 'Tech Sulit Deals',
        'workflow_actions' => ['Generate Dynamic Greeting'],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->postJson("/automated/{$rule->id}/toggle", [
        'status' => 'disabled',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', 'disabled');

    $this->assertEquals('disabled', $rule->fresh()->status);
});

test('user can execute workflow rule server-side', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/automated/execute', [
        'name' => 'Instant Community Test',
        'actions' => ['Generate Dynamic Time-Aware AI Greeting'],
        'target_page' => 'Tech Sulit Deals',
        'is_preview' => true,
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'rule_name',
            'title',
            'caption',
            'executed_steps',
        ])
        ->assertJsonPath('success', true);
});

test('user can delete workflow rule', function () {
    $user = User::factory()->create();

    $rule = WorkflowRule::create([
        'id' => 'sch_delete_test',
        'name' => 'Delete Test Rule',
        'category' => 'Brand Promotion',
        'frequency' => 'daily',
        'times' => ['08:00 AM'],
        'target_page' => 'Tech Sulit Deals',
        'workflow_actions' => ['Generate Dynamic Greeting'],
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->delete("/automated/{$rule->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('workflow_rules', ['id' => 'sch_delete_test']);
});

test('rule is due even if Hostinger cron is delayed by 10 minutes (grace window test)', function () {
    $rule = WorkflowRule::create([
        'id' => 'sch_delay_test',
        'name' => 'Morning Deal',
        'category' => 'Affiliate Deals',
        'frequency' => 'daily',
        'times' => ['08:00 AM'],
        'target_page' => 'Tech Sulit Deals',
        'status' => 'active',
    ]);

    // Simulated Time: 08:10 AM (10 minutes after scheduled slot)
    $delayedCronTime = Carbon::parse('2026-08-23 08:10:00', 'Asia/Manila');

    $this->assertTrue($rule->isDue($delayedCronTime));

    // Simulate execution recorded at 08:10 AM
    $rule->update(['last_run' => $delayedCronTime]);

    // Simulated Next Minute: 08:11 AM
    $nextMinute = Carbon::parse('2026-08-23 08:11:00', 'Asia/Manila');

    // Should NOT run again because it already executed for today's 08:00 AM slot
    $this->assertFalse($rule->isDue($nextMinute));
});

test('web cron endpoint evaluates workflow rules via HTTP', function () {
    $rule = WorkflowRule::create([
        'id' => 'sch_cron_api_test',
        'name' => 'Web Cron Rule',
        'category' => 'Brand Promotion',
        'frequency' => 'daily',
        'times' => ['08:00 AM'],
        'target_page' => 'Tech Sulit Deals',
        'workflow_actions' => ['Generate Dynamic Time-Aware AI Greeting'],
        'status' => 'active',
    ]);

    $response = $this->getJson('/api/cron/workflows');

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'timestamp',
            'output',
        ])
        ->assertJsonPath('success', true);
});
