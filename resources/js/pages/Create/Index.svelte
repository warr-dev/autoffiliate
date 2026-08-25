<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let url = $state('');
    let product_title = $state('');
    let product_price = $state('');
    let captionStyle = $state('viral_ai');
    let loading = $state(false);
    let error = $state('');

    function handleExtract(e: SubmitEvent) {
        e.preventDefault();

        if (!url.trim()) {
            return;
        }

        loading = true;
        error = '';

        router.post(
            '/drafts',
            {
                product_title: product_title.trim() || 'Shopee Sulit Deal',
                product_price: product_price.trim(),
                affiliate_url: url.trim(),
                caption_style: captionStyle,
            },
            {
                onError: (err) => {
                    error = Object.values(err)[0] || 'Draft creation failed or invalid link';
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
                    Create Post & AI Deal Generator
                </span>
            </h1>
            <p class="text-gray-400 text-sm mt-1">
                Paste a Shopee PH affiliate link to generate a high-converting AI draft post
            </p>
        </div>

        <div
            class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6"
        >
            <form onsubmit={handleExtract} class="space-y-6">
                <!-- Affiliate Link Field -->
                <div>
                    <label
                        for="url"
                        class="block text-xs font-semibold text-gray-300 uppercase tracking-wider mb-2"
                        >Shopee PH Affiliate Link <span class="text-red-400">*</span></label
                    >
                    <div class="relative">
                        <input
                            id="url"
                            type="url"
                            bind:value={url}
                            placeholder="https://s.shopee.ph/... or https://shopee.ph/product/..."
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-sm text-white focus:border-indigo-500 focus:outline-none pr-10"
                            disabled={loading}
                            required
                        />
                        {#if url}
                            <button
                                type="button"
                                onclick={() => (url = '')}
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 text-lg cursor-pointer"
                                >×</button
                            >
                        {/if}
                    </div>
                </div>

                <!-- Product Title & Price (Optional) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="product_title"
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5"
                            >Product Title <span class="text-gray-500 font-normal">(Optional)</span></label
                        >
                        <input
                            id="product_title"
                            type="text"
                            bind:value={product_title}
                            placeholder="e.g. Anker 65W GaN Fast Charger"
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-white focus:border-indigo-500 focus:outline-none"
                            disabled={loading}
                        />
                    </div>
                    <div>
                        <label
                            for="product_price"
                            class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5"
                            >Price / Discount <span class="text-gray-500 font-normal">(Optional)</span></label
                        >
                        <input
                            id="product_price"
                            type="text"
                            bind:value={product_price}
                            placeholder="e.g. ₱899 (50% OFF)"
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-white focus:border-indigo-500 focus:outline-none"
                            disabled={loading}
                        />
                    </div>
                </div>

                <!-- AI Copywriting & Caption Style -->
                <div class="space-y-2">
                    <span
                        class="block text-xs font-semibold text-gray-300 uppercase tracking-wider"
                        >AI Copywriting & Tone Style</span
                    >
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        {#each [
                            { id: 'viral_ai', label: '✨ Viral AI Hook', desc: 'High conversion & FOMO' },
                            { id: 'pinoy_taglish', label: '🇵🇭 Pinoy Tropa', desc: "Casual Taglish 'Budol' vibe" },
                            { id: 'specs_tech', label: '💻 Tech Specs', desc: 'Specs & performance breakdown' },
                            { id: 'urgency_flash', label: '🚨 Flash Sale', desc: 'High urgency voucher alert' },
                            { id: 'review_story', label: '⭐ Personal Review', desc: 'Warm 10/10 recommendation' },
                            { id: 'aesthetic', label: '🌸 Aesthetic Vibe', desc: 'Clean lifestyle presentation' },
                            { id: 'minimal', label: '📄 Minimalist', desc: 'Clean 3-line punchy callout' },
                            { id: 'standard', label: '🔥 Standard Deal', desc: 'Classic deal alert post' },
                        ] as style}
                            <button
                                type="button"
                                onclick={() => (captionStyle = style.id)}
                                class="p-3 rounded-xl text-left border transition-all flex flex-col gap-0.5 cursor-pointer
                                    {captionStyle === style.id
                                    ? 'bg-indigo-500/20 border-indigo-500 text-indigo-200 shadow-md shadow-indigo-500/10 font-semibold'
                                    : 'bg-gray-950/60 border-gray-800/80 text-gray-400 hover:text-gray-200 hover:border-gray-700'}"
                            >
                                <span class="text-xs text-white"
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
                        class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs"
                    >
                        {error}
                    </div>
                {/if}

                <!-- Submit Button -->
                <button
                    type="submit"
                    disabled={loading || !url.trim()}
                    class="w-full py-3.5 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    {#if loading}
                        <span class="animate-spin">🌀</span>
                        <span>Generating AI Copy & Draft...</span>
                    {:else}
                        <span>✨ Generate AI Draft Post</span>
                    {/if}
                </button>

                <div
                    class="p-4 rounded-xl bg-gray-950/50 border border-gray-800/60"
                >
                    <h3
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                    >
                        How it works
                    </h3>
                    <ol
                        class="text-xs text-gray-500 space-y-1.5 list-decimal list-inside leading-relaxed"
                    >
                        <li>Paste your Shopee affiliate link (shortlink or full URL).</li>
                        <li>AI generates high-converting sales copy in your chosen style.</li>
                        <li>Hashtags and affiliate disclosure are automatically attached.</li>
                        <li>Review and publish directly to Facebook with one click.</li>
                    </ol>
                </div>
            </form>
        </div>
    </div>
</AppLayout>
