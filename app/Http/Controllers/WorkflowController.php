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
            $actions = ['Generate Dynamic Time-Aware AI Greeting', 'Publish to Facebook Page'];
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
                        } else {
                            $errJson = $fbResp->json();
                            $errMsg = $errJson['error']['message'] ?? json_encode($errJson);
                            $publishError = "Facebook API error: {$errMsg}";
                            $post->update(['status' => 'failed']);
                        }
                    } catch (\Exception $e) {
                        $publishError = "Facebook connection exception: {$e->getMessage()}";
                        $post->update(['status' => 'failed']);
                    }
                } else {
                    $publishWarning = "No active Facebook account connected for '{$targetPage}'. Post saved as draft.";
                }
            }

            // Update rule last_run timestamp if workflow_id passed
            if ($ruleId = $request->input('workflow_id', $request->input('id'))) {
                WorkflowRule::where('id', $ruleId)->update(['last_run' => now()]);
            }
        }

        return response()->json([
            'success' => true,
            'job_id' => 'wf_'.time(),
            'rule_name' => $name,
            'post_id' => $postId,
            'post_url' => $livePostUrl,
            'published' => (bool) $livePostUrl,
            'warning' => $publishWarning ?? null,
            'error' => $publishError ?? null,
            'link' => $livePostUrl ?: null,
            'title' => $finalTitle,
            'caption' => $finalCaption,
            'executed_steps' => $actions,
            'result' => [
                'title' => $finalTitle,
                'caption' => $finalCaption,
                'executed_steps' => $actions,
                'post_url' => $livePostUrl,
                'published' => (bool) $livePostUrl,
                'warning' => $publishWarning ?? null,
                'error' => $publishError ?? null,
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

    public function export(Request $request)
    {
        $workflows = WorkflowRule::latest()->get();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'count' => $workflows->count(),
            'workflows' => $workflows->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'category' => $w->category,
                    'frequency' => $w->frequency,
                    'times' => $w->times,
                    'days' => $w->days,
                    'target_page' => $w->target_page,
                    'workflow_actions' => $w->workflow_actions,
                    'action_contexts' => $w->action_contexts,
                    'general_context' => $w->general_context,
                    'weather_context' => $w->weather_context,
                    'occasion_context' => $w->occasion_context,
                    'tones' => $w->tones,
                    'personas' => $w->personas,
                    'custom_persona' => $w->custom_persona,
                    'manual_prompt' => $w->manual_prompt,
                    'status' => $w->status,
                ];
            }),
        ];

        if ($request->query('download') || ! $request->wantsJson()) {
            $filename = 'autoffiliate-workflows-'.now()->format('Y-m-d-His').'.json';

            return response()->streamDownload(function () use ($exportData) {
                echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }, $filename, [
                'Content-Type' => 'application/json',
            ]);
        }

        return response()->json($exportData);
    }

    public function import(Request $request)
    {
        $rawContent = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $rawContent = file_get_contents($file->getRealPath());
        } elseif ($request->has('workflows')) {
            $data = $request->input('workflows');
            if (is_array($data)) {
                $rawContent = json_encode(['workflows' => $data]);
            } else {
                $rawContent = (string) $data;
            }
        } else {
            $rawContent = $request->getContent();
        }

        if (empty($rawContent)) {
            return response()->json(['success' => false, 'error' => 'No import data provided'], 400);
        }

        $decoded = json_decode($rawContent, true);

        if (! is_array($decoded)) {
            return response()->json(['success' => false, 'error' => 'Invalid JSON file or format'], 400);
        }

        // Support both direct array of rules or { workflows: [...] } structure
        $rules = $decoded['workflows'] ?? (isset($decoded[0]) ? $decoded : [$decoded]);

        if (! is_array($rules) || empty($rules)) {
            return response()->json(['success' => false, 'error' => 'No valid workflow rules found in import data'], 400);
        }

        $mode = $request->input('mode', 'merge'); // 'merge' or 'replace'

        if ($mode === 'replace') {
            WorkflowRule::truncate();
        }

        $importedCount = 0;

        foreach ($rules as $ruleData) {
            if (empty($ruleData['name'])) {
                continue;
            }

            $id = $ruleData['id'] ?? ('sch_'.time().'_'.Str::random(4));

            $attributes = [
                'name' => $ruleData['name'],
                'category' => $ruleData['category'] ?? 'Connection & Community',
                'frequency' => $ruleData['frequency'] ?? 'daily',
                'times' => is_array($ruleData['times'] ?? null) ? $ruleData['times'] : ['08:00 AM'],
                'days' => is_array($ruleData['days'] ?? null) ? $ruleData['days'] : [],
                'target_page' => $ruleData['target_page'] ?? $ruleData['targetPage'] ?? 'Tech Sulit Deals',
                'workflow_actions' => is_array($ruleData['workflow_actions'] ?? null)
                    ? $ruleData['workflow_actions']
                    : (is_array($ruleData['workflowActions'] ?? null) ? $ruleData['workflowActions'] : []),
                'action_contexts' => is_array($ruleData['action_contexts'] ?? null)
                    ? $ruleData['action_contexts']
                    : (is_array($ruleData['actionContexts'] ?? null) ? $ruleData['actionContexts'] : []),
                'general_context' => $ruleData['general_context'] ?? $ruleData['generalContext'] ?? '',
                'weather_context' => $ruleData['weather_context'] ?? $ruleData['weatherContext'] ?? '',
                'occasion_context' => $ruleData['occasion_context'] ?? $ruleData['occasionContext'] ?? '',
                'tones' => is_array($ruleData['tones'] ?? null) ? $ruleData['tones'] : [],
                'personas' => is_array($ruleData['personas'] ?? null) ? $ruleData['personas'] : [],
                'custom_persona' => $ruleData['custom_persona'] ?? $ruleData['customPersona'] ?? '',
                'manual_prompt' => $ruleData['manual_prompt'] ?? $ruleData['manualPrompt'] ?? '',
                'status' => ($ruleData['status'] ?? 'active') === 'disabled' ? 'disabled' : 'active',
            ];

            WorkflowRule::updateOrCreate(
                ['id' => $id],
                $attributes
            );

            $importedCount++;
        }

        $allRules = WorkflowRule::latest()->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} workflow rule(s).",
                'imported_count' => $importedCount,
                'workflows' => $allRules,
            ]);
        }

        return back()->with('success', "Successfully imported {$importedCount} workflow rule(s).");
    }
}
