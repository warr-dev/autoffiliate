<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let { posts = [], socialAccounts = [] } = $props<{
        posts: Array<any>;
        socialAccounts?: Array<any>;
    }>();

    let showModal = $state(false);
    let product_title = $state('');
    let product_price = $state('');
    let affiliate_url = $state('');
    let captionStyle = $state('viral_ai');
    let creating = $state(false);
    let searchQuery = $state('');

    let stats = $derived({
        total: posts.length,
        drafts: posts.filter((p: any) => p.status === 'draft' || !p.status).length,
        posted: posts.filter((p: any) => p.status === 'posted' || p.status === 'published').length,
    });

    let filteredPosts = $derived(
        posts.filter((p: any) => {
            if (!searchQuery.trim()) return true;
            const q = searchQuery.toLowerCase().trim();
            return (
                (p.product_title || '').toLowerCase().includes(q) ||
                (p.id || '').toLowerCase().includes(q) ||
                (p.caption || '').toLowerCase().includes(q)
            );
        })
    );

    function handleCreate(e: SubmitEvent) {
        e.preventDefault();
        creating = true;
        router.post(
            '/drafts',
            {
                product_title: product_title.trim() || 'Shopee Sulit Deal',
                product_price: product_price.trim(),
                affiliate_url: affiliate_url.trim(),
                caption_style: captionStyle,
            },
            {
                onSuccess: () => {
                    showModal = false;
                    product_title = '';
                    product_price = '';
                    affiliate_url = '';
                },
                onFinish: () => {
                    creating = false;
                },
            },
        );
    }

    let publishingId = $state<string | null>(null);
    let generatingId = $state<string | null>(null);

    function handlePublish(id: string, e: MouseEvent) {
        e.preventDefault();
        e.stopPropagation();

        if (!confirm('Publish this draft to your connected Facebook page(s) now?')) {
            return;
        }

        publishingId = id;
        router.post(
            `/drafts/${id}/publish`,
            {},
            {
                onFinish: () => {
                    publishingId = null;
                },
            },
        );
    }

    function handleGenerateCaption(id: string, e: MouseEvent, style = 'viral_ai') {
        e.preventDefault();
        e.stopPropagation();

        generatingId = id;
        router.post(
            `/drafts/${id}/generate-caption`,
            { caption_style: style },
            {
                onFinish: () => {
                    generatingId = null;
                },
            },
        );
    }

    function handleDelete(id: string, e: MouseEvent) {
        e.preventDefault();
        e.stopPropagation();

        if (confirm('Are you sure you want to delete this draft?')) {
            router.delete(`/drafts/${id}`);
        }
    }
</script>

