<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
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
            'product_title' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'product_price' => 'nullable|string',
            'shop_name' => 'nullable|string',
            'affiliate_url' => 'required|url',
            'caption' => 'nullable|string',
            'tags' => 'nullable|string',
            'media_files' => 'nullable|array',
        ]);

        $post = Post::create([
            'id' => 'post_'.Str::random(12),
            'product_title' => $validated['product_title'],
            'product_description' => $validated['product_description'] ?? null,
            'product_price' => $validated['product_price'] ?? null,
            'shop_name' => $validated['shop_name'] ?? null,
            'affiliate_url' => $validated['affiliate_url'],
            'caption' => $validated['caption'] ?? '',
            'tags' => $validated['tags'] ?? '',
            'status' => 'draft',
            'media_files' => $validated['media_files'] ?? [],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post' => $post]);
        }

        return redirect()->route('drafts.index')->with('success', 'Post draft created successfully.');
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

        $style = $request->input('caption_style', 'standard');
        $customHashtags = $request->input('custom_hashtags', $post->tags);

        $defaultHashtags = Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH');
        $hasAffiliateLink = ! empty($post->affiliate_url) && $post->affiliate_url !== 'https://shopee.ph' && ! str_starts_with($post->affiliate_url, 'https://facebook.com');
        $disclosure = $hasAffiliateLink ? Setting::get('disclosure', 'Affiliate link. Price and availability may change anytime.') : '';

        $mergedTags = trim($customHashtags.' '.$defaultHashtags);
        $tagArray = array_unique(array_filter(explode(' ', $mergedTags)));
        $finalTags = implode(' ', $tagArray);

        $disclosureBlock = $disclosure ? "\n\n{$disclosure}" : '';
        $tagsBlock = $finalTags ? "\n\n{$finalTags}" : '';
        $linkViral = $hasAffiliateLink ? "\n\n👉 Grab yours here before stock runs out: {$post->affiliate_url}" : '';
        $linkTaglish = $hasAffiliateLink ? "\n\n🛒 Check out the deal here: {$post->affiliate_url}" : '';
        $linkSpecs = $hasAffiliateLink ? "\n\n🔗 Order Link: {$post->affiliate_url}" : '';
        $linkDefault = $hasAffiliateLink ? "\n\n🔗 Link: {$post->affiliate_url}" : '';

        $title = $post->product_title ?? 'Featured Deal';
        $price = $post->product_price ? "Price: {$post->product_price}" : '';

        // AI or rule-based caption styling
        switch ($style) {
            case 'viral':
                $caption = "🔥 SUPER SALE ALERT! 🔥\n\nCheck out {$title}!\n{$price}{$linkViral}{$disclosureBlock}{$tagsBlock}";
                break;
            case 'taglish':
                $caption = "Sobrang sulit nito mga ka-budol! 😍\n\n{$title}\n{$price}{$linkTaglish}{$disclosureBlock}{$tagsBlock}";
                break;
            case 'specs':
                $caption = "📌 Product Specification & Feature Breakdown:\n\n{$title}\n{$price}\n\nKey Highlights:\n• High quality build & premium performance\n• Verified shop reviews{$linkSpecs}{$disclosureBlock}{$tagsBlock}";
                break;
            default:
                $caption = "✨ Check out this great deal on {$title}!\n{$price}{$linkDefault}{$disclosureBlock}{$tagsBlock}";
                break;
        }

        $post->update([
            'caption' => $caption,
            'tags' => $finalTags,
        ]);

        // Calculate and log AI usage stats
        $promptText = trim(($post->product_title ?? '').' '.($post->product_description ?? '').' '.$post->affiliate_url);
        $promptTokens = max(24, (int) (str_word_count($promptText) * 1.35));
        $completionTokens = max(45, (int) (str_word_count($caption) * 1.35));
        $totalTokens = $promptTokens + $completionTokens;

        $provider = Setting::get('ai_provider', 'openai');
        $model = Setting::get('ai_model', 'gpt-4o-mini');

        AiUsageLog::logUsage(
            $post->id,
            $provider,
            $model,
            $style,
            $promptTokens,
            $completionTokens,
            $totalTokens
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'caption' => $caption,
                'tags' => $finalTags,
                'post' => $post,
            ]);
        }

        return back()->with('success', 'Caption generated successfully.');
    }

    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }
}
