<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookPublishService
{
    /**
     * Publish a post with its media files (single image, multi-image carousel, video, or link) to a Facebook Page.
     */
    public static function publish(Post $post, SocialAccount $account, string $message, ?string $link = null): array
    {
        $pageId = $account->account_id;
        $token = $account->access_token;
        $mediaFiles = is_array($post->media_files) ? array_values(array_filter($post->media_files)) : [];

        if (empty($mediaFiles) && ! empty($post->media_files) && is_string($post->media_files)) {
            $mediaFiles = [$post->media_files];
        }

        // Case 1: Multi-Image Carousel Post (2 or more photos)
        if (count($mediaFiles) > 1) {
            $multiResult = self::publishMultiPhoto($pageId, $token, $mediaFiles, $message);
            if ($multiResult['success']) {
                return $multiResult;
            }
            Log::warning("[FacebookPublishService] Multi-photo publish failed for page {$pageId}, falling back to single photo: " . ($multiResult['error'] ?? ''));
        }

        // Case 2: Single Media File (Image or Video)
        if (count($mediaFiles) >= 1) {
            $firstMedia = $mediaFiles[0];
            if (self::isVideoFile($firstMedia)) {
                $videoResult = self::publishVideo($pageId, $token, $firstMedia, $message);
                if ($videoResult['success']) {
                    return $videoResult;
                }
            } else {
                $photoResult = self::publishSinglePhoto($pageId, $token, $firstMedia, $message);
                if ($photoResult['success']) {
                    return $photoResult;
                }
            }
            Log::warning("[FacebookPublishService] Single media publish failed for page {$pageId}, falling back to standard feed: " . ($photoResult['error'] ?? ''));
        }

        // Case 3: Standard Feed Post with Link or Text
        return self::publishFeedPost($pageId, $token, $message, $link ?: $post->affiliate_url);
    }

    /**
     * Upload and publish multiple photos as a single multi-photo feed post.
     */
    protected static function publishMultiPhoto(string $pageId, string $token, array $mediaFiles, string $message): array
    {
        $attachedMedia = [];
        $photosToUpload = array_slice($mediaFiles, 0, 10); // Facebook supports up to 10 attached photos per post

        foreach ($photosToUpload as $mediaUrl) {
            $uploadRes = self::uploadUnpublishedPhoto($pageId, $token, $mediaUrl);
            if ($uploadRes['success'] && ! empty($uploadRes['id'])) {
                $attachedMedia[] = ['media_fbid' => (string) $uploadRes['id']];
            }
        }

        if (empty($attachedMedia)) {
            return [
                'success' => false,
                'error' => 'Failed to upload any photo attachments to Facebook.',
            ];
        }

        // If only 1 photo succeeded out of multiple, publish as single photo with caption
        if (count($attachedMedia) === 1) {
            return self::publishSinglePhoto($pageId, $token, $photosToUpload[0], $message);
        }

        // Publish feed post with all attached media IDs
        try {
            $resp = Http::timeout(45)->post("https://graph.facebook.com/v20.0/{$pageId}/feed", [
                'message' => $message,
                'attached_media' => $attachedMedia,
                'access_token' => $token,
            ]);

            if ($resp->successful()) {
                $fbData = $resp->json();
                $fbPostId = $fbData['id'] ?? null;
                $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";

                return [
                    'success' => true,
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                ];
            }

            $err = $resp->json();
            return [
                'success' => false,
                'error' => $err['error']['message'] ?? 'Graph API error attaching multi-photos',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Upload an unpublished photo to Facebook to get a media_fbid.
     */
    protected static function uploadUnpublishedPhoto(string $pageId, string $token, string $mediaUrl): array
    {
        try {
            $localPath = self::resolveLocalPath($mediaUrl);

            if ($localPath && file_exists($localPath)) {
                $resp = Http::timeout(30)
                    ->attach('source', file_get_contents($localPath), basename($localPath))
                    ->post("https://graph.facebook.com/v20.0/{$pageId}/photos", [
                        'published' => 'false',
                        'access_token' => $token,
                    ]);
            } else {
                $resp = Http::timeout(30)->post("https://graph.facebook.com/v20.0/{$pageId}/photos", [
                    'url' => self::formatMediaUrl($mediaUrl),
                    'published' => 'false',
                    'access_token' => $token,
                ]);
            }

            if ($resp->successful()) {
                $data = $resp->json();
                return ['success' => true, 'id' => $data['id'] ?? null];
            }

            $err = $resp->json();
            return ['success' => false, 'error' => $err['error']['message'] ?? 'Failed photo upload'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Publish a single photo directly with message caption.
     */
    protected static function publishSinglePhoto(string $pageId, string $token, string $mediaUrl, string $message): array
    {
        try {
            $localPath = self::resolveLocalPath($mediaUrl);

            if ($localPath && file_exists($localPath)) {
                $resp = Http::timeout(45)
                    ->attach('source', file_get_contents($localPath), basename($localPath))
                    ->post("https://graph.facebook.com/v20.0/{$pageId}/photos", [
                        'caption' => $message,
                        'access_token' => $token,
                    ]);
            } else {
                $resp = Http::timeout(45)->post("https://graph.facebook.com/v20.0/{$pageId}/photos", [
                    'url' => self::formatMediaUrl($mediaUrl),
                    'caption' => $message,
                    'access_token' => $token,
                ]);
            }

            if ($resp->successful()) {
                $data = $resp->json();
                $fbPostId = $data['post_id'] ?? $data['id'] ?? null;
                $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";

                return [
                    'success' => true,
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                ];
            }

            $err = $resp->json();
            return ['success' => false, 'error' => $err['error']['message'] ?? 'Single photo publish error'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Publish a video to the page.
     */
    protected static function publishVideo(string $pageId, string $token, string $videoUrl, string $message): array
    {
        try {
            $localPath = self::resolveLocalPath($videoUrl);

            if ($localPath && file_exists($localPath)) {
                $resp = Http::timeout(60)
                    ->attach('source', file_get_contents($localPath), basename($localPath))
                    ->post("https://graph.facebook.com/v20.0/{$pageId}/videos", [
                        'description' => $message,
                        'access_token' => $token,
                    ]);
            } else {
                $resp = Http::timeout(60)->post("https://graph.facebook.com/v20.0/{$pageId}/videos", [
                    'file_url' => self::formatMediaUrl($videoUrl),
                    'description' => $message,
                    'access_token' => $token,
                ]);
            }

            if ($resp->successful()) {
                $data = $resp->json();
                $fbPostId = $data['id'] ?? null;
                $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";

                return [
                    'success' => true,
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                ];
            }

            $err = $resp->json();
            return ['success' => false, 'error' => $err['error']['message'] ?? 'Video publish error'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Standard text/link feed post fallback.
     */
    protected static function publishFeedPost(string $pageId, string $token, string $message, ?string $link): array
    {
        try {
            $payload = [
                'message' => $message,
                'access_token' => $token,
            ];

            if ($link && $link !== 'https://shopee.ph' && filter_var($link, FILTER_VALIDATE_URL)) {
                $payload['link'] = $link;
            }

            $resp = Http::timeout(30)->post("https://graph.facebook.com/v20.0/{$pageId}/feed", $payload);

            if ($resp->successful()) {
                $data = $resp->json();
                $fbPostId = $data['id'] ?? null;
                $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";

                return [
                    'success' => true,
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                ];
            }

            $err = $resp->json();
            return [
                'success' => false,
                'error' => $err['error']['message'] ?? 'Feed post error',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve local disk file path if media URL points to local storage.
     */
    protected static function resolveLocalPath(string $url): ?string
    {
        if (str_starts_with($url, '/storage/')) {
            $relativePath = substr($url, strlen('/storage/'));
            return storage_path('app/public/' . $relativePath);
        }

        if (str_starts_with($url, 'storage/')) {
            $relativePath = substr($url, strlen('storage/'));
            return storage_path('app/public/' . $relativePath);
        }

        return null;
    }

    /**
     * Ensure the media URL is a fully qualified URL for remote downloads.
     */
    protected static function formatMediaUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    /**
     * Check if a given URL is a video file.
     */
    protected static function isVideoFile(string $url): bool
    {
        $clean = strtolower(strtok($url, '?'));
        return str_ends_with($clean, '.mp4') ||
               str_ends_with($clean, '.mov') ||
               str_ends_with($clean, '.webm') ||
               str_ends_with($clean, '.mkv');
    }
}
