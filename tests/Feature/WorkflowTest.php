<?php

use App\Models\User;
use App\Models\WorkflowRule;
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
