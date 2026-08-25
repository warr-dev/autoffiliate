<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let { posts = [] } = $props<{ posts: Array<any> }>();

    let showModal = $state(false);
    let product_title = $state('');
    let affiliate_url = $state('');
    let caption = $state('');

    function handleCreate(e: SubmitEvent) {
        e.preventDefault();
        router.post(
            '/drafts',
            { product_title, affiliate_url, caption },
            {
                onSuccess: () => {
                    showModal = false;
                    product_title = '';
                    affiliate_url = '';
                    caption = '';
                },
            },
        );
    }

    let publishingId = $state<string | null>(null);
    let generatingId = $state<string | null>(null);

    function handlePublish(id: string) {
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

    function handleGenerateCaption(id: string, style = 'viral_ai') {
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

    function handleDelete(id: string) {
        if (confirm('Are you sure you want to delete this draft?')) {
            router.delete(`/drafts/${id}`);
        }
    }
</script>

<AppLayout title="Drafts">
    <div class="px-4 py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                    >
                        Post Drafts
                    </span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Review and manage your pending affiliate content drafts
                </p>
            </div>
            <button
                onclick={() => (showModal = true)}
                class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer"
            >
                New Draft
            </button>
        </div>

        {#if posts.length === 0}
            <div
                class="p-12 text-center border border-gray-800/80 rounded-2xl bg-gray-900/60 backdrop-blur-xl"
            >
                <div
                    class="w-12 h-12 rounded-xl bg-gray-800/60 flex items-center justify-center mx-auto mb-3 text-2xl"
                >
                    📝
                </div>
                <p class="text-gray-400 font-medium">
                    No post drafts available.
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Create a new draft or paste a link to get started.
                </p>
            </div>
        {:else}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {#each posts as post (post.id)}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl rounded-2xl border border-gray-800/80 p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all shadow-xl"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/10 border border-amber-500/20 text-amber-400"
                                >
                                    {post.status}
                                </span>
                                <span
                                    class="text-[11px] text-gray-500 font-mono"
                                    >ID: {post.id}</span
                                >
                            </div>
                            <h3
                                class="text-base font-bold text-gray-100 mb-1 line-clamp-2"
                            >
                                {post.product_title}
                            </h3>
                            <p
                                class="text-xs text-indigo-400 font-mono truncate mb-3"
                            >
                                {post.affiliate_url}
                            </p>
                            <p
                                class="text-xs text-gray-400 line-clamp-3 bg-gray-950/50 p-3 rounded-xl border border-gray-800/40 mb-4 font-sans leading-relaxed"
                            >
                                {post.caption || 'No caption generated'}
                            </p>
                        </div>
                        <div
                            class="flex items-center justify-between pt-3 border-t border-gray-800/60 mt-2"
                        >
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    onclick={() => handlePublish(post.id)}
                                    disabled={publishingId === post.id}
                                    class="p-2 rounded-lg text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 transition-colors cursor-pointer disabled:opacity-50"
                                    title="Publish / Post to Facebook Page"
                                >
                                    {#if publishingId === post.id}
                                        <div
                                            class="loading-spinner w-4 h-4"
                                        ></div>
                                    {:else}
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                                            />
                                        </svg>
                                    {/if}
                                </button>
                                <button
                                    type="button"
                                    onclick={() =>
                                        handleGenerateCaption(post.id)}
                                    disabled={generatingId === post.id}
                                    class="p-2 rounded-lg text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 transition-colors cursor-pointer disabled:opacity-50"
                                    title="Generate viral AI caption"
                                >
                                    {#if generatingId === post.id}
                                        <div
                                            class="loading-spinner w-4 h-4"
                                        ></div>
                                    {:else}
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z"
                                            />
                                        </svg>
                                    {/if}
                                </button>
                            </div>
                            <button
                                type="button"
                                onclick={() => handleDelete(post.id)}
                                class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer"
                                title="Delete Draft"
                            >
                                <svg
                                    class="w-4 h-4"
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

        {#if showModal}
            <div
                class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center p-4 z-50"
            >
                <div
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-md w-full shadow-2xl"
                >
                    <h2 class="text-lg font-bold text-white mb-4">
                        Create New Draft
                    </h2>
                    <form onsubmit={handleCreate} class="space-y-4">
                        <div>
                            <label
                                for="product_title"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Product Title</label
                            >
                            <input
                                id="product_title"
                                type="text"
                                bind:value={product_title}
                                required
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label
                                for="affiliate_url"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Affiliate Link URL</label
                            >
                            <input
                                id="affiliate_url"
                                type="url"
                                bind:value={affiliate_url}
                                required
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label
                                for="caption"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Caption</label
                            >
                            <textarea
                                id="caption"
                                bind:value={caption}
                                rows="3"
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            ></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 pt-2">
                            <button
                                type="button"
                                onclick={() => (showModal = false)}
                                class="px-4 py-2 bg-gray-800 text-gray-300 rounded-xl text-xs font-medium hover:bg-gray-700"
                                >Cancel</button
                            >
                            <button
                                type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-emerald-500 text-white rounded-xl text-xs font-semibold shadow-md"
                                >Save Draft</button
                            >
                        </div>
                    </form>
                </div>
            </div>
        {/if}
    </div>
</AppLayout>
