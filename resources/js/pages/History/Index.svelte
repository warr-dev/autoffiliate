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
                class="p-16 text-center bg-gray-900/60 border border-gray-800/80 rounded-2xl shadow-xl"
            >
                <div class="text-5xl mb-4 opacity-30">📭</div>
                <p class="text-gray-400 font-medium">
                    No posts found for "{filter}" filter
                </p>
            </div>
        {:else}
            <div class="space-y-2">
                {#each filtered as post (post.id)}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 hover:border-indigo-500/40 p-4 rounded-2xl flex items-center justify-between transition-all shadow-md"
                    >
                        <div class="flex items-center gap-4 min-w-0">
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
                                    class="text-xs text-indigo-400 font-mono truncate mt-0.5"
                                >
                                    {post.affiliate_url}
                                </p>
                                <p
                                    class="text-[11px] text-gray-500 mt-0.5 font-mono"
                                >
                                    Created: {post.created_at || 'Just now'}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                            {#if post.facebook_post_url}
                                <a
                                    href={post.facebook_post_url}
                                    target="_blank"
                                    class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                                >
                                    View on FB ↗
                                </a>
                            {/if}
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide uppercase
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
                            <button
                                onclick={(e) => handleDeletePost(post.id, e)}
                                class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-red-400 rounded-lg hover:bg-red-500/10 border border-gray-800 hover:border-red-500/30 transition-colors cursor-pointer"
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
</AppLayout>
