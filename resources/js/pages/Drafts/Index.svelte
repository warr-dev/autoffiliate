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
    let previewingPost = $state<any | null>(null);

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
        <div class="grid grid-cols-3 gap-2 sm:gap-4 max-w-xl mx-auto">
            {#each [
                { label: 'Total Posts', value: stats.total, color: 'from-indigo-400 to-purple-500' },
                { label: 'Drafts', value: stats.drafts, color: 'from-amber-400 to-orange-500' },
                { label: 'Published', value: stats.posted, color: 'from-emerald-400 to-teal-500' },
            ] as stat}
                <div class="p-3 sm:p-4 rounded-2xl bg-gray-900/60 border border-gray-800/80 text-center shadow-md">
                    <div class="text-xl sm:text-2xl font-bold bg-gradient-to-r {stat.color} bg-clip-text text-transparent">
                        {stat.value}
                    </div>
                    <div class="text-[11px] sm:text-xs text-gray-400 mt-0.5 sm:mt-1">{stat.label}</div>
                </div>
            {/each}
        </div>

        <!-- Recent Posts / Drafts Section (Reference List Style) -->
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-gray-200">
                    Recent Drafts & Posts
                </h2>
                {#if posts.length > 0}
                    <div class="relative w-full sm:w-64">
                        <input
                            type="text"
                            bind:value={searchQuery}
                            placeholder="Filter drafts..."
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3 py-2 text-xs text-white placeholder-gray-500 outline-none focus:border-indigo-500 transition-colors"
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
                <div class="space-y-3">
                    {#each filteredPosts as post (post.id)}
                        {@const mediaCount = Array.isArray(post.media_files) ? post.media_files.length : (post.media_files ? 1 : 0)}
                        <div
                            class="p-4 sm:p-4.5 rounded-2xl bg-gray-900/70 hover:bg-gray-900/90 border border-gray-800/80 hover:border-indigo-500/40 transition-all shadow-md group flex flex-col gap-3"
                        >
                            <!-- Top Row: Thumbnail + Product Title + Status Badge -->
                            <div class="flex items-start justify-between gap-3 min-w-0">
                                <a
                                    href="/drafts/{post.id}"
                                    class="flex items-start gap-3 min-w-0 flex-1 group/title cursor-pointer"
                                >
                                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700/60 flex items-center justify-center flex-shrink-0 text-base shadow-sm group-hover/title:scale-105 transition-transform">
                                        {mediaCount > 0 ? '🖼️' : '🔗'}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-sm text-gray-200 group-hover/title:text-indigo-300 transition-colors line-clamp-2 sm:line-clamp-1 leading-snug">
                                            {post.product_title || 'Untitled Post'}
                                        </p>
                                        <div class="text-xs text-gray-400 mt-1 flex items-center gap-2 flex-wrap">
                                            <span>{new Date(post.created_at).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                                            <span>·</span>
                                            <span>{mediaCount} media</span>
                                            {#if post.product_price}
                                                <span>·</span>
                                                <span class="text-emerald-400 font-bold font-mono">{post.product_price}</span>
                                            {/if}
                                        </div>
                                    </div>
                                </a>

                                <!-- Status Badge & Live Link -->
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    {#if post.facebook_post_url}
                                        <a
                                            href={post.facebook_post_url}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold bg-blue-500/15 text-blue-300 hover:text-blue-200 border border-blue-500/30 hover:border-blue-400/50 shadow-sm transition-all active:scale-95"
                                            title="Open live post on Facebook"
                                        >
                                            <span>🌐 View on FB ↗</span>
                                        </a>
                                    {/if}
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-semibold tracking-wide uppercase
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
                                </div>
                            </div>

                            <!-- Bottom Row: Touch-Friendly Action Toolbar & Edit Post Link -->
                            <div class="pt-2.5 border-t border-gray-800/60 flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                                <div class="flex items-center gap-2 flex-wrap">
                                    {#if post.status === 'posted' || post.status === 'published'}
                                        <!-- Posted Bottom Actions: Preview Post, View on FB, Delete -->
                                        <button
                                            type="button"
                                            onclick={(e) => { e.stopPropagation(); previewingPost = post; }}
                                            class="px-2.5 py-1.5 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 hover:text-purple-200 border border-purple-500/20 text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer shadow-sm"
                                            title="Preview published post card"
                                        >
                                            <span>👁️</span>
                                            <span>Preview Post</span>
                                        </button>

                                        {#if post.facebook_post_url}
                                            <a
                                                href={post.facebook_post_url}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="px-2.5 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 hover:text-blue-300 border border-blue-500/20 text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95 cursor-pointer shadow-sm"
                                                title="Open live post on Facebook"
                                            >
                                                <span>🌐</span>
                                                <span>View on FB ↗</span>
                                            </a>
                                        {/if}

                                        <button
                                            type="button"
                                            onclick={(e) => handleDelete(post.id, e)}
                                            class="p-1.5 px-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 text-xs font-semibold transition-all active:scale-95 cursor-pointer"
                                            title="Delete post record"
                                        >
                                            🗑️
                                        </button>
                                    {:else}
                                        <!-- Draft Bottom Actions: AI Re-roll, Publish, Delete -->
                                        <button
                                            type="button"
                                            onclick={(e) => handleGenerateCaption(post.id, e)}
                                            disabled={generatingId === post.id}
                                            class="px-2.5 py-1.5 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 hover:text-indigo-200 border border-indigo-500/20 text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
                                            title="AI Re-roll caption"
                                        >
                                            {#if generatingId === post.id}
                                                <span class="animate-spin text-xs">🌀</span>
                                                <span>Generating...</span>
                                            {:else}
                                                <span>✨</span>
                                                <span>AI Re-roll</span>
                                            {/if}
                                        </button>

                                        <button
                                            type="button"
                                            onclick={(e) => handlePublish(post.id, e)}
                                            disabled={publishingId === post.id}
                                            class="px-2.5 py-1.5 rounded-xl bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 hover:text-emerald-200 border border-emerald-500/20 text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
                                            title="Publish to Facebook"
                                        >
                                            {#if publishingId === post.id}
                                                <span class="animate-spin text-xs">🌀</span>
                                                <span>Publishing...</span>
                                            {:else}
                                                <span>🚀</span>
                                                <span>Publish</span>
                                            {/if}
                                        </button>

                                        <button
                                            type="button"
                                            onclick={(e) => handleDelete(post.id, e)}
                                            class="p-1.5 px-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 text-xs font-semibold transition-all active:scale-95 cursor-pointer"
                                            title="Delete post draft"
                                        >
                                            🗑️
                                        </button>
                                    {/if}
                                </div>

                                <a
                                    href="/drafts/{post.id}"
                                    class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 hover:underline flex items-center gap-1 ml-auto transition-colors py-1 cursor-pointer"
                                >
                                    <span>{post.status === 'posted' || post.status === 'published' ? 'Edit Post' : 'Edit Draft'}</span>
                                    <span>→</span>
                                </a>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </div>

    <!-- Quick Preview Post Modal -->
    {#if previewingPost}
        <div
            class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 overscroll-contain animate-fadeIn"
            onclick={() => (previewingPost = null)}
            role="dialog"
            aria-modal="true"
            tabindex="-1"
        >
            <div
                class="bg-gray-950 border border-purple-500/30 rounded-2xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl relative animate-scaleIn flex flex-col max-h-[92vh]"
                onclick={(e) => e.stopPropagation()}
                role="document"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 border border-purple-500/30 flex items-center justify-center text-sm">
                            👁️
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm text-gray-100 truncate">Post Preview</h3>
                            <p class="text-[11px] text-gray-400 truncate">{previewingPost.product_title || 'Post'}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick={() => (previewingPost = null)}
                        class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors cursor-pointer"
                    >
                        ✕
                    </button>
                </div>

                <!-- Preview Body (Facebook Mockup) -->
                <div class="py-4 overflow-y-auto space-y-4 flex-1">
                    <div class="bg-gray-900 border border-gray-800 rounded-xl p-4 shadow-inner space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-blue-600/30 border border-blue-500/40 flex items-center justify-center text-blue-400 font-bold text-xs">
                                FB
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-200">
                                    {socialAccounts && socialAccounts[0]?.name ? socialAccounts[0].name : 'Facebook Page'}
                                </p>
                                <p class="text-[10px] text-gray-500 font-mono">
                                    {new Date(previewingPost.created_at || Date.now()).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })} · 🌐 Public
                                </p>
                            </div>
                        </div>

                        <!-- Caption & Link -->
                        <div class="text-xs text-gray-200 whitespace-pre-line leading-relaxed font-sans">
                            {previewingPost.caption || 'No caption generated.'}
                        </div>

                        <!-- Hashtags -->
                        {#if previewingPost.tags}
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                {#each previewingPost.tags.split(/\s+/) as tag}
                                    {#if tag.trim()}
                                        <span class="text-[11px] font-semibold text-blue-400 hover:underline">{tag}</span>
                                    {/if}
                                {/each}
                            </div>
                        {/if}

                        <!-- Media Preview -->
                        {#if previewingPost.media_files && previewingPost.media_files.length > 0}
                            <div class="grid grid-cols-2 gap-2 pt-2 rounded-xl overflow-hidden">
                                {#each previewingPost.media_files.slice(0, 4) as mediaUrl}
                                    <img
                                        src={mediaUrl}
                                        alt="Post media preview"
                                        class="w-full h-36 object-cover rounded-lg border border-gray-800"
                                        onerror={(e) => { (e.target as HTMLElement).style.display = 'none'; }}
                                    />
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pt-3 border-t border-gray-800 flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        {#if previewingPost.facebook_post_url}
                            <a
                                href={previewingPost.facebook_post_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5"
                            >
                                <span>🌐 Open in Facebook ↗</span>
                            </a>
                        {/if}
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="/drafts/{previewingPost.id}"
                            class="px-3.5 py-1.5 rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 text-xs font-semibold transition-all flex items-center gap-1"
                        >
                            <span>Open Editor →</span>
                        </a>
                        <button
                            type="button"
                            onclick={() => (previewingPost = null)}
                            class="px-3 py-1.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-semibold transition-colors cursor-pointer"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    {/if}
</AppLayout>