<AppLayout title="Post Drafts">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        <!-- Hero Header (Reference Style) -->
        <div class="text-center animate-slideUp">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-medium mb-4">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Platform Ready
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-2">
                <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
                    Affiliate Content Generator
                </span>
            </h1>
            <p class="text-gray-400 text-sm max-w-xl mx-auto">
                Paste a Shopee PH link → auto-extract media → generate viral drafts → publish to Facebook
            </p>
        </div>

        <!-- Quick Action Create Card (Reference Style) -->
        <div class="max-w-2xl mx-auto animate-slideUp">
            <a
                href="/create"
                class="block p-6 sm:p-8 text-center group cursor-pointer bg-gray-900/70 hover:bg-gray-900 border border-gray-800/80 hover:border-indigo-500/50 rounded-2xl transition-all shadow-xl hover:shadow-indigo-500/10"
            >
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center mx-auto mb-3.5 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-indigo-500/20">
                    <span class="text-2xl text-white font-bold">＋</span>
                </div>
                <h2 class="text-lg font-bold text-gray-100 mb-1">Create New Post</h2>
                <p class="text-gray-400 text-xs">Paste a Shopee PH affiliate link to extract media & generate copy</p>
            </a>
        </div>

        <!-- Stats Counters (Reference Style) -->
        <div class="grid grid-cols-3 gap-4 max-w-xl mx-auto">
            {#each [
                { label: 'Total Posts', value: stats.total, color: 'from-indigo-400 to-purple-500' },
                { label: 'Drafts', value: stats.drafts, color: 'from-amber-400 to-orange-500' },
                { label: 'Published', value: stats.posted, color: 'from-emerald-400 to-teal-500' },
            ] as stat}
                <div class="p-4 rounded-2xl bg-gray-900/60 border border-gray-800/80 text-center shadow-md">
                    <div class="text-2xl font-bold bg-gradient-to-r {stat.color} bg-clip-text text-transparent">
                        {stat.value}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">{stat.label}</div>
                </div>
            {/each}
        </div>

        <!-- Recent Posts / Drafts Section (Reference List Style) -->
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-200">
                    Recent Drafts & Posts
                </h2>
                {#if posts.length > 0}
                    <div class="relative w-48 sm:w-64">
                        <input
                            type="text"
                            bind:value={searchQuery}
                            placeholder="Filter drafts..."
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-white placeholder-gray-500 outline-none focus:border-indigo-500"
                        />
                    </div>
                {/if}
            </div>

            {#if filteredPosts.length === 0}
                <div class="p-12 text-center border border-gray-800/80 rounded-2xl bg-gray-900/60 backdrop-blur-xl">
                    <div class="text-4xl mb-3 opacity-30">📦</div>
                    <p class="text-gray-400 font-medium text-sm">No post drafts available</p>
                    <p class="text-xs text-gray-500 mt-1">Paste a Shopee link to get started</p>
                </div>
            {:else}
                <div class="space-y-2.5">
                    {#each filteredPosts as post (post.id)}
                        {@const mediaCount = Array.isArray(post.media_files) ? post.media_files.length : (post.media_files ? 1 : 0)}
                        <a
                            href="/drafts/{post.id}"
                            class="p-4 rounded-2xl bg-gray-900/70 hover:bg-gray-900 border border-gray-800/80 hover:border-indigo-500/50 flex items-center justify-between gap-3 transition-all cursor-pointer shadow-md group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700/60 flex items-center justify-center flex-shrink-0 text-base shadow-sm">
                                    {mediaCount > 0 ? '🖼️' : '🔗'}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-sm text-gray-200 group-hover:text-indigo-300 transition-colors truncate">
                                        {post.product_title || 'Untitled Post'}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-2">
                                        <span>{new Date(post.created_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                                        <span>·</span>
                                        <span>{mediaCount} media</span>
                                        {#if post.product_price}
                                            <span>·</span>
                                            <span class="text-emerald-400 font-bold font-mono">{post.product_price}</span>
                                        {/if}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 flex-shrink-0 ml-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide uppercase
                                    {post.status === 'posted' || post.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 
                                     post.status === 'publishing' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shadow-sm' : 
                                     post.status === 'failed' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 
                                     'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                        {post.status === 'posted' || post.status === 'published' ? 'bg-emerald-400' : 
                                         post.status === 'publishing' ? 'bg-indigo-400 animate-ping' : 
                                         post.status === 'failed' ? 'bg-red-400' : 
                                         'bg-amber-400 animate-pulse'}"></span>
                                    {post.status === 'posted' || post.status === 'published' ? '✓ Posted' : 
                                     post.status === 'publishing' ? '⌛ Publishing...' : 
                                     post.status === 'failed' ? '❌ Failed' : 
                                     '✎ Draft'}
                                </span>

                                <button
                                    type="button"
                                    onclick={(e) => handleGenerateCaption(post.id, e)}
                                    disabled={generatingId === post.id}
                                    class="p-1.5 text-gray-400 hover:text-indigo-300 rounded-lg hover:bg-indigo-500/10 transition-colors cursor-pointer"
                                    title="AI Re-roll caption"
                                >
                                    {#if generatingId === post.id}
                                        <span class="animate-spin text-xs">🌀</span>
                                    {:else}
                                        <span>✨</span>
                                    {/if}
                                </button>

                                <button
                                    type="button"
                                    onclick={(e) => handlePublish(post.id, e)}
                                    disabled={publishingId === post.id}
                                    class="p-1.5 text-gray-400 hover:text-emerald-400 rounded-lg hover:bg-emerald-500/10 transition-colors cursor-pointer"
                                    title="Publish to Facebook"
                                >
                                    {#if publishingId === post.id}
                                        <span class="animate-spin text-xs">🌀</span>
                                    {:else}
                                        <span>🚀</span>
                                    {/if}
                                </button>

                                <button
                                    type="button"
                                    onclick={(e) => handleDelete(post.id, e)}
                                    class="p-1.5 text-gray-400 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition-colors cursor-pointer"
                                    title="Delete post draft"
                                >
                                    🗑️
                                </button>
                            </div>
                        </a>
                    {/each}
                </div>
            {/if}
        </div>
    </div>
</AppLayout>
