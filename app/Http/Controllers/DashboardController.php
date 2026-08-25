<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $period = $request->query('period', 'all');
        return response()->json(AiUsageLog::getAnalytics(['period' => $period]));
    }

    public function getAiAnalyticsData(): array
    {
        return AiUsageLog::getAnalytics(['period' => 'all']);
    }
}
