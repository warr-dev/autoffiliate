<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let {
        posts = [],
        draftsCount = 0,
        publishedCount = 0,
        totalCount = 0,
        aiAnalytics = null,
    } = $props<{
        posts?: Array<any>;
        draftsCount?: number;
        publishedCount?: number;
        totalCount?: number;
        aiAnalytics?: any;
    }>();

    function formatTokenCount(num: number): string {
        if (!num) {
            return '0';
        }

        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }

        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }

        return num.toString();
    }

    function handleDeletePost(id: string, e: MouseEvent) {
        e.preventDefault();
        e.stopPropagation();

        if (confirm('Are you sure you want to delete this post?')) {
            router.delete(`/drafts/${id}`);
        }
    }
</script>

<AppLayout title="Dashboard">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Hero -->
        <div class="text-center mb-12">
            <div
                class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-4"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                ></span>
                Platform Active & Ready
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-3">
                <span
                    class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                >
                    Affiliate Content Generator
                </span>
            </h1>
            <p
                class="text-gray-400 text-base md:text-lg max-w-xl mx-auto leading-relaxed"
            >
                Paste a Shopee PH link → auto-extract media → generate viral
                drafts → publish to Facebook
            </p>
        </div>

        <!-- Quick Action Card -->
        <div class="max-w-2xl mx-auto mb-12">
            <a
                href="/create"
                class="block p-8 text-center bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 hover:border-indigo-500/50 rounded-2xl transition-all group shadow-2xl"
            >
                <div
                    class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-indigo-500/20"
                >
                    <span class="text-3xl text-white">＋</span>
                </div>
                <h2 class="text-xl font-bold text-gray-100 mb-2">
                    Create New Post
                </h2>
                <p class="text-gray-400 text-sm">
                    Paste a Shopee PH affiliate link to extract media and
                    generate a draft
                </p>
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-4 max-w-xl mx-auto mb-12">
            <div
                class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg"
            >
                <div
                    class="text-3xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent"
                >
                    {totalCount}
                </div>
                <div
                    class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider"
                >
                    Total Posts
                </div>
            </div>

            <div
                class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg"
            >
                <div
                    class="text-3xl font-extrabold bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent"
                >
                    {draftsCount}
                </div>
                <div
                    class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider"
                >
                    Drafts
                </div>
            </div>

            <div
                class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg"
            >
                <div
                    class="text-3xl font-extrabold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent"
                >
                    {publishedCount}
                </div>
                <div
                    class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider"
                >
                    Published
                </div>
            </div>
        </div>

        <!-- AI Analytics Dashboard Section -->
        {#if aiAnalytics}
            <div class="max-w-5xl mx-auto mb-12">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🤖</span>
                        <h2 class="text-lg font-bold text-gray-200">
                            AI Model & Token Analytics
                        </h2>
                    </div>
                    <a
                        href="/settings/app"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                    >
                        Configure AI Provider →
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div
                        class="p-4 rounded-2xl border border-indigo-500/20 bg-indigo-950/20"
                    >
                        <div class="text-xs text-indigo-300 font-semibold mb-1">
                            Total Generations
                        </div>
                        <div class="text-2xl font-extrabold text-white">
                            {aiAnalytics.summary?.total_generations || 0}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1">
                            AI caption runs
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-2xl border border-purple-500/20 bg-purple-950/20"
                    >
                        <div class="text-xs text-purple-300 font-semibold mb-1">
                            Total Tokens Used
                        </div>
                        <div
                            class="text-2xl font-extrabold text-purple-300 font-mono"
                        >
                            {formatTokenCount(
                                aiAnalytics.summary?.total_tokens_used || 0,
                            )}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-2xl border border-emerald-500/20 bg-emerald-950/20"
                    >
                        <div
                            class="text-xs text-emerald-300 font-semibold mb-1"
                        >
                            Est. Cumulative Cost
                        </div>
                        <div
                            class="text-2xl font-extrabold text-emerald-400 font-mono"
                        >
                            ${(
                                aiAnalytics.summary?.estimated_cost_usd || 0
                            ).toFixed(4)}
                        </div>
                    </div>

                    <div
                        class="p-4 rounded-2xl border border-amber-500/20 bg-amber-950/20"
                    >
                        <div class="text-xs text-amber-300 font-semibold mb-1">
                            Active Provider
                        </div>
                        <div
                            class="text-base font-extrabold text-amber-200 capitalize truncate"
                        >
                            {aiAnalytics.summary?.active_provider || 'OpenAI'}
                        </div>
                        <div
                            class="text-[11px] text-amber-400/80 font-mono truncate mt-1"
                        >
                            {aiAnalytics.summary?.active_model || 'gpt-4o-mini'}
                        </div>
                    </div>
                </div>

                <!-- Breakdown by Provider & Tone Preset -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- Provider Usage -->
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl"
                    >
                        <h3
                            class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                        >
                            ⚡ Usage by AI Provider
                        </h3>
                        {#if !aiAnalytics.by_provider || aiAnalytics.by_provider.length === 0}
                            <p class="text-xs text-gray-500 py-4 text-center">
                                No external AI requests logged yet
                            </p>
                        {:else}
                            <div class="space-y-3">
                                {#each aiAnalytics.by_provider as prov}
                                    <div>
                                        <div
                                            class="flex justify-between text-xs font-medium mb-1"
                                        >
                                            <span
                                                class="text-gray-200 capitalize"
                                                >{prov.provider}</span
                                            >
                                            <span
                                                class="font-mono text-gray-400"
                                                >{prov.count} runs ({formatTokenCount(
                                                    prov.total_tokens,
                                                )} tok)</span
                                            >
                                        </div>
                                        <div
                                            class="w-full h-2 rounded-full bg-gray-800 overflow-hidden"
                                        >
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-400"
                                                style="width: {Math.min(
                                                    100,
                                                    Math.max(
                                                        10,
                                                        (prov.count /
                                                            (aiAnalytics.summary
                                                                ?.total_generations ||
                                                                1)) *
                                                            100,
                                                    ),
                                                )}%"
                                            ></div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>

                    <!-- Tone Preset Usage -->
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl"
                    >
                        <h3
                            class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 flex items-center gap-2"
                        >
                            ✨ Usage by Caption Tone
                        </h3>
                        {#if !aiAnalytics.by_style || aiAnalytics.by_style.length === 0}
                            <p class="text-xs text-gray-500 py-4 text-center">
                                No tone styles logged yet
                            </p>
                        {:else}
                            <div class="space-y-3">
                                {#each aiAnalytics.by_style as style}
                                    <div>
                                        <div
                                            class="flex justify-between text-xs font-medium mb-1"
                                        >
                                            <span
                                                class="text-gray-200 uppercase tracking-wider text-[11px]"
                                            >
                                                {style.style.replace('_', ' ')}
                                            </span>
                                            <span
                                                class="font-mono text-gray-400"
                                                >{style.count} runs ({formatTokenCount(
                                                    style.total_tokens,
                                                )} tok)</span
                                            >
                                        </div>
                                        <div
                                            class="w-full h-2 rounded-full bg-gray-800 overflow-hidden"
                                        >
                                            <div
                                                class="h-full rounded-full bg-gradient-to-r from-purple-500 to-indigo-400"
                                                style="width: {Math.min(
                                                    100,
                                                    Math.max(
                                                        10,
                                                        (style.count /
                                                            (aiAnalytics.summary
                                                                ?.total_generations ||
                                                                1)) *
                                                            100,
                                                    ),
                                                )}%"
                                            ></div>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Recent AI Activity Feed -->
                {#if aiAnalytics.recent_activity && aiAnalytics.recent_activity.length > 0}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl"
                    >
                        <h3
                            class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3"
                        >
                            📋 Recent AI Generation Activity
                        </h3>
                        <div
                            class="divide-y divide-gray-800/60 overflow-x-auto"
                        >
                            {#each aiAnalytics.recent_activity as log}
                                <div
                                    class="py-2.5 flex items-center justify-between text-xs gap-3"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="font-medium text-gray-200 truncate"
                                        >
                                            {log.product_title ||
                                                'Shopee Product Draft'}
                                        </p>
                                        <p
                                            class="text-[11px] text-gray-500 mt-0.5 font-mono"
                                        >
                                            {new Date(
                                                log.timestamp,
                                            ).toLocaleTimeString('en-US', {
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })} · {log.provider} ({log.model}) ·
                                            preset: {log.style}
                                        </p>
                                    </div>
                                    <div
                                        class="flex items-center gap-3 font-mono flex-shrink-0 text-right"
                                    >
                                        <span class="text-gray-400"
                                            >{log.total_tokens} tok</span
                                        >
                                        <span class="text-emerald-400 font-bold"
                                            >${(
                                                log.estimated_cost || 0
                                            ).toFixed(5)}</span
                                        >
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>
        {/if}

        <!-- Recent posts -->
        <div class="max-w-3xl mx-auto">
            <h2 class="text-lg font-bold text-gray-200 mb-4">Recent Posts</h2>

            {#if posts.length === 0}
                <div
                    class="p-12 text-center bg-gray-900/60 border border-gray-800/80 rounded-2xl"
                >
                    <div class="text-4xl mb-3 opacity-30">📦</div>
                    <p class="text-gray-400 mb-1 font-medium">No posts yet</p>
                    <p class="text-gray-600 text-xs">
                        Create your first post to get started
                    </p>
                </div>
            {:else}
                <div class="space-y-3">
                    {#each posts as post (post.id)}
                        <div
                            class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-4 rounded-2xl flex items-center justify-between shadow-md hover:border-indigo-500/40 transition-all"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-emerald-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0 text-lg"
                                >
                                    🔗
                                </div>
                                <div class="min-w-0">
                                    <p
                                        class="font-bold text-sm text-gray-200 truncate"
                                    >
                                        {post.product_title ||
                                            'Shopee Product Deal'}
                                    </p>
                                    <p
                                        class="text-xs text-indigo-400 font-mono truncate"
                                    >
                                        {post.affiliate_url}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-3 flex-shrink-0 ml-3"
                            >
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/20 text-amber-400"
                                >
                                    {post.status}
                                </span>
                                <button
                                    onclick={(e) =>
                                        handleDeletePost(post.id, e)}
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 border border-gray-800 hover:border-red-500/30 transition-colors cursor-pointer"
                                    title="Delete post"
                                >
                                    <svg
                                        class="w-3.5 h-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</AppLayout>
