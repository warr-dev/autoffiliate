<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'posts' => Post::latest()->take(10)->get(),
            'totalCount' => Post::count(),
            'draftsCount' => Post::where('status', 'draft')->count(),
            'publishedCount' => Post::where('status', 'published')->count(),
            'aiAnalytics' => $this->getAiAnalyticsData(),
        ]);
    }

    public function aiAnalytics(Request $request): JsonResponse
    {
        return response()->json($this->getAiAnalyticsData());
    }

    public function getAiAnalyticsData(): array
    {
        $totalGenerations = AiUsageLog::count();
        $promptTokensTotal = (int) AiUsageLog::sum('prompt_tokens');
        $completionTokensTotal = (int) AiUsageLog::sum('completion_tokens');
        $totalTokensUsed = (int) AiUsageLog::sum('total_tokens');
        $estimatedCostUsd = (float) AiUsageLog::sum('estimated_cost');

        $activeProvider = Setting::get('ai_provider', 'openai');
        $activeModel = Setting::get('ai_model', 'gpt-4o-mini');

        $byProvider = AiUsageLog::select('provider', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'))
            ->groupBy('provider')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        $byStyle = AiUsageLog::select('style', DB::raw('count(*) as count'), DB::raw('sum(total_tokens) as total_tokens'))
            ->groupBy('style')
            ->orderByDesc('count')
            ->get()
            ->toArray();

        $recentActivity = AiUsageLog::with('post:id,product_title')
            ->latest('timestamp')
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'timestamp' => $log->timestamp->toIso8601String(),
                    'post_id' => $log->post_id,
                    'product_title' => $log->post->product_title ?? 'Shopee Product Deal',
                    'provider' => $log->provider,
                    'model' => $log->model,
                    'style' => $log->style,
                    'prompt_tokens' => (int) $log->prompt_tokens,
                    'completion_tokens' => (int) $log->completion_tokens,
                    'total_tokens' => (int) $log->total_tokens,
                    'estimated_cost' => (float) $log->estimated_cost,
                ];
            })
            ->toArray();

        return [
            'summary' => [
                'total_generations' => $totalGenerations,
                'prompt_tokens_total' => $promptTokensTotal,
                'completion_tokens_total' => $completionTokensTotal,
                'total_tokens_used' => $totalTokensUsed,
                'estimated_cost_usd' => round($estimatedCostUsd, 6),
                'active_provider' => $activeProvider,
                'active_model' => $activeModel,
            ],
            'by_provider' => $byProvider,
            'by_style' => $byStyle,
            'recent_activity' => $recentActivity,
        ];
    }
}
