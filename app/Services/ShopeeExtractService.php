<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopeeExtractService
{
    /**
     * Extract product metadata (title, description, price, media images, shop) from a Shopee PH URL.
     */
    public static function extract(string $url): array
    {
        $url = trim($url);
        $title = null;
        $description = null;
        $price = null;
        $mediaFiles = [];
        $seenMedia = [];
        $canonicalUrl = null;
        $shopName = null;

        // 1. Resolve shop_id and item_id if present to build canonical URL
        $ids = self::extractShopeeIds($url);
        if ($ids['shop_id'] && $ids['item_id']) {
            $canonicalUrl = "https://shopee.ph/product/{$ids['shop_id']}/{$ids['item_id']}";
        }

        $targetUrl = $canonicalUrl ?: $url;

        $userAgents = [
            'facebook' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.html)',
            'twitter' => 'Twitterbot/1.0',
            'android' => 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
            'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        foreach ($userAgents as $uaKey => $ua) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $ua,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9,fil;q=0.8',
                ])
                ->timeout(15)
                ->withoutVerifying()
                ->get($targetUrl);

                if (! $response->successful()) {
                    continue;
                }

                $html = $response->body();
                $effectiveUrl = (string) $response->effectiveUri();
                if ($effectiveUrl && ! $canonicalUrl) {
                    $canonicalUrl = $effectiveUrl;
                }

                // 1. Title from OG meta or <title>
                if (! $title) {
                    $ogTitle = self::extractMetaTag($html, 'og:title') ?: self::extractMetaTag($html, 'twitter:title');
                    if ($ogTitle) {
                        $title = self::cleanTitle($ogTitle);
                    } else if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                        $title = self::cleanTitle($m[1]);
                    }
                }

                // 2. Description from OG meta or meta description
                if (! $description) {
                    $ogDesc = self::extractMetaTag($html, 'og:description') ?: self::extractMetaByName($html, 'description');
                    if ($ogDesc) {
                        $description = self::cleanDescription($ogDesc);
                    }
                }

                // 3. Price from OG or micro-units in JSON state
                if (! $price) {
                    $pricesFound = [];

                    $ogPrice = self::extractMetaTag($html, 'product:price:amount') ?: self::extractMetaTag($html, 'og:price:amount');
                    if ($ogPrice && is_numeric($ogPrice) && (float)$ogPrice > 0) {
                        $pricesFound[] = (float)$ogPrice;
                    }

                    // Regex for ₱ or PHP patterns
                    foreach ([$title, $description, $html] as $src) {
                        if (! $src) continue;
                        if (preg_match_all('/(?:₱|PHP)\s*([\d,]+(?:\.\d{2})?)/i', $src, $pMatches)) {
                            foreach ($pMatches[1] as $pStr) {
                                $num = (float) str_replace(',', '', $pStr);
                                if ($num >= 1 && $num <= 1000000) {
                                    $pricesFound[] = $num;
                                }
                            }
                        }
                    }

                    // Micro-units in JSON state (e.g. 29900000 -> 299)
                    if (preg_match_all('/"(?:price_min|price|price_min_before_discount)"\s*:\s*(\d+)/i', $html, $rawPrices)) {
                        foreach ($rawPrices[1] as $rVal) {
                            $rNum = (int)$rVal;
                            if ($rNum > 0) {
                                $val = $rNum > 100000 ? ($rNum / 100000.0) : (float)$rNum;
                                if ($val >= 1 && $val <= 1000000) {
                                    $pricesFound[] = $val;
                                }
                            }
                        }
                    }

                    if (! empty($pricesFound)) {
                        $dealPrice = min($pricesFound);
                        $price = '₱' . number_format($dealPrice, $dealPrice == (int)$dealPrice ? 0 : 2);
                    }
                }

                // 4. Media Extraction:
                // a. OG images (exact product cover photos)
                foreach (['og:square_image', 'og:image', 'og:image:secure_url', 'twitter:image'] as $prop) {
                    $ogImg = self::extractMetaTag($html, $prop);
                    if ($ogImg && self::isValidProductPhoto($ogImg) && ! isset($seenMedia[$ogImg])) {
                        $seenMedia[$ogImg] = true;
                        $mediaFiles[] = $ogImg;
                    }
                }

                // b. Product-specific regional CDN hashes (ph-*, cn-*, sg-*, id-*, my-*, etc.)
                if (preg_match_all('/(?:cn|ph|sg|id|my|vn|th|br|mx|tw)-\d{8,10}-[a-z0-9\-_]+/i', $html, $regMatches)) {
                    foreach ($regMatches[0] as $h) {
                        $imgUrl = "https://down-ph.img.susercontent.com/file/{$h}";
                        if (self::isValidProductPhoto($imgUrl) && ! isset($seenMedia[$imgUrl]) && count($mediaFiles) < 16) {
                            $seenMedia[$imgUrl] = true;
                            $mediaFiles[] = $imgUrl;
                        }
                    }
                }

                // c. 32-char hex image hashes in JSON gallery
                if (preg_match_all('/"(?:images?|cover|tier_images)":\s*(?:\[\s*)?"([a-f0-9]{32})"/i', $html, $hexMatches)) {
                    foreach ($hexMatches[1] as $h) {
                        $imgUrl = "https://down-ph.img.susercontent.com/file/{$h}";
                        if (self::isValidProductPhoto($imgUrl) && ! isset($seenMedia[$imgUrl]) && count($mediaFiles) < 16) {
                            $seenMedia[$imgUrl] = true;
                            $mediaFiles[] = $imgUrl;
                        }
                    }
                }

                // d. Shopee CDN full URLs
                if (preg_match_all('/https:\/\/[a-zA-Z0-9\.\-]*susercontent\.com\/file\/([a-zA-Z0-9_\-]+)/i', $html, $imMatches)) {
                    foreach ($imMatches[0] as $imgUrl) {
                        if (self::isValidProductPhoto($imgUrl) && ! isset($seenMedia[$imgUrl]) && count($mediaFiles) < 16) {
                            $seenMedia[$imgUrl] = true;
                            $mediaFiles[] = $imgUrl;
                        }
                    }
                }

                // 5. Shop Name
                if (! $shopName) {
                    $shopName = self::extractMetaTag($html, 'og:site_name');
                }

                if ($title && count($mediaFiles) >= 4) {
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("[ShopeeExtract] Scrape attempt with UA {$uaKey} failed: " . $e->getMessage());
            }
        }

        // Fallback: If still no media and we have shortlink, do a follow-redirect GET
        if (empty($mediaFiles) && (str_contains($url, 's.shopee.ph') || str_contains($url, 'shope.ee'))) {
            try {
                $headResp = Http::withHeaders(['User-Agent' => $userAgents['facebook']])
                    ->withoutVerifying()
                    ->get($url);
                $html = $headResp->body();
                foreach (['og:square_image', 'og:image'] as $prop) {
                    $img = self::extractMetaTag($html, $prop);
                    if ($img && self::isValidProductPhoto($img) && ! in_array($img, $mediaFiles)) {
                        $mediaFiles[] = $img;
                    }
                }
            } catch (\Exception $e) {}
        }

        return [
            'success' => ! empty($title) || ! empty($mediaFiles),
            'product_title' => $title ?: 'Shopee Sulit Deal',
            'product_description' => $description ?: '',
            'product_price' => $price ?: '',
            'shop_name' => $shopName ?: 'Shopee Philippines',
            'affiliate_url' => $url,
            'canonical_url' => $canonicalUrl ?: $url,
            'media_files' => array_values($mediaFiles),
        ];
    }

    /**
     * Determine if a media URL is a valid product photograph, stripping out SPayLater frames,
     * promotional borders, badges, vouchers, and transparent PNG overlays.
     */
    public static function isValidProductPhoto(string $url): bool
    {
        $urlLower = strtolower($url);

        // 1. Exclude known frame and overlay keywords
        $blacklistedKeywords = [
            'spaylater',
            'spay_later',
            'frame',
            'overlay',
            'border',
            'watermark',
            'voucher',
            'badge',
            'banner',
            'campaign_frame',
            'promo_frame',
            'cutout',
            'layer',
            'icon',
            'logo',
            'shopee_mall_badge',
        ];

        foreach ($blacklistedKeywords as $kw) {
            if (str_contains($urlLower, $kw)) {
                return false;
            }
        }

        // 2. Reject non-image or placeholder assets
        if (str_starts_with($url, 'data:image') || strlen($url) < 15) {
            return false;
        }

        // 3. Optional deep inspection for transparent PNG / WebP cutouts (if GD is available)
        if (extension_loaded('gd')) {
            try {
                // If the URL ends with .png, perform a lightweight check to reject transparent cutout frames
                if (str_ends_with($urlLower, '.png')) {
                    $imgData = @file_get_contents($url, false, stream_context_create([
                        'http' => ['timeout' => 3, 'user_agent' => 'Mozilla/5.0'],
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                    ]));

                    if ($imgData && strlen($imgData) > 500) {
                        $im = @imagecreatefromstring($imgData);
                        if ($im) {
                            $w = imagesx($im);
                            $h = imagesy($im);

                            // Reject tiny icons (< 250px) or banner strips (aspect ratio > 2.2)
                            if ($w < 250 || $h < 250 || (max($w, $h) / max(1, min($w, $h)) > 2.2)) {
                                imagedestroy($im);
                                return false;
                            }

                            // Probe center pixel for alpha transparency (hollow center frames like SPayLater)
                            $centerX = (int)($w / 2);
                            $centerY = (int)($h / 2);
                            $rgba = imagecolorat($im, $centerX, $centerY);
                            $alpha = ($rgba >> 24) & 0x7F; // In GD: 0 = opaque, 127 = fully transparent

                            imagedestroy($im);

                            if ($alpha > 90) {
                                // Center is transparent -> this is a hollow border/frame!
                                return false;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Keep image if probe check fails safely
            }
        }

        return true;
    }

    /**
     * Extract shop_id and item_id from any Shopee URL format or follow shortlink.
     */
    public static function extractShopeeIds(string $url): array
    {
        $shopId = null;
        $itemId = null;

        if (preg_match('#(?:i\.|product/|[-/])(\d+)[./-](\d+)#', $url, $m)) {
            return ['shop_id' => $m[1], 'item_id' => $m[2]];
        }

        // Shortlink redirect resolution
        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->timeout(10)
            ->withoutVerifying()
            ->get($url);

            $effUrl = (string) $resp->effectiveUri();
            if ($effUrl && preg_match('#(?:i\.|product/|[-/])(\d+)[./-](\d+)#', $effUrl, $m2)) {
                return ['shop_id' => $m2[1], 'item_id' => $m2[2]];
            }
        } catch (\Exception $e) {}

        return ['shop_id' => null, 'item_id' => null];
    }

    private static function extractMetaTag(string $html, string $property): ?string
    {
        $pattern1 = '/<meta[^>]*property=["\']' . self::preg_escape($property) . '["\'][^>]*content=["\']([^"\']*)["\']/i';
        $pattern2 = '/<meta[^>]*content=["\']([^"\']*)["\'][^>]*property=["\']' . self::preg_escape($property) . '["\']/i';

        if (preg_match($pattern1, $html, $m) || preg_match($pattern2, $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    private static function extractMetaByName(string $html, string $name): ?string
    {
        $pattern1 = '/<meta[^>]*name=["\']' . self::preg_escape($name) . '["\'][^>]*content=["\']([^"\']*)["\']/i';
        $pattern2 = '/<meta[^>]*content=["\']([^"\']*)["\'][^>]*name=["\']' . self::preg_escape($name) . '["\']/i';

        if (preg_match($pattern1, $html, $m) || preg_match($pattern2, $html, $m)) {
            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    private static function cleanTitle(string $title): string
    {
        $cleaned = preg_replace('/\s*\|\s*Shopee\s*(?:Philippines|PH)?\s*$/i', '', $title);
        $cleaned = preg_replace('/^Buy\s+/i', '', $cleaned);
        $cleaned = preg_replace('/\s*online at best price.*$/i', '', $cleaned);
        return trim($cleaned);
    }

    private static function cleanDescription(string $desc): string
    {
        $cleaned = preg_replace('/^Buy\s+.*?\s+online today!\s*/i', '', $desc);
        $cleaned = preg_replace('/\s*-\s*Enjoy best prices with free shipping vouchers\.?$/i', '', $cleaned);
        return trim($cleaned);
    }

    private static function preg_escape(string $str): string
    {
        return preg_quote($str, '/');
    }
}
