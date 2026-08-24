<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\WorkflowRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Automated/Index', [
            'workflows' => WorkflowRule::latest()->get(),
            'socialAccounts' => SocialAccount::where('platform', 'facebook')->get(),
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $rule = ! empty($id) ? WorkflowRule::find($id) : null;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'frequency' => 'required|string',
            'target_page' => 'required|string',
            'times' => 'nullable|array',
            'days' => 'nullable|array',
            'workflow_actions' => 'nullable|array',
            'action_contexts' => 'nullable|array',
            'general_context' => 'nullable|string',
            'weather_context' => 'nullable|string',
            'occasion_context' => 'nullable|string',
            'tones' => 'nullable|array',
            'personas' => 'nullable|array',
            'custom_persona' => 'nullable|string',
            'manual_prompt' => 'nullable|string',
            'status' => 'nullable|string|in:active,disabled',
        ]);

        // Fallback: match by exact rule name if rule wasn't found by ID
        if (! $rule && ! empty($validated['name'])) {
            $rule = WorkflowRule::where('name', $validated['name'])->first();
        }

        $data = [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'frequency' => $validated['frequency'],
            'target_page' => $validated['target_page'],
            'times' => $validated['times'] ?? ['08:00 AM'],
            'days' => $validated['days'] ?? [],
            'workflow_actions' => $validated['workflow_actions'] ?? [],
            'action_contexts' => $validated['action_contexts'] ?? [],
            'general_context' => $validated['general_context'] ?? '',
            'weather_context' => $validated['weather_context'] ?? '',
            'occasion_context' => $validated['occasion_context'] ?? '',
            'tones' => $validated['tones'] ?? [],
            'personas' => $validated['personas'] ?? [],
            'custom_persona' => $validated['custom_persona'] ?? '',
            'manual_prompt' => $validated['manual_prompt'] ?? '',
            'status' => $validated['status'] ?? 'active',
        ];

        if ($rule) {
            $rule->update($data);
        } else {
            $data['id'] = (! empty($id) && ! str_starts_with($id, 'preset_'))
                ? $id
                : 'sch_'.time().'_'.Str::random(4);
            $rule = WorkflowRule::create($data);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $rule->id, 'rule' => $rule]);
        }

        return back()->with('success', 'Workflow rule saved successfully.');
    }

    public function toggleStatus(string $id, Request $request)
    {
        $rule = WorkflowRule::findOrFail($id);
        $newStatus = $request->input('status') ?: ($rule->status === 'active' ? 'disabled' : 'active');
        $rule->update(['status' => $newStatus]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $rule->id, 'status' => $newStatus]);
        }

        return back()->with('success', 'Workflow rule status updated.');
    }

    public function execute(Request $request)
    {
        $actions = $request->input('actions', []);
        if (empty($actions)) {
            return response()->json(['success' => false, 'error' => 'No workflow actions provided'], 400);
        }

        $name = $request->input('name', 'Automated Workflow');
        $targetPage = $request->input('target_page', 'Tech Sulit Deals');
        $isPreview = (bool) $request->input('is_preview', false);
        $generalContext = $request->input('general_context', '');
        $weatherContext = $request->input('weather_context', '');
        $occasionContext = $request->input('occasion_context', '');
        $tones = $request->input('tones', []);
        $personas = $request->input('personas', []);
        $customPersona = $request->input('custom_persona', '');
        $manualPrompt = $request->input('manual_prompt', '');
        $shopeeUrl = $request->input('shopee_url', 'https://shopee.ph');

        // Philippine Time Context
        $now = now()->timezone('Asia/Manila');
        $hour = $now->hour;
        $dayName = $now->format('l');
        $dateStr = $now->format('F d, Y');
        $timeTag = ($hour >= 5 && $hour < 12) ? 'Morning ☕' : (($hour >= 12 && $hour < 18) ? 'Afternoon ☀️' : 'Evening 🌙');

        $defaultTags = Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH #BudolFinds');

        $hasAffiliateLink = (! empty($shopeeUrl) && $shopeeUrl !== 'https://shopee.ph') ||
            collect($actions)->contains(function ($a) {
                $l = strtolower($a);

                return str_contains($l, 'affiliate') || str_contains($l, 'shopee') || str_contains($l, 'buy link') || str_contains($l, 'voucher');
            }) ||
            str_contains($manualPrompt, 'http://') || str_contains($manualPrompt, 'https://');

        $disclosure = $hasAffiliateLink ? Setting::get('disclosure', 'Affiliate link. Price and availability may change anytime.') : '';

        // Build Title
        $finalTitle = "✨ {$timeTag} Community Lounge · {$name}";

        // Build Tone & Personality Caption
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

        // Estimate tokens
        $promptTokens = max(30, (int) (str_word_count($captionBody.' '.$generalContext) * 1.35));
        $completionTokens = max(50, (int) (str_word_count($finalCaption) * 1.35));
        $totalTokens = $promptTokens + $completionTokens;

        $postId = 'post_'.Str::random(12);
        $livePostUrl = null;

        if (! $isPreview) {
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

            // Log AI Usage
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

            // If action pipeline contains publish, publish to Facebook
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
                        }
                    } catch (\Exception) {
                        // ignore background network failures
                    }
                }
            }

            // Update rule last_run timestamp if workflow_id passed
            if ($ruleId = $request->input('workflow_id')) {
                WorkflowRule::where('id', $ruleId)->update(['last_run' => now()]);
            }
        }

        return response()->json([
            'success' => true,
            'job_id' => 'wf_'.time(),
            'rule_name' => $name,
            'post_id' => $postId,
            'post_url' => $livePostUrl ?: 'https://facebook.com',
            'link' => $livePostUrl ?: 'https://facebook.com',
            'title' => $finalTitle,
            'caption' => $finalCaption,
            'executed_steps' => $actions,
            'result' => [
                'title' => $finalTitle,
                'caption' => $finalCaption,
                'executed_steps' => $actions,
                'post_url' => $livePostUrl,
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $rule = WorkflowRule::find($id);
        if ($rule) {
            $rule->delete();
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Workflow rule deleted']);
        }

        return back()->with('success', 'Workflow rule deleted.');
    }
}
