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
        $canonicalUrl = $url;
        $shopName = null;

        $userAgents = [
            'facebook' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.html)',
            'twitter' => 'Twitterbot/1.0',
            'desktop' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ];

        foreach ($userAgents as $ua) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $ua,
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9,fil;q=0.8',
                ])
                ->timeout(15)
                ->withoutVerifying()
                ->get($url);

                if (! $response->successful()) {
                    continue;
                }

                $html = $response->body();
                $effectiveUrl = (string) $response->effectiveUri();
                if ($effectiveUrl) {
                    $canonicalUrl = $effectiveUrl;
                }

                // 1. Extract Title
                if (! $title) {
                    $ogTitle = self::extractMetaTag($html, 'og:title') ?: self::extractMetaTag($html, 'twitter:title');
                    if ($ogTitle) {
                        $title = self::cleanTitle($ogTitle);
                    } else if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                        $title = self::cleanTitle($m[1]);
                    }
                }

                // 2. Extract Description
                if (! $description) {
                    $ogDesc = self::extractMetaTag($html, 'og:description') ?: self::extractMetaByName($html, 'description');
                    if ($ogDesc) {
                        $description = self::cleanDescription($ogDesc);
                    }
                }

                // 3. Extract Price
                if (! $price) {
                    $ogPrice = self::extractMetaTag($html, 'product:price:amount') ?: self::extractMetaTag($html, 'og:price:amount');
                    if ($ogPrice && is_numeric($ogPrice) && (float)$ogPrice > 0) {
                        $price = '₱' . number_format((float)$ogPrice, 2);
                    } else if (preg_match('/(?:₱|PHP)\s*([\d,]+(?:\.\d{2})?)/i', $html, $pm)) {
                        $price = '₱' . $pm[1];
                    }
                }

                // 4. Extract Images / Media
                $ogImage = self::extractMetaTag($html, 'og:image') ?: self::extractMetaTag($html, 'og:image:secure_url');
                if ($ogImage && ! in_array($ogImage, $mediaFiles)) {
                    $mediaFiles[] = $ogImage;
                }

                // Check for Shopee CDN image hashes (e.g. https://down-ph.img.susercontent.com/file/...)
                if (preg_match_all('/https:\/\/[a-zA-Z0-9\.\-]*susercontent\.com\/file\/([a-zA-Z0-9_\-]+)/i', $html, $imMatches)) {
                    foreach ($imMatches[0] as $imgUrl) {
                        if (! in_array($imgUrl, $mediaFiles) && count($mediaFiles) < 8) {
                            $mediaFiles[] = $imgUrl;
                        }
                    }
                }

                // 5. Shop Name
                if (! $shopName) {
                    $shopName = self::extractMetaTag($html, 'og:site_name');
                }

                if ($title && count($mediaFiles) > 0) {
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("[ShopeeExtract] Scrape attempt with UA {$ua} failed: " . $e->getMessage());
            }
        }

        return [
            'success' => ! empty($title) || ! empty($mediaFiles),
            'product_title' => $title ?: 'Shopee Sulit Deal',
            'product_description' => $description ?: '',
            'product_price' => $price ?: '',
            'shop_name' => $shopName ?: 'Shopee Philippines',
            'affiliate_url' => $url,
            'canonical_url' => $canonicalUrl,
            'media_files' => $mediaFiles,
        ];
    }

    private static function extractMetaTag(string $html, string $property): ?string
    {
        // Support property before content AND content before property
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
