<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    /**
     * Display the full AI Usage & Analytics page.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'period' => $request->query('period', '30d'),
            'provider' => $request->query('provider', 'all'),
            'style' => $request->query('style', 'all'),
            'source' => $request->query('source', 'all'),
        ];

        $analytics = AiUsageLog::getAnalytics($filters);

        return Inertia::render('Analytics/Index', [
            'analytics' => $analytics,
            'filters' => $filters,
            'currentProvider' => Setting::get('ai_provider', 'openai'),
            'currentModel' => Setting::get('ai_model', 'gpt-4o-mini'),
        ]);
    }

    /**
     * API endpoint returning structured AI analytics data.
     */
    public function apiAnalytics(Request $request): JsonResponse
    {
        $filters = [
            'period' => $request->query('period', 'all'),
            'provider' => $request->query('provider', 'all'),
            'style' => $request->query('style', 'all'),
            'source' => $request->query('source', 'all'),
        ];

        return response()->json(AiUsageLog::getAnalytics($filters));
    }

    /**
     * Export AI usage records in CSV or JSON format.
     */
    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $format = strtolower($request->query('format', 'csv'));
        $period = $request->query('period', 'all');

        $query = AiUsageLog::with('post:id,product_title');

        if ($period !== 'all') {
            $now = Carbon::now('Asia/Manila');
            $startDate = match ($period) {
                'today' => $now->copy()->startOfDay(),
                '7d' => $now->copy()->subDays(7)->startOfDay(),
                '30d' => $now->copy()->subDays(30)->startOfDay(),
                '90d' => $now->copy()->subDays(90)->startOfDay(),
                default => null,
            };
            if ($startDate) {
                $query->where('timestamp', '>=', $startDate);
            }
        }

        $logs = $query->latest('timestamp')->get();

        if ($format === 'json') {
            $data = [
                'exported_at' => now()->toIso8601String(),
                'record_count' => $logs->count(),
                'total_tokens' => (int) $logs->sum('total_tokens'),
                'total_cost_usd' => round((float) $logs->sum('estimated_cost'), 6),
                'logs' => $logs->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'timestamp' => $log->timestamp ? $log->timestamp->toIso8601String() : null,
                        'post_id' => $log->post_id,
                        'product_title' => $log->post->product_title ?? null,
                        'provider' => $log->provider,
                        'model' => $log->model,
                        'style' => $log->style,
                        'source' => $log->source,
                        'status' => $log->status,
                        'prompt_tokens' => (int) $log->prompt_tokens,
                        'completion_tokens' => (int) $log->completion_tokens,
                        'total_tokens' => (int) $log->total_tokens,
                        'estimated_cost_usd' => (float) $log->estimated_cost,
                        'execution_time_ms' => (int) $log->execution_time_ms,
                    ];
                }),
            ];

            $filename = 'ai-analytics-'.now()->format('Y-m-d-His').'.json';

            return response()->streamDownload(function () use ($data) {
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            }, $filename, ['Content-Type' => 'application/json']);
        }

        // CSV export
        $filename = 'ai-usage-analytics-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($handle, [
                'Log ID',
                'Date & Time (Manila)',
                'Post ID',
                'Product / Deal Title',
                'Provider',
                'Model',
                'Caption Style',
                'Source',
                'Status',
                'Prompt Tokens',
                'Completion Tokens',
                'Total Tokens',
                'Est Cost (USD)',
                'Latency (ms)',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->timestamp ? $log->timestamp->timezone('Asia/Manila')->format('Y-m-d H:i:s') : '',
                    $log->post_id ?? '',
                    $log->post->product_title ?? 'N/A',
                    $log->provider,
                    $log->model,
                    $log->style,
                    $log->source ?? 'manual',
                    $log->status ?? 'success',
                    $log->prompt_tokens,
                    $log->completion_tokens,
                    $log->total_tokens,
                    $log->estimated_cost,
                    $log->execution_time_ms ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Clear or prune usage logs.
     */
    public function clear(Request $request): JsonResponse
    {
        $days = (int) $request->input('older_than_days', 0);

        if ($days > 0) {
            $cutoff = Carbon::now('Asia/Manila')->subDays($days);
            $deleted = AiUsageLog::where('timestamp', '<', $cutoff)->delete();
            $message = "Pruned {$deleted} AI logs older than {$days} days.";
        } else {
            $deleted = AiUsageLog::query()->delete();
            $message = "Cleared all {$deleted} AI usage log records.";
        }

        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
            'message' => $message,
        ]);
    }
}
