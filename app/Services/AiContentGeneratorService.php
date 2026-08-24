<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentGeneratorService
{
    /**
     * Generate dynamic, unique post content using configured AI Provider or Rich Dynamic Template Engine.
     */
    public static function generate(array $params): array
    {
        $name = $params['name'] ?? 'Community Post';
        $targetPage = $params['target_page'] ?? 'Tech Sulit Deals';
        $category = $params['category'] ?? 'Connection & Community';
        $tones = $params['tones'] ?? ['taglish'];
        $personas = $params['personas'] ?? [];
        $generalContext = $params['general_context'] ?? '';
        $weatherContext = $params['weather_context'] ?? '';
        $occasionContext = $params['occasion_context'] ?? '';
        $manualPrompt = $params['manual_prompt'] ?? '';
        $shopeeUrl = $params['shopee_url'] ?? 'https://shopee.ph';

        $now = now()->timezone('Asia/Manila');
        $hour = $now->hour;
        $dayName = $now->format('l');
        $timeSlot = ($hour >= 5 && $hour < 12) ? 'morning' : (($hour >= 12 && $hour < 18) ? 'afternoon' : 'evening');
        $timeTag = ($hour >= 5 && $hour < 12) ? 'Morning ☕' : (($hour >= 12 && $hour < 18) ? 'Afternoon ☀️' : 'Evening 🌙');

        $defaultTags = Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH #BudolFinds');
        $hasAffiliateLink = (! empty($shopeeUrl) && $shopeeUrl !== 'https://shopee.ph' && ! str_starts_with($shopeeUrl, 'https://facebook.com')) ||
            str_contains($manualPrompt, 'http://') || str_contains($manualPrompt, 'https://');
        $disclosure = $hasAffiliateLink ? Setting::get('disclosure', 'Affiliate link. Price and availability may change anytime.') : '';

        // Check if user has configured AI API key
        $apiKey = Setting::get('ai_api_key');
        $provider = Setting::get('ai_provider', 'openai');
        $model = Setting::get('ai_model', 'gpt-4o-mini');
        $systemPrompt = Setting::get('ai_system_prompt', 'You are an engaging, friendly Filipino community manager and affiliate creator. Write viral Taglish/English Facebook community engagement posts.');

        if (! empty($apiKey)) {
            $aiResult = self::callAiApi($provider, $apiKey, $model, $systemPrompt, [
                'name' => $name,
                'target_page' => $targetPage,
                'category' => $category,
                'time_slot' => $timeSlot,
                'day_name' => $dayName,
                'tones' => $tones,
                'personas' => $personas,
                'general_context' => $generalContext,
                'weather_context' => $weatherContext,
                'occasion_context' => $occasionContext,
                'manual_prompt' => $manualPrompt,
                'shopee_url' => $shopeeUrl,
            ]);

            if ($aiResult['success']) {
                $caption = trim(
                    $aiResult['caption'].
                    ($disclosure ? "\n\n".$disclosure : '').
                    ($defaultTags ? "\n\n".$defaultTags : '')
                );

                return [
                    'title' => $aiResult['title'] ?: "✨ {$timeTag} Community Lounge · {$name}",
                    'caption' => $caption,
                    'prompt_tokens' => $aiResult['prompt_tokens'],
                    'completion_tokens' => $aiResult['completion_tokens'],
                    'total_tokens' => $aiResult['total_tokens'],
                    'is_live_ai' => true,
                ];
            }
        }

        // Fallback: Use Diverse Randomized Dynamic Template Engine
        return self::generateDynamicFallback([
            'name' => $name,
            'target_page' => $targetPage,
            'category' => $category,
            'time_slot' => $timeSlot,
            'time_tag' => $timeTag,
            'day_name' => $dayName,
            'tones' => $tones,
            'general_context' => $generalContext,
            'weather_context' => $weatherContext,
            'occasion_context' => $occasionContext,
            'manual_prompt' => $manualPrompt,
            'disclosure' => $disclosure,
            'default_tags' => $defaultTags,
        ]);
    }

    /**
     * Call Live AI APIs (OpenAI, Gemini, Groq, OpenRouter, Anthropic)
     */
    protected static function callAiApi(string $provider, string $apiKey, string $model, string $systemPrompt, array $ctx): array
    {
        $userPrompt = "Generate a fresh, engaging Facebook Page post for {$ctx['target_page']}.\n".
                      "Category: {$ctx['category']}\n".
                      "Current Manila Time Slot: {$ctx['time_slot']} ({$ctx['day_name']})\n".
                      "Tone: ".implode(', ', (array) $ctx['tones'])."\n".
                      (! empty($ctx['general_context']) ? "Topic Context: {$ctx['general_context']}\n" : '').
                      (! empty($ctx['weather_context']) ? "Weather Note: {$ctx['weather_context']}\n" : '').
                      (! empty($ctx['occasion_context']) ? "Special Occasion: {$ctx['occasion_context']}\n" : '').
                      (! empty($ctx['manual_prompt']) ? "Custom User Instructions: {$ctx['manual_prompt']}\n" : '').
                      "Rules: Include relevant emojis, interactive question/poll to drive comments, keep it authentic, natural Taglish or English depending on tone. Do NOT output markdown code blocks or quotes around the whole text.";

        try {
            if ($provider === 'openai' || $provider === 'groq' || $provider === 'openrouter') {
                $endpoint = match ($provider) {
                    'groq' => 'https://api.groq.com/openai/v1/chat/completions',
                    'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
                    default => 'https://api.openai.com/v1/chat/completions',
                };

                $resp = Http::timeout(30)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'model' => $model ?: 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.85,
                ]);

                if ($resp->successful()) {
                    $data = $resp->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    $usage = $data['usage'] ?? [];

                    return [
                        'success' => true,
                        'title' => "✨ {$ctx['name']}",
                        'caption' => trim($content),
                        'prompt_tokens' => $usage['prompt_tokens'] ?? 60,
                        'completion_tokens' => $usage['completion_tokens'] ?? 120,
                        'total_tokens' => $usage['total_tokens'] ?? 180,
                    ];
                }
            } elseif ($provider === 'gemini') {
                $geminiModel = $model ?: 'gemini-1.5-flash';
                $resp = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => "{$systemPrompt}\n\n{$userPrompt}"],
                            ],
                        ],
                    ],
                ]);

                if ($resp->successful()) {
                    $data = $resp->json();
                    $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $meta = $data['usageMetadata'] ?? [];

                    return [
                        'success' => true,
                        'title' => "✨ {$ctx['name']}",
                        'caption' => trim($content),
                        'prompt_tokens' => $meta['promptTokenCount'] ?? 70,
                        'completion_tokens' => $meta['candidatesTokenCount'] ?? 130,
                        'total_tokens' => $meta['totalTokenCount'] ?? 200,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("[AiContentGenerator] Live AI call failed: {$e->getMessage()}. Falling back to dynamic engine.");
        }

        return ['success' => false];
    }

    /**
     * Rich Dynamic Fallback Template Engine with randomized variations
     */
    protected static function generateDynamicFallback(array $ctx): array
    {
        $dayName = $ctx['day_name'];
        $targetPage = $ctx['target_page'];
        $timeSlot = $ctx['time_slot'];
        $timeTag = $ctx['time_tag'];
        $name = $ctx['name'];

        $morningOpeners = [
            "Magandang umaga mga ka-Tech Sulit! ☕✨ Sana ready kayo for a productive {$dayName}!",
            "Good morning {$targetPage} family! ☀️ Kumusta ang tulog nyo mga idol?",
            "Rise and shine {$targetPage} squad! ☕ Unang higop ng kape sabay check ng daily goals!",
            "Happy {$dayName} morning everyone! 🌅 Simulan natin ang araw with positive energy and good vibes!",
            "Morning check-in for the {$targetPage} community! ☕ Kape muna bago sumabak sa work!",
        ];

        $morningQuestions = [
            "Quick question for today: Ano ang #1 tech accessory or daily habit na hindi pwede mawala sa routine nyo?\n\nDrop your setup or thoughts below! 👇",
            "Ano ang daily workspace setup nyo today: WFH ba or on-site grind?\n\nShare your workstation vibes in the comments! 💻👇",
            "If you could upgrade ONE item on your desk today for free, ano ang pipiliin nyo?\n\nComment your dream upgrade! 🚀👇",
            "Sino dito ang 1st cup of coffee pa lang vs 3rd cup na agad? ☕😂\n\nKamusta ang morning grind nyo mga besh? 👇",
            "Anong soundtrack or podcast ang nagpapatakbo ng productive {$dayName} nyo today?\n\nDrop your song recommendations! 🎧👇",
        ];

        $afternoonOpeners = [
            "Happy afternoon {$targetPage} family! ☀️ Kumain na ba kayo ng masarap na lunch?",
            "Good afternoon mga ka-Tech Sulit! 🍱 Surviving the midday grind this {$dayName}!",
            "Midday check-in with the {$targetPage} community! ⚡ Kumusta ang workload nyo today?",
            "Happy {$dayName} afternoon! ☀️ Quick break muna mula sa spreadsheets at deadlines!",
        ];

        $afternoonQuestions = [
            "Ano ang lunch nyo today, and ano ang pinaka-sulit na budol find nyo this week?\n\nShare your budol stories below! 🛍️👇",
            "Work from home ba kayo or office setup today? Pa-tingin naman ng coffee or tea buddy nyo! ☕👇",
            "Quick poll for tech lovers: Mechanical Keyboard vs Slim Silent Membrane for office work?\n\nVote in the comments! ⌨️👇",
            "May paparating na bang parcels today o naghihintay pa sa susunod na Payday Sale? 📦😂\n\nComment down below! 👇",
        ];

        $eveningOpeners = [
            "Good evening everyone! 🌙 Time to unwind and relax after a productive {$dayName}! ✨",
            "Magandang gabi {$targetPage} family! 🛋️ Tapos na ba ang shift or extend pa sa night grind?",
            "Evening lounge time with {$targetPage}! 🌌 Congrats on getting through {$dayName}!",
            "Happy evening mga ka-Sulit! 🌙 Rest mode activated or gaming night session?",
            "Late night check-in! 🌃 Kamusta ang naging takbo ng {$dayName} nyo mga ka-Tech?",
        ];

        $eveningQuestions = [
            "Ano ang pinaka-sulit na tech, gadget, or budol find nyo recently na talagang 10/10 recommend?\n\nI-flex nyo na sa comments below! 🛒👇",
            "Netflix chill, bedtime scroll, or PC/Mobile gaming session for tonight?\n\nAno ang relaxing routine nyo? 🎮📺👇",
            "Quick question bago matulog: What's one tech purchase you bought under ₱500 that exceeded expectations?\n\nShare your best sulit gems! 💎👇",
            "Rate your {$dayName} from 1 to 10! ✨ And ano ang nilu-look forward nyo for tomorrow?\n\nDrop your thoughts below! 👇",
            "Desk setup tour check: RGB lights ON or Warm cozy night lights?\n\nShare your evening workspace vibe! 💡👇",
        ];

        if ($timeSlot === 'morning') {
            $opener = $morningOpeners[array_rand($morningOpeners)];
            $question = $morningQuestions[array_rand($morningQuestions)];
        } elseif ($timeSlot === 'afternoon') {
            $opener = $afternoonOpeners[array_rand($afternoonOpeners)];
            $question = $afternoonQuestions[array_rand($afternoonQuestions)];
        } else {
            $opener = $eveningOpeners[array_rand($eveningOpeners)];
            $question = $eveningQuestions[array_rand($eveningQuestions)];
        }

        $captionBody = "{$opener}\n\n{$question}";

        if (! empty($ctx['general_context'])) {
            $captionBody .= "\n\n📌 Spotlight: ".$ctx['general_context'];
        }

        if (! empty($ctx['weather_context'])) {
            $captionBody .= "\n\n🌤️ Weather Note: ".$ctx['weather_context'];
        }

        if (! empty($ctx['occasion_context'])) {
            $captionBody .= "\n\n🎉 Special Feature: ".$ctx['occasion_context'];
        }

        if (! empty($ctx['manual_prompt'])) {
            $captionBody .= "\n\n".$ctx['manual_prompt'];
        }

        $finalCaption = trim(
            $captionBody.
            ($ctx['disclosure'] ? "\n\n".$ctx['disclosure'] : '').
            ($ctx['default_tags'] ? "\n\n".$ctx['default_tags'] : '')
        );

        $promptTokens = max(35, (int) (str_word_count($captionBody) * 1.35));
        $completionTokens = max(55, (int) (str_word_count($finalCaption) * 1.35));

        return [
            'title' => "✨ {$timeTag} Community Lounge · {$name}",
            'caption' => $finalCaption,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'is_live_ai' => false,
        ];
    }
}
