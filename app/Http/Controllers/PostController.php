<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Services\AiContentGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Drafts/Index', [
            'posts' => Post::where('status', 'draft')->latest()->get(),
            'socialAccounts' => SocialAccount::where('platform', 'facebook')->where('is_enabled', true)->get(),
        ]);
    }

    public function history(): Response
    {
        return Inertia::render('History/Index', [
            'posts' => Post::whereIn('status', ['published', 'failed', 'approved'])->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_title' => 'nullable|string|max:255',
            'product_description' => 'nullable|string',
            'product_price' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'affiliate_url' => 'required|url',
            'caption' => 'nullable|string',
            'caption_style' => 'nullable|string',
            'tags' => 'nullable|string',
            'media_files' => 'nullable|array',
        ]);

        $title = ! empty($validated['product_title']) && $validated['product_title'] !== 'Shopee Deal'
            ? $validated['product_title']
            : 'Shopee Sulit Deal';

        $caption = $validated['caption'] ?? '';
        $style = $validated['caption_style'] ?? $request->input('caption_style', 'viral_ai');

        if (empty($caption) || str_starts_with($caption, 'Caption Style:')) {
            $aiGen = AiContentGeneratorService::generateProductDeal([
                'product_title' => $title,
                'product_description' => $validated['product_description'] ?? '',
                'product_price' => $validated['product_price'] ?? '',
                'shop_name' => $validated['shop_name'] ?? '',
                'affiliate_url' => $validated['affiliate_url'],
                'caption_style' => $style,
            ]);

            $caption = $aiGen['caption'];
            $tags = $aiGen['tags'];
            $postId = 'post_'.Str::random(12);

            $provider = Setting::get('ai_provider', 'openai');
            $model = Setting::get('ai_model', 'gpt-4o-mini');
            AiUsageLog::logUsage(
                $postId,
                $provider,
                $model,
                $style,
                $aiGen['prompt_tokens'],
                $aiGen['completion_tokens'],
                $aiGen['total_tokens']
            );
        } else {
            $postId = 'post_'.Str::random(12);
            $tags = $validated['tags'] ?? Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH');
        }

        $post = Post::create([
            'id' => $postId,
            'product_title' => $title,
            'product_description' => $validated['product_description'] ?? null,
            'product_price' => $validated['product_price'] ?? null,
            'shop_name' => $validated['shop_name'] ?? null,
            'affiliate_url' => $validated['affiliate_url'],
            'caption' => $caption,
            'tags' => $tags,
            'status' => 'draft',
            'media_files' => $validated['media_files'] ?? [],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return redirect()->route('drafts.index')->with('success', 'Post draft created with AI copy.');
    }

    public function storeCustom(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'tags' => 'nullable|string',
            'media_files' => 'nullable|array',
        ]);

        $post = Post::create([
            'id' => 'post_'.Str::random(12),
            'product_title' => $validated['title'],
            'affiliate_url' => 'https://shopee.ph',
            'caption' => $validated['caption'],
            'tags' => $validated['tags'] ?? '',
            'status' => 'draft',
            'media_files' => $validated['media_files'] ?? [],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return back()->with('success', 'Custom post draft created successfully.');
    }

    public function update(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'product_title' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'tags' => 'nullable|string',
            'status' => 'nullable|string|in:draft,approved,published,failed',
            'media_files' => 'nullable|array',
        ]);

        $post->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return back()->with('success', 'Post updated.');
    }

    public function approve(Request $request, string $id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'approved']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return back()->with('success', 'Post approved.');
    }

    public function publish(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $targetAccountIds = $request->input('target_account_ids', []);

        $accountsQuery = SocialAccount::where('platform', 'facebook')
            ->where('is_enabled', true)
            ->whereNotNull('access_token');

        if (! empty($targetAccountIds)) {
            $accountsQuery->whereIn('id', $targetAccountIds);
        }

        $accounts = $accountsQuery->get();

        if ($accounts->isEmpty()) {
            // Fallback to global setting if no social account record
            $defaultPageId = Setting::get('fb_page_id');
            $defaultToken = Setting::get('fb_page_token');

            if (! $defaultToken || ! $defaultPageId) {
                $post->update(['status' => 'failed']);
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'error' => 'No active Facebook Page or Access Token configured.'], 400);
                }

                return back()->with('error', 'No active Facebook Page or Access Token configured.');
            }
        }

        $publishedResults = [];
        $firstPostId = null;
        $firstPostUrl = null;

        foreach ($accounts as $account) {
            $pageId = $account->account_id;
            $token = $account->access_token;
            $extraConfig = $account->extra_config ?? [];

            $message = $post->caption;

            $hasAffiliateLink = (! empty($post->affiliate_url) && $post->affiliate_url !== 'https://shopee.ph' && ! str_starts_with($post->affiliate_url, 'https://facebook.com')) ||
                str_contains($message ?? '', 'shopee.ph') ||
                str_contains($message ?? '', 'lazada') ||
                str_contains($message ?? '', 'amzn.to');

            if ($hasAffiliateLink && ! empty($extraConfig['is_affiliate'])) {
                $disclosure = $extraConfig['disclosure'] ?? Setting::get('disclosure', 'Affiliate link. Price and availability may change anytime.');
                if ($disclosure && ! str_contains($message, $disclosure)) {
                    $message .= "\n\n".$disclosure;
                }
            }

            if ($post->tags && ! str_contains($message, $post->tags)) {
                $message .= "\n\n".$post->tags;
            }

            try {
                $response = Http::timeout(30)->post("https://graph.facebook.com/v20.0/{$pageId}/feed", [
                    'message' => $message,
                    'link' => $post->affiliate_url,
                    'access_token' => $token,
                ]);

                if ($response->successful()) {
                    $fbData = $response->json();
                    $fbPostId = $fbData['id'] ?? null;
                    $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";
                    $firstPostId = $firstPostId ?: $fbPostId;
                    $firstPostUrl = $firstPostUrl ?: $fbPostUrl;

                    $publishedResults[] = [
                        'account_id' => $pageId,
                        'name' => $account->name,
                        'facebook_post_id' => $fbPostId,
                        'facebook_post_url' => $fbPostUrl,
                    ];
                } else {
                    $err = $response->json();
                    $publishedResults[] = [
                        'account_id' => $pageId,
                        'name' => $account->name,
                        'error' => $err['error']['message'] ?? 'Unknown Graph API error',
                    ];
                }
            } catch (\Exception $e) {
                $publishedResults[] = [
                    'account_id' => $pageId,
                    'name' => $account->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $successfulPublishes = array_filter($publishedResults, fn ($r) => ! empty($r['facebook_post_id']));

        if (! empty($successfulPublishes)) {
            $post->update([
                'status' => 'published',
                'facebook_post_id' => $firstPostId,
                'facebook_post_url' => $firstPostUrl,
            ]);

            // Outbound webhook dispatch
            $webhookUrl = Setting::get('n8n_outbound_webhook');
            if ($webhookUrl) {
                try {
                    Http::timeout(10)->post($webhookUrl, [
                        'event' => 'post.published',
                        'post_id' => $post->id,
                        'product_title' => $post->product_title,
                        'affiliate_url' => $post->affiliate_url,
                        'facebook_post_id' => $firstPostId,
                        'facebook_post_url' => $firstPostUrl,
                        'published_results' => $publishedResults,
                        'published_at' => now()->toIso8601String(),
                    ]);
                } catch (\Exception) {
                    // Ignore webhook delivery failures in background
                }
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => 'published',
                    'post' => $post,
                    'results' => $publishedResults,
                ]);
            }

            return back()->with('success', 'Post published successfully to Facebook!');
        }

        $post->update(['status' => 'failed']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'status' => 'failed',
                'error' => 'Failed to publish to Facebook.',
                'results' => $publishedResults,
            ], 400);
        }

        return back()->with('error', 'Failed to publish post to Facebook.');
    }

    public function generateCaption(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $style = $request->input('caption_style', 'viral_ai');

        $aiGen = AiContentGeneratorService::generateProductDeal([
            'product_title' => $post->product_title ?? 'Featured Deal',
            'product_description' => $post->product_description ?? '',
            'product_price' => $post->product_price ?? '',
            'shop_name' => $post->shop_name ?? '',
            'affiliate_url' => $post->affiliate_url,
            'caption_style' => $style,
        ]);

        $post->update([
            'caption' => $aiGen['caption'],
            'tags' => $aiGen['tags'],
        ]);

        $provider = Setting::get('ai_provider', 'openai');
        $model = Setting::get('ai_model', 'gpt-4o-mini');

        AiUsageLog::logUsage(
            $post->id,
            $provider,
            $model,
            $style,
            $aiGen['prompt_tokens'],
            $aiGen['completion_tokens'],
            $aiGen['total_tokens']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'caption' => $aiGen['caption'],
                'tags' => $aiGen['tags'],
                'post' => $post,
            ]);
        }

    public function show(string $id): Response
    {
        $post = Post::findOrFail($id);

        return Inertia::render('Drafts/Show', [
            'post' => $post,
            'socialAccounts' => SocialAccount::where('platform', 'facebook')->where('is_enabled', true)->get(),
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function uploadMedia(Request $request, string $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,mp4|max:51200',
        ]);

        $file = $request->file('file');
        $filename = 'media_'.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/posts', $filename, 'public');
        $url = '/storage/'.$path;

        $media = $post->media_files ?? [];
        $media[] = $url;
        $post->update(['media_files' => $media]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'media_files' => $media,
                'url' => $url,
            ]);
        }

        return back()->with('success', 'Media uploaded successfully.');
    }

    public function deleteMedia(Request $request, string $id, string $filename)
    {
        $post = Post::findOrFail($id);

        $media = array_values(array_filter($post->media_files ?? [], function ($item) use ($filename) {
            return ! str_ends_with($item, $filename);
        }));

        $post->update(['media_files' => $media]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'media_files' => $media,
            ]);
        }

        return back()->with('success', 'Media deleted.');
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return redirect()->route('drafts.index')->with('success', 'Post deleted.');
    }
}
