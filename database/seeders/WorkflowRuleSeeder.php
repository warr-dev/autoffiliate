<?php

namespace Database\Seeders;

use App\Models\WorkflowRule;
use Illuminate\Database\Seeder;

class WorkflowRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'id' => 'wf_morning_community',
                'name' => 'Morning Taglish Community Check-in',
                'category' => 'Connection & Community',
                'frequency' => 'daily',
                'times' => ['08:00'],
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'target_page' => 'Tech Sulit Deals',
                'workflow_actions' => ['dynamic_greeting', 'ai_caption_generate', 'publish_facebook'],
                'tones' => ['pinoy_taglish', 'casual'],
                'personas' => ['Tech Sulit Admin'],
                'general_context' => 'Warm, casual Taglish morning check-in for tech deals group',
                'status' => 'active',
            ],
            [
                'id' => 'wf_noon_flash_deals',
                'name' => '12PM Midday Flash Deals Pipeline',
                'category' => 'Deals & Promotions',
                'frequency' => 'daily',
                'times' => ['12:00'],
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'target_page' => 'Tech Sulit Deals',
                'workflow_actions' => ['extract_shopee', 'ai_caption_generate', 'publish_facebook'],
                'tones' => ['urgency_flash', 'viral_ai'],
                'personas' => ['Deal Hunter'],
                'general_context' => 'Highlight high discount Shopee flash vouchers',
                'status' => 'active',
            ],
            [
                'id' => 'wf_evening_tech_roundup',
                'name' => 'Evening Tech Specs Showcase',
                'category' => 'Product Highlights',
                'frequency' => 'daily',
                'times' => ['19:00'],
                'days' => ['Monday', 'Wednesday', 'Friday'],
                'target_page' => 'Tech Sulit Deals',
                'workflow_actions' => ['extract_shopee', 'ai_caption_generate'],
                'tones' => ['specs_tech', 'review_story'],
                'personas' => ['Tech Reviewer'],
                'general_context' => 'Detailed technical breakdown of top-rated gadgets',
                'status' => 'active',
            ],
            [
                'id' => 'wf_event_webhook_deal',
                'name' => 'Event: Incoming Webhook Deal Auto-Publish',
                'category' => 'Deals & Promotions',
                'frequency' => 'event_based',
                'times' => [],
                'days' => [],
                'target_page' => 'Tech Sulit Deals',
                'workflow_actions' => ['incoming_webhook_trigger', 'extract_shopee', 'ai_caption_generate', 'publish_facebook'],
                'tones' => ['urgency_flash', 'pinoy_taglish'],
                'personas' => ['Deal Hunter'],
                'general_context' => 'Triggers instantly when an affiliate link webhook is received from n8n or external bot',
                'status' => 'active',
            ],
            [
                'id' => 'wf_event_price_drop',
                'name' => 'Event: Price Drop Alert & Flash Restock',
                'category' => 'Deals & Promotions',
                'frequency' => 'event_based',
                'times' => [],
                'days' => [],
                'target_page' => 'Tech Sulit Deals',
                'workflow_actions' => ['price_drop_trigger', 'ai_caption_generate', 'publish_facebook'],
                'tones' => ['urgency_flash'],
                'personas' => ['Flash Sale Bot'],
                'general_context' => 'Fires immediately upon detecting price cuts over 30%',
                'status' => 'active',
            ],

        ];

        foreach ($rules as $rule) {
            WorkflowRule::updateOrCreate(['id' => $rule['id']], $rule);
        }
    }
}
