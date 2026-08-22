<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'estimated_cost' => 'float',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public static function logUsage(
        ?string $postId,
        string $provider,
        string $model,
        string $style,
        int $promptTokens,
        int $completionTokens,
        int $totalTokens
    ): self {
        $pRate = 0.00000020;
        $cRate = 0.00000050;

        $provLower = strtolower($provider);
        if (str_contains($provLower, 'deepseek')) {
            $pRate = 0.00000014;
            $cRate = 0.00000028;
        } elseif (str_contains($provLower, 'openai') || str_contains(strtolower($model), 'gpt-4')) {
            $pRate = 0.00000015;
            $cRate = 0.00000060;
        } elseif (str_contains($provLower, 'gemini')) {
            $pRate = 0.000000075;
            $cRate = 0.00000030;
        }

        $estimatedCost = ($promptTokens * $pRate) + ($completionTokens * $cRate);

        return self::create([
            'id' => 'ai_' . Str::random(12),
            'timestamp' => now(),
            'post_id' => $postId,
            'provider' => $provider,
            'model' => $model,
            'style' => $style,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'estimated_cost' => round($estimatedCost, 6),
        ]);
    }
}
