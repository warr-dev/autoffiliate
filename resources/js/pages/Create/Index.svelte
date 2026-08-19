<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router } from '@inertiajs/svelte';

    let url = $state('');
    let loading = $state(false);
    let error = $state('');
    let captionStyle = $state('standard');

    function handleExtract(e: SubmitEvent) {
        e.preventDefault();
        if (!url.trim()) return;
        loading = true;
        error = '';

        router.post(
            '/drafts',
            {
                product_title: 'Shopee Deal',
                affiliate_url: url.trim(),
                caption: `Caption Style: ${captionStyle}`,
            },
            {
                onError: (err) => {
                    error = 'Extraction failed or invalid link';
                    loading = false;
                },
                onFinish: () => {
                    loading = false;
                },
            },
        );
    }
</script>

<AppLayout title="Create Post">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="mb-8">
            <a
                href="/drafts"
                class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors mb-3 inline-block"
                >← Back to Drafts</a
            >
            <h1 class="text-3xl font-extrabold tracking-tight">
                <span
                    class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                >
                    Create Post & Link Extractor
                </span>
            </h1>
            <p class="text-gray-400 text-sm mt-1">
                Paste a Shopee PH affiliate link to extract media and generate a
                draft
            </p>
        </div>

        <div
            class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl"
        >
            <form onsubmit={handleExtract}>
                <div class="mb-6">
                    <label
                        for="url"
                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                        >Shopee PH Affiliate Link</label
                    >
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1 relative">
                            <input
                                id="url"
                                type="url"
                                bind:value={url}
                                placeholder="https://s.shopee.ph/..."
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-sm text-white focus:border-indigo-500 focus:outline-none"
                                disabled={loading}
                                required
                            />
                            {#if url}
                                <button
                                    type="button"
                                    onclick={() => {
                                        url = '';
                                    }}
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 text-lg"
                                    >×</button
                                >
                            {/if}
                        </div>
                        <button
                            type="submit"
                            disabled={loading || !url.trim()}
                            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            {#if loading}
                                Extracting...
                            {:else}
                                Extract →
                            {/if}
                        </button>
                    </div>
                </div>

                <div class="mb-6 space-y-2">
                    <label
                        class="block text-xs font-semibold text-gray-400 uppercase tracking-wider"
                        >AI Copywriting & Caption Style</label
                    >
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        {#each [{ id: 'standard', label: '🔥 Standard Deal', desc: 'Classic deal alert post' }, { id: 'viral_ai', label: '✨ Viral AI Hook', desc: 'High conversion & engagement' }, { id: 'pinoy_taglish', label: '🇵🇭 Pinoy Tropa', desc: "Casual Taglish 'Budol' vibe" }, { id: 'specs_tech', label: '💻 Tech Specs', desc: 'Technical specs & breakdown' }, { id: 'review_story', label: '⭐ Personal Review', desc: 'Warm recommendation' }, { id: 'aesthetic', label: '🌸 Aesthetic Vibe', desc: 'Clean lifestyle presentation' }, { id: 'urgency_flash', label: '🚨 Flash Sale', desc: 'High-urgency voucher alert' }, { id: 'minimal', label: '📄 Minimalist', desc: 'Clean 3-line link callout' }] as style}
                            <button
                                type="button"
                                onclick={() => (captionStyle = style.id)}
                                class="p-3 rounded-xl text-left border transition-all flex flex-col gap-0.5 cursor-pointer
                                    {captionStyle === style.id
                                    ? 'bg-indigo-500/15 border-indigo-500 text-indigo-300 shadow-md shadow-indigo-500/10'
                                    : 'bg-gray-950/60 border-gray-800/80 text-gray-400 hover:text-gray-200 hover:border-gray-700'}"
                            >
                                <span class="text-xs font-bold text-white"
                                    >{style.label}</span
                                >
                                <span class="text-[10px] opacity-70"
                                    >{style.desc}</span
                                >
                            </button>
                        {/each}
                    </div>
                </div>

                {#if error}
                    <div
                        class="mt-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs"
                    >
                        {error}
                    </div>
                {/if}

                <div
                    class="mt-6 p-4 rounded-xl bg-gray-950/50 border border-gray-800/60"
                >
                    <h3
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                    >
                        How it works
                    </h3>
                    <ol
                        class="text-xs text-gray-500 space-y-1.5 list-decimal list-inside leading-relaxed"
                    >
                        <li>
                            Paste a Shopee PH affiliate link (shortlink or full
                            URL)
                        </li>
                        <li>Media is automatically extracted and downloaded</li>
                        <li>A viral deal draft is generated for you</li>
                        <li>
                            Review, edit, and approve to publish to Facebook
                        </li>
                    </ol>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
