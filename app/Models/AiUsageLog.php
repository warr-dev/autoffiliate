<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiUsageLog extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'timestamp',
        'post_id',
        'provider',
        'model',
        'style',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'estimated_cost',
        'execution_time_ms',
        'source',
        'status',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'estimated_cost' => 'float',
        'execution_time_ms' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Calculate estimated cost based on live AI provider rates per million tokens.
     */
    public static function calculateCost(
        string $provider,
        string $model,
        int $promptTokens,
        int $completionTokens,
        bool $isLiveAi = true
    ): float {
        if (! $isLiveAi || strtolower($provider) === 'internal') {
            return 0.0;
        }

        $pRate = 0.00000020;
        $cRate = 0.00000050;

        $provLower = strtolower($provider);
        $modLower = strtolower($model);

        if (str_contains($provLower, 'deepseek')) {
            if (str_contains($modLower, 'reasoner') || str_contains($modLower, 'r1')) {
                $pRate = 0.00000055;
                $cRate = 0.00000219;
            } else {
                $pRate = 0.00000014;
                $cRate = 0.00000028;
            }
        } elseif (str_contains($provLower, 'openai')) {
            if (str_contains($modLower, 'gpt-4o-mini')) {
                $pRate = 0.00000015;
                $cRate = 0.00000060;
            } elseif (str_contains($modLower, 'gpt-4o')) {
                $pRate = 0.00000250;
                $cRate = 0.00001000;
            } elseif (str_contains($modLower, 'o1') || str_contains($modLower, 'o3')) {
                $pRate = 0.00000110;
                $cRate = 0.00000440;
            } elseif (str_contains($modLower, 'gpt-4')) {
                $pRate = 0.00001000;
                $cRate = 0.00003000;
            } elseif (str_contains($modLower, 'gpt-3.5')) {
                $pRate = 0.00000050;
                $cRate = 0.00000150;
            } else {
                $pRate = 0.00000015;
                $cRate = 0.00000060;
            }
        } elseif (str_contains($provLower, 'gemini')) {
            if (str_contains($modLower, 'pro')) {
                $pRate = 0.00000125;
                $cRate = 0.00000500;
            } else {
                $pRate = 0.000000075;
                $cRate = 0.00000030;
            }
        } elseif (str_contains($provLower, 'anthropic') || str_contains($provLower, 'claude')) {
            if (str_contains($modLower, 'sonnet') || str_contains($modLower, 'opus')) {
                $pRate = 0.00000300;
                $cRate = 0.00001500;
            } else {
                $pRate = 0.00000080;
                $cRate = 0.00000400;
            }
        } elseif (str_contains($provLower, 'groq')) {
            if (str_contains($modLower, '70b')) {
                $pRate = 0.00000059;
                $cRate = 0.00000079;
            } elseif (str_contains($modLower, '8b')) {
                $pRate = 0.00000005;
                $cRate = 0.00000008;
            } else {
                $pRate = 0.00000020;
                $cRate = 0.00000040;
            }
        }

        $estimatedCost = ($promptTokens * $pRate) + ($completionTokens * $cRate);

        return round($estimatedCost, 6);
    }

    /**
     * Record an AI generation run in the log.
     */
    public static function logUsage(
        ?string $postId,
        string $provider,
        string $model,
        string $style,
        int $promptTokens,
        int $completionTokens,
        int $totalTokens,
        ?int $executionTimeMs = null,
        string $source = 'manual',
        string $status = 'success',
        bool $isLiveAi = true
    ): self {
        $estimatedCost = self::calculateCost($provider, $model, $promptTokens, $completionTokens, $isLiveAi);

        return self::create([
            'id' => 'ai_'.Str::random(12),
            'timestamp' => now(),
            'post_id' => $postId,
            'provider' => $provider,
            'model' => $model,
            'style' => $style,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'estimated_cost' => $estimatedCost,
            'execution_time_ms' => $executionTimeMs,
            'source' => $source,
            'status' => $status,
        ]);
    }

    /**
     * Build rich aggregated analytics payload based on date range and filters.
     */
    public static function getAnalytics(array $filters = []): array
    {
        $period = $filters['period'] ?? 'all';
        $provider = $filters['provider'] ?? null;
        $style = $filters['style'] ?? null;
        $source = $filters['source'] ?? null;

        $query = self::query();

        // Apply period filter
        $now = Carbon::now('Asia/Manila');
        $startDate = match ($period) {
            'today' => $now->copy()->startOfDay(),
            '7d', '7days' => $now->copy()->subDays(7)->startOfDay(),
            '30d', '30days' => $now->copy()->subDays(30)->startOfDay(),
            '90d', '90days' => $now->copy()->subDays(90)->startOfDay(),
            default => null,
        };

        if ($startDate) {
            $query->where('timestamp', '>=', $startDate);
        }

        if (! empty($provider) && $provider !== 'all') {
            $query->where('provider', $provider);
        }

        if (! empty($style) && $style !== 'all') {
            $query->where('style', $style);
        }

        if (! empty($source) && $source !== 'all') {
            $query->where('source', $source);
        }

        $totalGenerations = (clone $query)->count();
        $promptTokensTotal = (int) (clone $query)->sum('prompt_tokens');
        $completionTokensTotal = (int) (clone $query)->sum('completion_tokens');
        $totalTokensUsed = (int) (clone $query)->sum('total_tokens');
        $estimatedCostUsd = (float) (clone $query)->sum('estimated_cost');
        $avgExecutionTimeMs = (float) ((clone $query)->whereNotNull('execution_time_ms')->avg('execution_time_ms') ?? 0);

        // Daily / timeline series data (last 7, 14, or 30 days)
        $timelineDays = match ($period) {
            'today' => 1,
            '7d', '7days' => 7,
            '30d', '30days' => 30,
            default => 14,
        };

        $timelineStart = $now->copy()->subDays($timelineDays - 1)->startOfDay();
        $timelineLogs = (clone $query)
            ->where('timestamp', '>=', $timelineStart)
            ->get();

        $timeline = [];
        for ($i = 0; $i < $timelineDays; $i++) {
            $date = $timelineStart->copy()->addDays($i)->format('Y-m-d');
            $dateLabel = $timelineStart->copy()->addDays($i)->format('M d');
            
            $dayLogs = $timelineLogs->filter(function ($l) use ($date) {
                return $l->timestamp && $l->timestamp->format('Y-m-d') === $date;
            });

            $timeline[] = [
                'date' => $date,
                'label' => $dateLabel,
                'count' => $dayLogs->count(),
                'tokens' => (int) $dayLogs->sum('total_tokens'),
                'prompt_tokens' => (int) $dayLogs->sum('prompt_tokens'),
                'completion_tokens' => (int) $dayLogs->sum('completion_tokens'),
                'cost' => round((float) $dayLogs->sum('estimated_cost'), 6),
            ];
        }

        // Provider breakdown
        $byProvider = (clone $query)
            ->select('provider', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'), DB::raw('sum(estimated_cost) as total_cost'))
            ->groupBy('provider')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($totalGenerations) {
                return [
                    'provider' => $item->provider,
                    'count' => (int) $item->count,
                    'total_tokens' => (int) $item->total_tokens,
                    'total_cost' => round((float) $item->total_cost, 6),
                    'percentage' => $totalGenerations > 0 ? round(($item->count / $totalGenerations) * 100, 1) : 0,
                ];
            })
            ->toArray();

        // Model breakdown
        $byModel = (clone $query)
            ->select('model', 'provider', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'), DB::raw('sum(estimated_cost) as total_cost'))
            ->groupBy('model', 'provider')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'model' => $item->model,
                    'provider' => $item->provider,
                    'count' => (int) $item->count,
                    'total_tokens' => (int) $item->total_tokens,
                    'total_cost' => round((float) $item->total_cost, 6),
                ];
            })
            ->toArray();

        // Style/Tone breakdown
        $byStyle = (clone $query)
            ->select('style', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'), DB::raw('sum(estimated_cost) as total_cost'))
            ->groupBy('style')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($totalGenerations) {
                return [
                    'style' => $item->style,
                    'count' => (int) $item->count,
                    'total_tokens' => (int) $item->total_tokens,
                    'total_cost' => round((float) $item->total_cost, 6),
                    'percentage' => $totalGenerations > 0 ? round(($item->count / $totalGenerations) * 100, 1) : 0,
                ];
            })
            ->toArray();

        // Source breakdown (e.g. manual, automated, api, regenerate)
        $bySource = (clone $query)
            ->select('source', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'), DB::raw('sum(estimated_cost) as total_cost'))
            ->groupBy('source')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->source ?: 'manual',
                    'count' => (int) $item->count,
                    'total_tokens' => (int) $item->total_tokens,
                    'total_cost' => round((float) $item->total_cost, 6),
                ];
            })
            ->toArray();

        // Recent activity
        $recentActivity = (clone $query)
            ->with('post:id,product_title,affiliate_url')
            ->latest('timestamp')
            ->take(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'timestamp' => $log->timestamp ? $log->timestamp->toIso8601String() : now()->toIso8601String(),
                    'post_id' => $log->post_id,
                    'product_title' => $log->post->product_title ?? 'Shopee Product Deal',
                    'affiliate_url' => $log->post->affiliate_url ?? null,
                    'provider' => $log->provider,
                    'model' => $log->model,
                    'style' => $log->style,
                    'source' => $log->source ?? 'manual',
                    'status' => $log->status ?? 'success',
                    'prompt_tokens' => (int) $log->prompt_tokens,
                    'completion_tokens' => (int) $log->completion_tokens,
                    'total_tokens' => (int) $log->total_tokens,
                    'estimated_cost' => (float) $log->estimated_cost,
                    'execution_time_ms' => $log->execution_time_ms ? (int) $log->execution_time_ms : null,
                ];
            })
            ->toArray();

        $activeProvider = Setting::get('ai_provider', 'openai');
        $activeModel = Setting::get('ai_model', 'gpt-4o-mini');

        $avgTokensPerGen = $totalGenerations > 0 ? (int) round($totalTokensUsed / $totalGenerations) : 0;
        $avgCostPerGen = $totalGenerations > 0 ? round($estimatedCostUsd / $totalGenerations, 6) : 0;

        return [
            'period' => $period,
            'summary' => [
                'total_generations' => $totalGenerations,
                'prompt_tokens_total' => $promptTokensTotal,
                'completion_tokens_total' => $completionTokensTotal,
                'total_tokens_used' => $totalTokensUsed,
                'estimated_cost_usd' => round($estimatedCostUsd, 6),
                'avg_tokens_per_gen' => $avgTokensPerGen,
                'avg_cost_per_gen' => $avgCostPerGen,
                'avg_execution_time_ms' => round($avgExecutionTimeMs, 1),
                'active_provider' => $activeProvider,
                'active_model' => $activeModel,
            ],
            'timeline' => $timeline,
            'by_provider' => $byProvider,
            'by_model' => $byModel,
            'by_style' => $byStyle,
            'by_source' => $bySource,
            'recent_activity' => $recentActivity,
            'available_providers' => self::distinct()->pluck('provider')->filter()->values()->toArray(),
            'available_styles' => self::distinct()->pluck('style')->filter()->values()->toArray(),
            'available_sources' => self::distinct()->pluck('source')->filter()->values()->toArray(),
        ];
    }
}
