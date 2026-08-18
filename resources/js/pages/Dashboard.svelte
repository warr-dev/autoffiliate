<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router } from '@inertiajs/svelte';

    let {
        posts = [],
        draftsCount = 0,
        publishedCount = 0,
        totalCount = 0,
        aiAnalytics = null
    } = $props<{
        posts?: Array<any>;
        draftsCount?: number;
        publishedCount?: number;
        totalCount?: number;
        aiAnalytics?: any;
    }>();

    function formatTokenCount(num: number): string {
        if (!num) return '0';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
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
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-semibold mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Platform Active & Ready
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-3">
                <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
                    Affiliate Content Generator
                </span>
            </h1>
            <p class="text-gray-400 text-base md:text-lg max-w-xl mx-auto leading-relaxed">
                Paste a Shopee PH link → auto-extract media → generate viral drafts → publish to Facebook
            </p>
        </div>

        <!-- Quick Action Card -->
        <div class="max-w-2xl mx-auto mb-12">
            <a href="/create" class="block p-8 text-center bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 hover:border-indigo-500/50 rounded-2xl transition-all group shadow-2xl">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-indigo-500/20">
                    <span class="text-3xl text-white">＋</span>
                </div>
                <h2 class="text-xl font-bold text-gray-100 mb-2">Create New Post</h2>
                <p class="text-gray-400 text-sm">Paste a Shopee PH affiliate link to extract media and generate a draft</p>
            </a>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-3 gap-4 max-w-xl mx-auto mb-12">
            <div class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg">
                <div class="text-3xl font-extrabold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                    {totalCount}
                </div>
                <div class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider">Total Posts</div>
            </div>

            <div class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg">
                <div class="text-3xl font-extrabold bg-gradient-to-r from-amber-400 to-orange-400 bg-clip-text text-transparent">
                    {draftsCount}
                </div>
                <div class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider">Drafts</div>
            </div>

            <div class="bg-gray-900/70 border border-gray-800/80 p-5 rounded-2xl text-center shadow-lg">
                <div class="text-3xl font-extrabold bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">
                    {publishedCount}
                </div>
                <div class="text-xs text-gray-400 mt-1 font-semibold uppercase tracking-wider">Published</div>
            </div>
        </div>

        <!-- AI Analytics Dashboard Section -->
        {#if aiAnalytics}
            <div class="max-w-5xl mx-auto mb-12">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🤖</span>
                        <h2 class="text-lg font-bold text-gray-200">AI Model & Token Analytics</h2>
                    </div>
                    <a href="/settings/app" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">
                        Configure AI Provider →
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 rounded-2xl border border-indigo-500/20 bg-indigo-950/20">
                        <div class="text-xs text-indigo-300 font-semibold mb-1">Total Generations</div>
                        <div class="text-2xl font-extrabold text-white">
                            {aiAnalytics.summary?.total_generations || 0}
                        </div>
                        <div class="text-[11px] text-gray-400 mt-1">AI caption runs</div>
                    </div>

                    <div class="p-4 rounded-2xl border border-purple-500/20 bg-purple-950/20">
                        <div class="text-xs text-purple-300 font-semibold mb-1">Total Tokens Used</div>
                        <div class="text-2xl font-extrabold text-purple-300 font-mono">
                            {formatTokenCount(aiAnalytics.summary?.total_tokens_used || 0)}
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-emerald-500/20 bg-emerald-950/20">
                        <div class="text-xs text-emerald-300 font-semibold mb-1">Est. Cumulative Cost</div>
                        <div class="text-2xl font-extrabold text-emerald-400 font-mono">
                            ${(aiAnalytics.summary?.estimated_cost_usd || 0).toFixed(4)}
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl border border-amber-500/20 bg-amber-950/20">
                        <div class="text-xs text-amber-300 font-semibold mb-1">Active Provider</div>
                        <div class="text-base font-extrabold text-amber-200 capitalize truncate">
                            {aiAnalytics.summary?.active_provider || 'OpenAI'}
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <!-- Recent posts -->
        <div class="max-w-3xl mx-auto">
            <h2 class="text-lg font-bold text-gray-200 mb-4">Recent Posts</h2>

            {#if posts.length === 0}
                <div class="p-12 text-center bg-gray-900/60 border border-gray-800/80 rounded-2xl">
                    <div class="text-4xl mb-3 opacity-30">📦</div>
                    <p class="text-gray-400 mb-1 font-medium">No posts yet</p>
                    <p class="text-gray-600 text-xs">Create your first post to get started</p>
                </div>
            {:else}
                <div class="space-y-3">
                    {#each posts as post (post.id)}
                        <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-4 rounded-2xl flex items-center justify-between shadow-md hover:border-indigo-500/40 transition-all">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-emerald-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0 text-lg">
                                    🔗
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-sm text-gray-200 truncate">{post.product_title || 'Shopee Product Deal'}</p>
                                    <p class="text-xs text-indigo-400 font-mono truncate">{post.affiliate_url}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/20 text-amber-400">
                                    {post.status}
                                </span>
                                <button
                                    onclick={(e) => handleDeletePost(post.id, e)}
                                    class="p-1.5 text-gray-500 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition-colors cursor-pointer"
                                    title="Delete post"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</AppLayout>
