<?php

namespace App\Jobs;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\WorkflowRule;
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
        $actions = $rule->workflow_actions ?? ['Generate Dynamic Time-Aware AI Greeting', 'Publish to Facebook Page'];
        $name = $rule->name;
        $targetPage = $rule->target_page ?? 'Tech Sulit Deals';
        $generalContext = $rule->general_context ?? '';
        $weatherContext = $rule->weather_context ?? '';
        $occasionContext = $rule->occasion_context ?? '';
        $tones = $rule->tones ?? ['taglish'];
        $manualPrompt = $rule->manual_prompt ?? '';
        $shopeeUrl = $rule->action_contexts['shopee_url'] ?? $rule->action_contexts['url'] ?? 'https://shopee.ph';

        $now = now()->timezone('Asia/Manila');
        $hour = $now->hour;
        $dayName = $now->format('l');
        $timeTag = ($hour >= 5 && $hour < 12) ? 'Morning ☕' : (($hour >= 12 && $hour < 18) ? 'Afternoon ☀️' : 'Evening 🌙');

        $defaultTags = Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH #BudolFinds');

        $hasAffiliateLink = (! empty($shopeeUrl) && $shopeeUrl !== 'https://shopee.ph' && ! str_starts_with($shopeeUrl, 'https://facebook.com')) ||
            collect($actions)->contains(function ($a) {
                $l = strtolower($a);

                return str_contains($l, 'affiliate') || str_contains($l, 'shopee') || str_contains($l, 'buy link') || str_contains($l, 'voucher');
            }) ||
            str_contains($manualPrompt, 'http://') || str_contains($manualPrompt, 'https://');

        $disclosure = $hasAffiliateLink ? Setting::get('disclosure', 'Affiliate link. Price and availability may change anytime.') : '';

        $finalTitle = "✨ {$timeTag} Community Lounge · {$name}";

        if ($hour >= 5 && $hour < 12) {
            $captionBody = "Magandang umaga mga ka-Tech Sulit! ☕\n\nSana maganda ang simula ng inyong {$dayName}! ✨\n\nQuick check-in for the {$targetPage} family:\nAno ang #1 tech upgrade or daily goal nyo for today?\n\nDrop your setup or thoughts in the comments below! 👇";
        } elseif ($hour >= 12 && $hour < 18) {
            $captionBody = "Happy afternoon {$targetPage} family! ☀️\n\nKamusta ang hapon nyo mga besh? Working from home ba kayo or on-site setup today?\n\nKumain na ba kayo ng lunch? Share your workspace vibes below! 👇";
        } else {
            $captionBody = "Good evening everyone! 🌙\n\nTime to unwind and relax after a productive {$dayName}! ✨\n\nAno ang pinaka-sulit na tech or budol find nyo recently?\n\nShare your thoughts with the community below! 👇";
        }

        if (! empty($generalContext)) {
            $captionBody .= "\n\n📌 Topic Spotlight: ".$generalContext;
        }

        if (! empty($weatherContext)) {
            $captionBody .= "\n\n🌤️ Weather Check: ".$weatherContext;
        }

        if (! empty($occasionContext)) {
            $captionBody .= "\n\n🎉 Special: ".$occasionContext;
        }

        if (! empty($manualPrompt)) {
            $captionBody .= "\n\n".$manualPrompt;
        }

        $finalCaption = trim(
            $captionBody.
            ($disclosure ? "\n\n".$disclosure : '').
            ($defaultTags ? "\n\n".$defaultTags : '')
        );

        $promptTokens = max(30, (int) (str_word_count($captionBody.' '.$generalContext) * 1.35));
        $completionTokens = max(50, (int) (str_word_count($finalCaption) * 1.35));
        $totalTokens = $promptTokens + $completionTokens;

        $postId = 'post_'.Str::random(12);
        $livePostUrl = null;

        // Record post
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

        if ($shouldPublish) {
            $account = SocialAccount::where('name', $targetPage)
                ->orWhere('platform', 'facebook')
                ->where('is_enabled', true)
                ->whereNotNull('access_token')
                ->first();

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
                        $post->update(['status' => 'failed']);
                        Log::error('[Workflow Job] FB Graph API failed: '.json_encode($fbResp->json()));
                    }
                } catch (\Exception $e) {
                    $post->update(['status' => 'failed']);
                    Log::error("[Workflow Job] FB publish exception: {$e->getMessage()}");
                }
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
        ];
    }
}
