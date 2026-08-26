<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeMediaService
{
    /**
     * Download a media file from a URL to local storage.
     * Mirrors the reference download_media logic from autoaff backend/services/shopee_media.py.
     */
    public static function downloadMedia(string $url, string $postId, int $index): ?string
    {
        $rawExt = strtolower(substr(strtok(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION), '?'), 0, 5));
        $ext = in_array($rawExt, ['mp4', 'mov', 'webm', 'm3u8', 'png', 'webp', 'gif', 'jpeg', 'jpg']) ? $rawExt : null;

        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Referer' => 'https://shopee.ph/',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])
            ->timeout(25)
            ->withoutVerifying()
            ->get($url);

            if (! $resp->successful() || strlen($resp->body()) <= 500) {
                return null;
            }

            $body = $resp->body();

            // Detect extension from Content-Type if URL didn't provide it
            if (! $ext) {
                $ct = strtolower($resp->header('Content-Type', ''));
                if (str_contains($ct, 'png')) {
                    $ext = 'png';
                } elseif (str_contains($ct, 'webp')) {
                    $ext = 'webp';
                } elseif (str_contains($ct, 'gif')) {
                    $ext = 'gif';
                } elseif (str_contains($ct, 'jpeg') || str_contains($ct, 'jpg')) {
                    $ext = 'jpg';
                } elseif (str_contains($ct, 'mp4')) {
                    $ext = 'mp4';
                } elseif (str_contains($ct, 'webm')) {
                    $ext = 'webm';
                } else {
                    // Magic byte probe
                    $magic = substr($body, 0, 12);
                    if (str_starts_with($magic, "\x89PNG")) {
                        $ext = 'png';
                    } elseif (str_starts_with($magic, "\xff\xd8\xff")) {
                        $ext = 'jpg';
                    } elseif (str_starts_with($magic, 'RIFF') && substr($magic, 8, 4) === 'WEBP') {
                        $ext = 'webp';
                    } elseif (str_starts_with($magic, 'GIF')) {
                        $ext = 'gif';
                    } elseif (substr($magic, 4, 4) === 'ftyp') {
                        $ext = 'mp4';
                    } else {
                        $ext = 'jpg';
                    }
                }
            }

            $indexFormatted = sprintf('%02d', $index);
            $filename = "{$postId}_{$indexFormatted}.{$ext}";
            $mediaDir = storage_path('app/public/media');
            if (! is_dir($mediaDir)) {
                mkdir($mediaDir, 0755, true);
            }

            $localPath = "{$mediaDir}/{$filename}";
            file_put_contents($localPath, $body);

            // Validate image dimensions & aspect ratio (drop tiny icons < 300px, badges, and wide banners)
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && file_exists($localPath)) {
                $info = @getimagesize($localPath);
                if ($info && isset($info[0], $info[1])) {
                    $width = (int) $info[0];
                    $height = (int) $info[1];

                    // 1. Drop tiny icons, rating stars, badges (< 300px)
                    if ($width < 300 || $height < 300) {
                        @unlink($localPath);
                        return null;
                    }

                    // 2. Drop extreme aspect ratio banners or promotional strips (> 2.2:1)
                    if ((max($width, $height) / max(1, min($width, $height))) > 2.2) {
                        @unlink($localPath);
                        return null;
                    }

                    // 3. Drop transparent SPayLater frames and campaign cutouts (PNG with transparent center)
                    if ($ext === 'png' && function_exists('imagecreatefromstring')) {
                        $im = @imagecreatefromstring($body);
                        if ($im) {
                            $centerX = (int) ($width / 2);
                            $centerY = (int) ($height / 2);
                            $rgba = imagecolorat($im, $centerX, $centerY);
                            $alpha = ($rgba >> 24) & 0x7F;
                            imagedestroy($im);

                            if ($alpha > 90) {
                                @unlink($localPath);
                                return null;
                            }
                        }
                    }
                }
            }

            return "/storage/media/{$filename}";
        } catch (\Exception $e) {
            Log::warning("[ShopeeMediaService] Failed downloading media from {$url}: " . $e->getMessage());
            return null;
        }
    }
}
