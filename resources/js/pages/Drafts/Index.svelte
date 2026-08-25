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

    function handlePublish(id: string) {
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

<AppLayout title="Post Drafts">
    <div class="px-4 py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Header Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                    >
                        Post Drafts & Media Editor
                    </span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Manage, edit with live Facebook preview, and publish your affiliate drafts
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a
                    href="/create"
                    class="px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-200 hover:text-white rounded-xl text-xs font-bold border border-gray-700 transition-all cursor-pointer flex items-center gap-1.5"
                >
                    <span>🔗 Paste Shopee Link</span>
                </a>
                <button
                    onclick={() => (showModal = true)}
                    class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-1.5"
                >
                    <span>+ New Draft</span>
                </button>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        {#if posts.length > 0}
            <div class="flex items-center justify-between gap-3 bg-gray-900/60 p-3 rounded-2xl border border-gray-800/80 backdrop-blur-xl">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        bind:value={searchQuery}
                        placeholder="Search drafts by title, keyword, or ID..."
                        class="w-full bg-gray-950 border border-gray-800 rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-500 outline-none focus:border-indigo-500/60"
                    />
                    {#if searchQuery}
                        <button
                            type="button"
                            onclick={() => (searchQuery = '')}
                            class="absolute right-3 top-2 text-gray-500 hover:text-white text-xs cursor-pointer"
                        >
                            ✕
                        </button>
                    {/if}
                </div>
                <div class="text-xs text-gray-400 font-mono hidden sm:block px-2">
                    {filteredPosts.length} / {posts.length} draft(s)
                </div>
            </div>
        {/if}

        <!-- Draft Cards Grid -->
        {#if filteredPosts.length === 0}
            <div
                class="p-12 text-center border border-gray-800/80 rounded-2xl bg-gray-900/60 backdrop-blur-xl space-y-3"
            >
                <div
                    class="w-12 h-12 rounded-xl bg-gray-800/60 flex items-center justify-center mx-auto text-2xl"
                >
                    📝
                </div>
                <p class="text-gray-300 font-semibold text-sm">
                    {searchQuery ? 'No drafts matching your search query.' : 'No post drafts available.'}
                </p>
                <p class="text-xs text-gray-500">
                    Paste a Shopee product link or click "+ New Draft" to create one with AI copy.
                </p>
                <div class="pt-2 flex justify-center gap-3">
                    <a
                        href="/create"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold"
                    >
                        Extract Shopee Link
                    </a>
                </div>
            </div>
        {:else}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {#each filteredPosts as post (post.id)}
                    {@const mediaCount = Array.isArray(post.media_files) ? post.media_files.length : (post.media_files ? 1 : 0)}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl rounded-2xl border border-gray-800/80 p-5 flex flex-col justify-between hover:border-indigo-500/50 transition-all shadow-xl group hover:shadow-indigo-500/5"
                    >
                        <div>
                            <!-- Card Top Badges -->
                            <div class="flex items-center justify-between mb-3">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide uppercase
                                        {post.status === 'published' ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400' : 'bg-amber-500/10 border border-amber-500/20 text-amber-400'}"
                                >
                                    {post.status || 'draft'}
                                </span>
                                <div class="flex items-center gap-2">
                                    {#if mediaCount > 0}
                                        <span class="text-[11px] text-gray-400 bg-gray-950/80 px-2 py-0.5 rounded-md border border-gray-800">
                                            🖼️ {mediaCount}
                                        </span>
                                    {/if}
                                    <span class="text-[11px] text-gray-500 font-mono">ID: {post.id}</span>
                                </div>
                            </div>

                            <!-- Clickable Title linking to detail editor -->
                            <a
                                href="/drafts/{post.id}"
                                class="block text-base font-bold text-gray-100 group-hover:text-indigo-300 transition-colors mb-1 line-clamp-2 leading-snug cursor-pointer"
                            >
                                {post.product_title || 'Shopee Sulit Deal'}
                            </a>

                            <!-- Price Pill -->
                            {#if post.product_price}
                                <div class="inline-block bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md text-[11px] font-bold text-emerald-400 font-mono mb-2">
                                    💰 {post.product_price}
                                </div>
                            {/if}

                            <!-- Affiliate Link -->
                            <p class="text-[11px] text-indigo-400 font-mono truncate mb-3">
                                🔗 {post.affiliate_url}
                            </p>

                            <!-- Caption Preview Snippet -->
                            <a
                                href="/drafts/{post.id}"
                                class="block text-xs text-gray-300 line-clamp-3 bg-gray-950/60 hover:bg-gray-950 p-3 rounded-xl border border-gray-800/60 mb-4 font-sans leading-relaxed transition-colors cursor-pointer"
                            >
                                {post.caption || 'No caption generated yet. Click to write or generate with AI.'}
                            </a>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-800/60 mt-2">
                            <a
                                href="/drafts/{post.id}"
                                class="px-3 py-1.5 bg-gray-800 hover:bg-indigo-600/30 text-gray-300 hover:text-indigo-200 border border-gray-700/80 hover:border-indigo-500/40 rounded-xl text-xs font-semibold transition-all flex items-center gap-1"
                            >
                                <span>✏️ Edit & Preview</span>
                            </a>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    onclick={() => handleGenerateCaption(post.id)}
                                    disabled={generatingId === post.id}
                                    class="p-2 rounded-xl text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 transition-colors cursor-pointer disabled:opacity-50"
                                    title="Regenerate viral AI caption"
                                >
                                    {#if generatingId === post.id}
                                        <span class="animate-spin text-xs">🌀</span>
                                    {:else}
                                        <span>⚡</span>
                                    {/if}
                                </button>

                                <button
                                    type="button"
                                    onclick={() => handlePublish(post.id)}
                                    disabled={publishingId === post.id}
                                    class="p-2 rounded-xl text-emerald-400 hover:text-emerald-300 hover:bg-emerald-500/10 transition-colors cursor-pointer disabled:opacity-50"
                                    title="Publish to Facebook Page"
                                >
                                    {#if publishingId === post.id}
                                        <span class="animate-spin text-xs">🌀</span>
                                    {:else}
                                        <span>🚀</span>
                                    {/if}
                                </button>

                                <button
                                    type="button"
                                    onclick={() => handleDelete(post.id)}
                                    class="p-2 rounded-xl text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer"
                                    title="Delete Draft"
                                >
                                    🗑️
                                </button>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        {/if}

        <!-- New Draft Modal -->
        {#if showModal}
            <div
                class="fixed inset-0 bg-black/75 backdrop-blur-md flex items-center justify-center p-4 z-50 animate-fadeIn"
            >
                <div
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-gray-800">
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>✨</span> Create New AI Draft
                        </h2>
                        <button
                            type="button"
                            onclick={() => (showModal = false)}
                            class="text-gray-400 hover:text-white"
                        >✕</button>
                    </div>

                    <form onsubmit={handleCreate} class="space-y-4">
                        <div>
                            <label
                                for="modal_url"
                                class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1"
                                >Shopee Affiliate Link URL <span class="text-red-400">*</span></label
                            >
                            <input
                                id="modal_url"
                                type="url"
                                bind:value={affiliate_url}
                                placeholder="https://s.shopee.ph/..."
                                required
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    for="modal_title"
                                    class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                    >Title (Optional)</label
                                >
                                <input
                                    id="modal_title"
                                    type="text"
                                    bind:value={product_title}
                                    placeholder="e.g. Wireless Earbuds"
                                    class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>
                            <div>
                                <label
                                    for="modal_price"
                                    class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                    >Price (Optional)</label
                                >
                                <input
                                    id="modal_price"
                                    type="text"
                                    bind:value={product_price}
                                    placeholder="e.g. ₱599"
                                    class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-1.5">AI Copywriting Style</span>
                            <div class="grid grid-cols-2 gap-2">
                                {#each [
                                    { id: 'viral_ai', label: '✨ Viral Hook', desc: 'FOMO & urgency' },
                                    { id: 'pinoy_taglish', label: '🇵🇭 Pinoy Tropa', desc: 'Budol vibes' },
                                    { id: 'specs_tech', label: '💻 Tech Specs', desc: 'Detailed specs' },
                                    { id: 'urgency_flash', label: '🚨 Flash Sale', desc: 'Price drop alert' },
                                ] as style}
                                    <button
                                        type="button"
                                        onclick={() => (captionStyle = style.id)}
                                        class="p-2 rounded-xl text-left border text-xs cursor-pointer transition-colors
                                            {captionStyle === style.id
                                            ? 'bg-indigo-500/20 border-indigo-500 text-indigo-200 font-semibold'
                                            : 'bg-gray-950 border-gray-800 text-gray-400 hover:text-white'}"
                                    >
                                        <div>{style.label}</div>
                                        <div class="text-[10px] opacity-70">{style.desc}</div>
                                    </button>
                                {/each}
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-3 border-t border-gray-800">
                            <button
                                type="button"
                                onclick={() => (showModal = false)}
                                class="px-4 py-2 bg-gray-800 text-gray-300 hover:text-white rounded-xl text-xs font-medium cursor-pointer"
                                >Cancel</button
                            >
                            <button
                                type="submit"
                                disabled={creating || !affiliate_url.trim()}
                                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-emerald-500 text-white rounded-xl text-xs font-bold shadow-md cursor-pointer disabled:opacity-50 flex items-center gap-1.5"
                            >
                                {#if creating}
                                    <span class="animate-spin">🌀</span>
                                    <span>Generating...</span>
                                {:else}
                                    <span>✨ Create with AI</span>
                                {/if}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        {/if}
    </div>
</AppLayout>
