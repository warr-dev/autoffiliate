<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let { posts = [] } = $props<{ posts: Array<any> }>();

    let filter = $state<'all' | 'draft' | 'approved' | 'published' | 'failed'>(
        'all',
    );

    let filtered = $derived(
        filter === 'all' ? posts : posts.filter((p) => p.status === filter),
    );

    function handleDeletePost(id: string, e: MouseEvent) {
        e.stopPropagation();

        if (confirm('Are you sure you want to delete this post record?')) {
            router.delete(`/drafts/${id}`);
        }
    }
</script>

<AppLayout title="Post History">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
        >
            <div>
                <a
                    href="/dashboard"
                    class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors mb-2 inline-block"
                    >← Dashboard</a
                >
                <h1 class="text-3xl font-extrabold tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                    >
                        Post History
                    </span>
                </h1>
                <p class="text-gray-400 text-xs mt-1">
                    {posts.length} total recorded posts
                </p>
            </div>

            <div
                class="flex flex-wrap gap-1 bg-gray-900/70 p-1.5 rounded-xl border border-gray-800/80 text-xs shadow-md"
            >
                {#each ['all', 'draft', 'approved', 'published', 'failed'] as const as f}
                    <button
                        onclick={() => (filter = f)}
                        class="px-3 py-1.5 rounded-lg font-semibold transition-all duration-200 capitalize cursor-pointer
                            {filter === f
                            ? 'bg-gray-800 text-gray-100 shadow-sm border border-gray-700/60'
                            : 'text-gray-400 hover:text-gray-200'}"
                    >
                        {f === 'all'
                            ? 'All'
                            : f === 'draft'
                              ? 'Drafts'
                              : f === 'approved'
                                ? 'Approved'
                                : f === 'published'
                                  ? 'Published'
                                  : 'Failed'}
                    </button>
                {/each}
            </div>
        </div>

        {#if filtered.length === 0}
            <div
                class="p-12 sm:p-16 text-center bg-gray-900/60 border border-gray-800/80 rounded-2xl shadow-xl"
            >
                <div class="text-4xl sm:text-5xl mb-4 opacity-30">📭</div>
                <p class="text-gray-400 font-medium text-sm">
                    No posts found for "{filter}" filter
                </p>
            </div>
        {:else}
            <div class="space-y-3">
                {#each filtered as post (post.id)}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 hover:border-indigo-500/40 p-3.5 sm:p-4 rounded-2xl flex flex-col gap-3 transition-all shadow-md group"
                    >
                        <!-- Top Row: Icon + Title + Status Badge -->
                        <div class="flex items-start justify-between gap-3">
                            <a
                                href="/drafts/{post.id}"
                                class="flex items-start gap-3 min-w-0 flex-1 group-hover:text-indigo-300 transition-colors"
                            >
                                <div
                                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-emerald-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0 text-base shadow-inner"
                                >
                                    🔗
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="font-bold text-sm text-gray-200 line-clamp-2 leading-snug break-words"
                                    >
                                        {post.product_title || 'Shopee Product Deal'}
                                    </h3>
                                    <p
                                        class="text-xs text-indigo-400 font-mono truncate mt-0.5"
                                    >
                                        {post.affiliate_url}
                                    </p>
                                    <p
                                        class="text-[11px] text-gray-500 mt-0.5 font-mono"
                                    >
                                        {new Date(post.created_at || Date.now()).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                    </p>
                                </div>
                            </a>

                            <!-- Status Badge -->
                            <div class="flex-shrink-0">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] sm:text-[11px] font-semibold tracking-wide uppercase
                                    {post.status === 'published'
                                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                        : post.status === 'approved'
                                          ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20'
                                          : post.status === 'failed'
                                            ? 'bg-red-500/10 text-red-400 border border-red-500/20'
                                            : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'}"
                                >
                                    <span
                                        class="w-1.5 h-1.5 rounded-full
                                        {post.status === 'published'
                                            ? 'bg-emerald-400'
                                            : post.status === 'approved'
                                              ? 'bg-indigo-400 animate-ping'
                                              : post.status === 'failed'
                                                ? 'bg-red-400'
                                                : 'bg-amber-400 animate-pulse'}"
                                    ></span>
                                    {post.status === 'published'
                                        ? '✓ Published'
                                        : post.status === 'approved'
                                          ? '⌛ Approved'
                                          : post.status === 'failed'
                                            ? '❌ Failed'
                                            : '✎ Draft'}
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Row: Action Toolbar (View on FB, Edit Draft, Delete) -->
                        <div class="pt-2 border-t border-gray-800/60 flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                {#if post.facebook_post_url}
                                    <a
                                        href={post.facebook_post_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 hover:text-blue-300 border border-blue-500/20 text-xs font-semibold shadow-sm transition-all active:scale-95 cursor-pointer"
                                        title="Open live post on Facebook"
                                    >
                                        <span>🌐 View on FB ↗</span>
                                    </a>
                                {/if}

                                <button
                                    onclick={(e) => handleDeletePost(post.id, e)}
                                    class="p-1.5 px-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 text-xs font-semibold transition-all active:scale-95 cursor-pointer"
                                    title="Delete post record"
                                >
                                    🗑️
                                </button>
                            </div>

                            <a
                                href="/drafts/{post.id}"
                                class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 hover:underline flex items-center gap-1 ml-auto transition-colors py-1 cursor-pointer"
                            >
                                <span>Open Details</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>
