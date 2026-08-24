<?php

namespace App\Jobs;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\WorkflowRule;
use App\Services\AiContentGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExecuteWorkflowRuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public WorkflowRule $rule
    ) {}

    public function handle(): array
    {
        $rule = $this->rule;
        $actions = ! empty($rule->workflow_actions)
            ? $rule->workflow_actions
            : ['Generate Dynamic Time-Aware AI Greeting', 'Publish to Facebook Page'];
        $name = $rule->name;
        $targetPage = $rule->target_page ?? 'Tech Sulit Deals';
        $generalContext = $rule->general_context ?? '';
        $weatherContext = $rule->weather_context ?? '';
        $occasionContext = $rule->occasion_context ?? '';
        $tones = $rule->tones ?? ['taglish'];
        $personas = $rule->personas ?? [];
        $manualPrompt = $rule->manual_prompt ?? '';
        $shopeeUrl = $rule->action_contexts['shopee_url'] ?? $rule->action_contexts['url'] ?? 'https://shopee.ph';

        $defaultTags = Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH #BudolFinds');

        // Generate Dynamic & Varied Content via AI Service / Dynamic Template Engine
        $generated = AiContentGeneratorService::generate([
            'name' => $name,
            'target_page' => $targetPage,
            'category' => $rule->category ?? 'Connection & Community',
            'tones' => $tones,
            'personas' => $personas,
            'general_context' => $generalContext,
            'weather_context' => $weatherContext,
            'occasion_context' => $occasionContext,
            'manual_prompt' => $manualPrompt,
            'shopee_url' => $shopeeUrl,
        ]);

        $finalTitle = $generated['title'];
        $finalCaption = $generated['caption'];
        $totalTokens = $generated['total_tokens'];
        $promptTokens = $generated['prompt_tokens'];
        $completionTokens = $generated['completion_tokens'];

        $postId = 'post_'.Str::random(12);
        $livePostUrl = null;

        // Record post in database
        $post = Post::create([
            'id' => $postId,
            'product_title' => $finalTitle,
            'affiliate_url' => $shopeeUrl,
            'caption' => $finalCaption,
            'tags' => $defaultTags,
            'status' => 'draft',
            'media_files' => [],
        ]);

        // Log AI tokens
        $provider = Setting::get('ai_provider', 'openai');
        $model = Setting::get('ai_model', 'gpt-4o-mini');
        AiUsageLog::logUsage(
            $postId,
            $provider,
            $model,
            ! empty($tones) ? $tones[0] : 'taglish',
            $promptTokens,
            $completionTokens,
            $totalTokens
        );

        // Check if publish action requested
        $shouldPublish = collect($actions)->contains(fn ($a) => str_contains(strtolower($a), 'publish') || str_contains(strtolower($a), 'facebook'));
        $publishWarning = null;
        $publishError = null;

        if ($shouldPublish) {
            $account = SocialAccount::where(function ($query) use ($targetPage) {
                $query->where('name', $targetPage)
                    ->orWhere('account_id', $targetPage);
            })
                ->where('platform', 'facebook')
                ->where('is_enabled', true)
                ->whereNotNull('access_token')
                ->where('access_token', '!=', '')
                ->first();

            if (! $account) {
                $account = SocialAccount::where('platform', 'facebook')
                    ->where('is_enabled', true)
                    ->whereNotNull('access_token')
                    ->where('access_token', '!=', '')
                    ->first();
            }

            if ($account) {
                try {
                    $fbResp = Http::timeout(30)->post("https://graph.facebook.com/v20.0/{$account->account_id}/feed", [
                        'message' => $finalCaption,
                        'access_token' => $account->access_token,
                    ]);

                    if ($fbResp->successful()) {
                        $fbId = $fbResp->json()['id'] ?? null;
                        $livePostUrl = $fbId ? "https://facebook.com/{$fbId}" : "https://facebook.com/{$account->account_id}";
                        $post->update([
                            'status' => 'published',
                            'facebook_post_id' => $fbId,
                            'facebook_post_url' => $livePostUrl,
                        ]);
                        Log::info("[Workflow Job] Published to Facebook: {$livePostUrl}");
                    } else {
                        $errJson = $fbResp->json();
                        $errMsg = $errJson['error']['message'] ?? json_encode($errJson);
                        $publishError = "Facebook API error: {$errMsg}";
                        $post->update(['status' => 'failed']);
                        Log::error("[Workflow Job] FB Graph API failed: {$errMsg}");
                    }
                } catch (\Exception $e) {
                    $publishError = "Facebook network exception: {$e->getMessage()}";
                    $post->update(['status' => 'failed']);
                    Log::error("[Workflow Job] FB publish exception: {$e->getMessage()}");
                }
            } else {
                $publishWarning = "No active Facebook account connected for '{$targetPage}'. Saved as draft.";
                Log::warning("[Workflow Job] {$publishWarning}");
            }
        }

        $rule->update(['last_run' => now()]);

        return [
            'post_id' => $postId,
            'title' => $finalTitle,
            'caption' => $finalCaption,
            'tags' => $defaultTags,
            'tokens_used' => $totalTokens,
            'facebook_post_url' => $livePostUrl,
            'published' => (bool) $livePostUrl,
            'warning' => $publishWarning,
            'error' => $publishError,
        ];
    }
}
