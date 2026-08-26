<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookPublishService
{
    /**
     * Publish a post with its media files to a Facebook Page.
     * Replicates the exact working logic from autoaff backend/services/facebook.py.
     */
    public static function publish(Post $post, SocialAccount $account, string $message, ?string $link = null): array
    {
        $pageId = $account->account_id;
        $token = $account->access_token;
        $mediaFiles = is_array($post->media_files) ? array_values(array_filter($post->media_files)) : [];

        if (empty($mediaFiles) && ! empty($post->media_files) && is_string($post->media_files)) {
            $mediaFiles = json_decode($post->media_files, true) ?: [$post->media_files];
        }

        // Step 1: Upload images as unpublished photos for this page
        $photoIds = [];
        $photosToUpload = array_slice($mediaFiles, 0, 10);

        foreach ($photosToUpload as $index => $mediaUrl) {
            $fileData = self::resolveMediaFileData($mediaUrl, $post->id, $index);
            if (! $fileData) {
                continue;
            }

            try {
                $resp = Http::timeout(45)
                    ->attach('source', $fileData['content'], $fileData['filename'])
                    ->post("https://graph.facebook.com/v20.0/{$pageId}/photos?published=false&access_token=" . urlencode($token));

                if ($resp->successful()) {
                    $data = $resp->json();
                    if (! empty($data['id'])) {
                        $photoIds[] = (string) $data['id'];
                    }
                } else {
                    Log::warning("[FacebookPublishService] Photo upload API error for page {$pageId} ({$resp->status()}): " . $resp->body());
                }
            } catch (\Exception $e) {
                Log::warning("[FacebookPublishService] Photo upload exception for page {$pageId}: " . $e->getMessage());
            }
        }

        // Step 2: Publish feed post to this page
        // Format attached_media as form-encoded JSON strings: attached_media[0] = '{"media_fbid":"..."}'
        $params = [
            'message' => $message,
            'access_token' => $token,
        ];

        foreach ($photoIds as $i => $pid) {
            $params["attached_media[{$i}]"] = json_encode(['media_fbid' => (string) $pid]);
        }

        // If no photos could be uploaded, attach fallback link if available
        if (empty($photoIds) && $link && $link !== 'https://shopee.ph' && filter_var($link, FILTER_VALIDATE_URL)) {
            $params['link'] = $link;
        }

        try {
            $resp = Http::asForm()->timeout(60)->post("https://graph.facebook.com/v20.0/{$pageId}/feed", $params);

            if ($resp->successful()) {
                $data = $resp->json();
                if (! empty($data['id'])) {
                    $fbPostId = (string) $data['id'];

                    if (str_contains($fbPostId, '_')) {
                        [$pageIdPart, $storyIdPart] = explode('_', $fbPostId, 2);
                        $fbPostUrl = "https://www.facebook.com/permalink.php?story_fbid={$storyIdPart}&id={$pageIdPart}";
                    } else {
                        $fbPostUrl = "https://www.facebook.com/permalink.php?story_fbid={$fbPostId}&id={$pageId}";
                    }

                    return [
                        'success' => true,
                        'facebook_post_id' => $fbPostId,
                        'facebook_post_url' => $fbPostUrl,
                        'photo_count' => count($photoIds),
                    ];
                }
            }

            $err = $resp->json();
            Log::warning("[FacebookPublishService] Feed post error for page {$pageId}: " . json_encode($err));

            return [
                'success' => false,
                'error' => $err['error']['message'] ?? 'Graph API error publishing to Facebook feed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resolve binary file data and filename for a media file (local storage or remote download).
     */
    protected static function resolveMediaFileData(string $mediaUrl, string $postId, int $index): ?array
    {
        // 1. Check local storage path
        $localPath = self::resolveLocalPath($mediaUrl);
        if ($localPath && file_exists($localPath)) {
            $content = file_get_contents($localPath);
            if ($content !== false && strlen($content) > 0) {
                return [
                    'content' => $content,
                    'filename' => basename($localPath),
                ];
            }
        }

        // 2. If it's a remote URL, download it locally via ShopeeMediaService
        if (str_starts_with($mediaUrl, 'http://') || str_starts_with($mediaUrl, 'https://')) {
            $downloadedPath = ShopeeMediaService::downloadMedia($mediaUrl, $postId, $index);
            if ($downloadedPath) {
                $diskPath = self::resolveLocalPath($downloadedPath);
                if ($diskPath && file_exists($diskPath)) {
                    $content = file_get_contents($diskPath);
                    if ($content !== false && strlen($content) > 0) {
                        return [
                            'content' => $content,
                            'filename' => basename($diskPath),
                        ];
                    }
                }
            }

            // Direct in-memory download fallback
            try {
                $resp = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer' => 'https://shopee.ph/',
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                ])
                ->timeout(20)
                ->withoutVerifying()
                ->get($mediaUrl);

                if ($resp->successful() && strlen($resp->body()) > 500) {
                    $body = $resp->body();
                    $info = @getimagesizefromstring($body);
                    if ($info && isset($info[0], $info[1])) {
                        $w = (int) $info[0];
                        $h = (int) $info[1];
                        if ($w < 300 || $h < 300 || ((max($w, $h) / max(1, min($w, $h))) > 2.2)) {
                            return null;
                        }
                    }
                    $clean = strtok(basename($mediaUrl), '?');
                    $filename = str_contains($clean, '.') ? $clean : "{$clean}.jpg";
                    return [
                        'content' => $body,
                        'filename' => $filename,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("[FacebookPublishService] Direct download failed for {$mediaUrl}: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Resolve absolute disk path from public storage URL.
     */
    protected static function resolveLocalPath(string $url): ?string
    {
        if (str_contains($url, '/storage/')) {
            $parts = explode('/storage/', $url, 2);
            return storage_path('app/public/' . $parts[1]);
        }

        if (str_starts_with($url, 'storage/')) {
            $relativePath = substr($url, strlen('storage/'));
            return storage_path('app/public/' . $relativePath);
        }

        if (file_exists($url)) {
            return $url;
        }

        return null;
    }
}
