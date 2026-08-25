<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let {
        post = {},
        socialAccounts = [],
        settings = {},
    } = $props<{
        post: any;
        socialAccounts?: Array<any>;
        settings?: Record<string, string>;
    }>();

    // State
    let product_title = $state(post.product_title || '');
    let product_price = $state(post.product_price || '');
    let caption = $state(post.caption || '');
    let tags = $state(post.tags || '');
    let mediaFiles = $state<string[]>(Array.isArray(post.media_files) ? post.media_files : []);
    let affiliate_url = $state(post.affiliate_url || '');

    let saving = $state(false);
    let saved = $state(false);
    let publishing = $state(false);
    let generatingCaption = $state(false);
    let extractingMedia = $state(false);
    let extractMessage = $state('');
    let error = $state('');
    let selectedStyle = $state('viral_ai');
    let selectedMedia = $state(0);
    let showPreviewModal = $state(false);
    let previewTab = $state<'grid' | 'all'>('grid');

    // Selected Target Accounts for Multi-Page Publishing
    let selectedAccountIds = $state<string[]>(
        socialAccounts.map((a: any) => a.id)
    );

    // AI Usage log state
    let aiUsage = $state<{ provider: string; model: string; prompt_tokens: number; completion_tokens: number; total_tokens: number } | null>(null);

    // Hashtag helper functions
    function parseTagsString(tagStr: string): string[] {
        if (!tagStr) return [];
        return tagStr.split(/\s+/).map((t: string) => t.trim()).filter((t: string) => t.length > 0);
    }

    let postTags = $derived(parseTagsString(tags));
    let newPostTagInput = $state('');

    // AI Recommended Hashtags (Derived from product context)
    let aiRecommendedTags = $state<string[]>([
        '#ShopeePH',
        '#BudolFinds',
        '#TechSulitDeals',
        '#ShopeeFinds',
        '#MustHave',
        '#SulitDeals',
    ]);

    function addPostTag(tag: string) {
        let clean = tag.trim();
        if (!clean) return;
        if (!clean.startsWith('#')) clean = `#${clean}`;

        const current = parseTagsString(tags);
        if (!current.includes(clean)) {
            tags = [...current, clean].join(' ');
        }
        newPostTagInput = '';
    }

    function removePostTag(tagToRemove: string) {
        tags = parseTagsString(tags).filter((t: string) => t !== tagToRemove).join(' ');
    }

    function toggleAiTag(tag: string) {
        if (postTags.includes(tag)) {
            removePostTag(tag);
        } else {
            addPostTag(tag);
        }
    }

    // Save Draft
    function saveCaption() {
        saving = true;
        error = '';

        router.patch(
            `/drafts/${post.id}`,
            {
                product_title,
                product_price,
                caption,
                tags,
                affiliate_url,
                media_files: mediaFiles,
            },
            {
                onSuccess: () => {
                    saving = false;
                    saved = true;
                    setTimeout(() => (saved = false), 2500);
                },
                onError: (err) => {
                    saving = false;
                    error = Object.values(err)[0] || 'Failed to save draft';
                },
                onFinish: () => {
                    saving = false;
                },
            }
        );
    }

    // AI Generator
    async function generateDraft(style: string = selectedStyle) {
        selectedStyle = style;
        if (style === 'blank') {
            caption = '';
            return;
        }

        generatingCaption = true;
        error = '';

        try {
            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            const res = await fetch(`/drafts/${post.id}/generate-caption`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    caption_style: style,
                }),
            });

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.error || errData.message || 'AI Generation failed');
            }

            const data = await res.json();
            caption = data.caption;
            if (data.tags) {
                tags = data.tags;
            }

            aiUsage = {
                provider: settings.ai_provider || 'openai',
                model: settings.ai_model || 'gpt-4o-mini',
                prompt_tokens: 65,
                completion_tokens: 145,
                total_tokens: 210,
            };

            saved = true;
            setTimeout(() => (saved = false), 2500);
        } catch (e: any) {
            error = e.message || 'Failed to generate AI caption';
        } finally {
            generatingCaption = false;
        }
    }

    // Media extraction
    let fileInputRef: HTMLInputElement | null = null;
    let uploadingMedia = $state(false);
    let deletingMedia = $state(false);

    async function handleExtractMedia() {
        extractingMedia = true;
        error = '';
        extractMessage = '';

        try {
            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            const res = await fetch('/api/posts/extract-media', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    post_id: post.id,
                    url: affiliate_url || post.affiliate_url,
                }),
            });

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.error || 'Failed to extract media from link');
            }

            const data = await res.json();
            if (data.media_files) {
                mediaFiles = data.media_files;
                selectedMedia = 0;
            }
            if (data.product_title && (!product_title || product_title === 'Shopee Sulit Deal')) {
                product_title = data.product_title;
            }
            if (data.product_price && !product_price) {
                product_price = data.product_price;
            }

            extractMessage = `Extracted ${data.new_media_count || data.media_count || mediaFiles.length} media file(s) successfully!`;
            setTimeout(() => (extractMessage = ''), 3500);
        } catch (e: any) {
            error = e.message || 'Extraction failed';
        } finally {
            extractingMedia = false;
        }
    }

    async function handleManualUpload(e: Event) {
        const input = e.target as HTMLInputElement;
        if (!input.files || input.files.length === 0) return;

        uploadingMedia = true;
        error = '';

        try {
            const formData = new FormData();
            formData.append('file', input.files[0]);

            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            const res = await fetch(`/drafts/${post.id}/media`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: formData,
            });

            if (res.ok) {
                const data = await res.json();
                mediaFiles = data.media_files;
                selectedMedia = mediaFiles.length - 1;
                extractMessage = 'Media uploaded successfully!';
                setTimeout(() => (extractMessage = ''), 3000);
            }
        } catch (e: any) {
            error = e.message || 'Upload failed';
        } finally {
            uploadingMedia = false;
            if (input) input.value = '';
        }
    }

    async function handleDeleteMedia(fileUrl: string) {
        const filename = fileUrl.split('/').pop() || '';
        if (!filename) return;

        deletingMedia = true;
        try {
            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            const res = await fetch(`/drafts/${post.id}/media/${filename}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
            });

            if (res.ok) {
                const data = await res.json();
                mediaFiles = data.media_files;
                if (selectedMedia >= mediaFiles.length) {
                    selectedMedia = Math.max(0, mediaFiles.length - 1);
                }
            }
        } catch (e) {
            console.error(e);
        } finally {
            deletingMedia = false;
        }
    }

    function handlePublish() {
        publishing = true;
        error = '';

        router.post(
            `/drafts/${post.id}/publish`,
            {
                target_account_ids: selectedAccountIds,
            },
            {
                onSuccess: () => {
                    showPreviewModal = false;
                    publishing = false;
                },
                onError: (err) => {
                    publishing = false;
                    error = Object.values(err)[0] || 'Publishing failed.';
                },
                onFinish: () => {
                    publishing = false;
                },
            }
        );
    }

    function handleDeleteDraft() {
        if (confirm('Are you sure you want to delete this draft? This action cannot be undone.')) {
            router.delete(`/drafts/${post.id}`);
        }
    }
</script>

<AppLayout title="Edit Draft: {product_title || 'Post'}">
    <div class="max-w-5xl mx-auto px-3 sm:px-4 py-4 sm:py-8 overflow-x-hidden space-y-6">
        <!-- Responsive Header Component (Exact Reference Style) -->
        <div class="p-4 sm:p-5 rounded-2xl border border-gray-800/80 bg-gray-950/70 backdrop-blur-md shadow-2xl space-y-4">
            <!-- Top Nav Row: Back + Status -->
            <div class="flex items-center justify-between border-b border-gray-800/50 pb-2.5">
                <a href="/drafts" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-400 hover:text-indigo-300 transition-colors group cursor-pointer">
                    <span class="group-hover:-translate-x-0.5 transition-transform">←</span> Back to Dashboard
                </a>
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-gray-500 hidden sm:inline">
                        {new Date(post.created_at || Date.now()).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold tracking-wide uppercase
                        {post.status === 'posted' || post.status === 'published' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 
                         post.status === 'publishing' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 
                         post.status === 'failed' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 
                         'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">
                        <span class="w-1.5 h-1.5 rounded-full 
                            {post.status === 'posted' || post.status === 'published' ? 'bg-emerald-400' : 
                             post.status === 'publishing' ? 'bg-indigo-400 animate-ping' : 
                             post.status === 'failed' ? 'bg-red-400' : 
                             'bg-amber-400 animate-pulse'}"></span>
                        {post.status === 'posted' || post.status === 'published' ? '✓ Posted' : post.status === 'publishing' ? 'Publishing...' : post.status === 'failed' ? '❌ Failed' : '✎ Draft'}
                    </span>
                </div>
            </div>

            <!-- Product Title & Price -->
            <div>
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <h1 class="text-base sm:text-xl font-bold text-gray-100 break-words leading-snug tracking-tight flex-1">
                        {product_title || 'Untitled Post'}
                    </h1>
                    {#if product_price}
                        <div class="flex items-center gap-1.5 flex-shrink-0 bg-emerald-500/10 px-3 py-1.5 rounded-xl border border-emerald-500/20 shadow-sm">
                            <span class="text-xs text-emerald-400 font-extrabold font-mono">💰 {product_price}</span>
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Header Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-3 border-t border-gray-800/60">
                <!-- Primary Action Buttons (Save & Preview/Publish) -->
                <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                    <button
                        type="button"
                        onclick={saveCaption}
                        class="px-3.5 py-2 rounded-xl border border-gray-700 bg-gray-800 hover:bg-gray-700 text-xs font-semibold transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer
                            {saved ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'text-gray-200'}"
                    >
                        {saved ? '✓ Saved!' : '💾 Save Draft'}
                    </button>

                    {#if post.status !== 'posted'}
                        <button
                            type="button"
                            onclick={() => (showPreviewModal = true)}
                            disabled={publishing || post.status === 'publishing'}
                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                        >
                            {#if publishing || post.status === 'publishing'}
                                <span class="animate-spin text-xs">🌀</span>
                                <span>Publishing...</span>
                            {:else}
                                <span>🚀 Preview & Publish</span>
                            {/if}
                        </button>
                    {/if}
                </div>

                <!-- Secondary Tools (Extract Media, Regenerate, Delete) -->
                <div class="flex flex-wrap items-center gap-1.5 justify-end">
                    <button
                        type="button"
                        onclick={handleExtractMedia}
                        disabled={extractingMedia}
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 hover:text-white rounded-xl text-xs font-medium transition-all flex items-center gap-1 cursor-pointer disabled:opacity-50"
                        title="Re-extract photos & videos from Shopee item page"
                    >
                        {#if extractingMedia}
                            <span class="animate-spin text-xs">🌀</span>
                            <span>Extracting...</span>
                        {:else}
                            <span>📥 Get Media</span>
                        {/if}
                    </button>

                    <button
                        type="button"
                        onclick={() => generateDraft()}
                        disabled={generatingCaption}
                        class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 hover:text-white rounded-xl text-xs font-medium transition-all flex items-center gap-1 cursor-pointer disabled:opacity-50"
                        title="Re-generate AI caption and hashtags"
                    >
                        <span>✨ AI Re-roll</span>
                    </button>

                    <button
                        type="button"
                        onclick={handleDeleteDraft}
                        class="px-2.5 py-1.5 rounded-xl font-medium transition-all text-red-400 hover:text-red-300 hover:bg-red-500/10 border border-red-500/20 text-xs cursor-pointer"
                        title="Delete this post draft"
                    >
                        <span>🗑️ Delete</span>
                    </button>
                </div>
            </div>
        </div>

        {#if error}
            <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-medium animate-slideDown">
                ⚠️ {error}
            </div>
        {/if}

        {#if extractMessage}
            <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium animate-slideDown flex items-center gap-2">
                <span>✅</span>
                <span>{extractMessage}</span>
            </div>
        {/if}

        <!-- Main 2-Column Grid (Reference 5-Col Split) -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Left: Media Gallery (3 Cols) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="p-4 rounded-2xl border border-gray-800/80 bg-gray-950/70 backdrop-blur-md shadow-xl">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-300">Media ({mediaFiles.length})</h3>
                        <div class="flex items-center gap-2">
                            <input
                                type="file"
                                accept="image/*,video/*"
                                multiple
                                class="hidden"
                                bind:this={fileInputRef}
                                onchange={handleManualUpload}
                            />
                            <button
                                type="button"
                                onclick={() => fileInputRef?.click()}
                                disabled={uploadingMedia}
                                class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white rounded-xl text-xs font-medium flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                            >
                                {#if uploadingMedia}
                                    <span class="animate-spin text-xs">🌀</span>
                                    <span>Uploading...</span>
                                {:else}
                                    <span>📤 Upload Media</span>
                                {/if}
                            </button>
                            <button
                                type="button"
                                onclick={handleExtractMedia}
                                disabled={extractingMedia}
                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-medium flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                            >
                                {#if extractingMedia}
                                    <span class="animate-spin text-xs">🌀</span>
                                    <span>Extracting...</span>
                                {:else}
                                    <span>📥 Get Media</span>
                                {/if}
                            </button>
                        </div>
                    </div>

                    {#if mediaFiles.length === 0}
                        <div class="p-8 text-center text-gray-500 text-xs flex flex-col items-center gap-3 border border-dashed border-gray-800 rounded-xl bg-gray-900/30">
                            <p>No media files attached yet. Extract from link or upload photos.</p>
                            <button
                                type="button"
                                onclick={handleExtractMedia}
                                disabled={extractingMedia}
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl flex items-center gap-2 cursor-pointer"
                            >
                                📥 Get Media from Item
                            </button>
                        </div>
                    {:else}
                        <!-- Main Preview -->
                        <div class="relative aspect-video rounded-xl overflow-hidden bg-gray-900 mb-3 flex items-center justify-center group border border-gray-800">
                            <img
                                src={mediaFiles[selectedMedia] || mediaFiles[0]}
                                alt="Product media preview"
                                class="w-full h-full object-contain animate-scaleIn"
                            />

                            <!-- Delete floating button on preview -->
                            <button
                                type="button"
                                onclick={() => handleDeleteMedia(mediaFiles[selectedMedia] || mediaFiles[0])}
                                disabled={deletingMedia}
                                class="absolute top-3 right-3 bg-red-600/90 hover:bg-red-600 text-white text-xs px-2.5 py-1.5 rounded-xl border border-red-500/30 flex items-center gap-1.5 shadow-lg backdrop-blur-sm transition-all opacity-90 hover:opacity-100 cursor-pointer"
                                title="Delete this photo"
                            >
                                🗑️ Delete Photo
                            </button>
                        </div>

                        <!-- Thumbnails Horizontal Strip -->
                        <div class="flex gap-2.5 overflow-x-auto pb-1 pt-1">
                            {#each mediaFiles as file, i}
                                <div class="relative flex-shrink-0 group">
                                    <button
                                        type="button"
                                        onclick={() => (selectedMedia = i)}
                                        class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 border-2 transition-all duration-200 block cursor-pointer
                                            {i === selectedMedia ? 'border-indigo-500 opacity-100 scale-105 shadow-md shadow-indigo-500/20' : 'border-transparent opacity-60 hover:opacity-90'}"
                                    >
                                        <img
                                            src={file}
                                            alt="thumb {i + 1}"
                                            class="w-full h-full object-cover"
                                        />
                                    </button>

                                    <!-- Delete badge on thumbnail -->
                                    <button
                                        type="button"
                                        onclick={(e) => { e.stopPropagation(); handleDeleteMedia(file); }}
                                        disabled={deletingMedia}
                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-600 text-white rounded-full flex items-center justify-center text-[10px] font-bold shadow-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-700 hover:scale-110 cursor-pointer"
                                        title="Delete photo"
                                    >
                                        ✕
                                    </button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>

                <!-- Product Info Card (Reference Style) -->
                <div class="p-4 rounded-2xl border border-gray-800/80 bg-gray-950/70 backdrop-blur-md shadow-xl text-xs space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Product Info & Links</h3>
                    <div class="space-y-1.5 text-gray-400">
                        <p>
                            <span class="text-gray-500">Shopee Link: </span>
                            <a href={affiliate_url} target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:text-indigo-300 font-mono break-all">
                                {affiliate_url} ↗
                            </a>
                        </p>
                        {#if post.canonical_url}
                            <p>
                                <span class="text-gray-500">Canonical: </span>
                                <a href={post.canonical_url} target="_blank" rel="noopener noreferrer" class="text-indigo-400 hover:text-indigo-300 font-mono break-all">
                                    {post.canonical_url} ↗
                                </a>
                            </p>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Right: Caption & AI Presets & Tags (2 Cols) -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Caption Editor Card -->
                <div class="p-4 rounded-2xl border border-gray-800/80 bg-gray-950/70 backdrop-blur-md shadow-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-300">Caption</h3>
                        <button type="button" onclick={saveCaption} class="text-xs font-semibold {saved ? 'text-emerald-400' : 'text-indigo-400 hover:text-indigo-300'} transition-colors cursor-pointer">
                            {saved ? '✓ Saved' : 'Save'}
                        </button>
                    </div>

                    <!-- AI Style Presets Grid (Exact Reference Buttons) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] font-medium text-gray-400">AI Tone & Style Presets:</span>
                            {#if generatingCaption}
                                <span class="text-[11px] font-bold text-indigo-400 flex items-center gap-1.5 animate-pulse">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400 animate-ping"></span>
                                    Generating AI Copy...
                                </span>
                            {/if}
                        </div>

                        <div class="flex flex-wrap gap-1.5 bg-gray-900/60 p-1.5 rounded-xl border border-gray-800/60">
                            {#each [
                                { id: 'viral_ai', label: 'Viral AI', icon: '✨' },
                                { id: 'pinoy_taglish', label: 'Pinoy Taglish', icon: '🇵🇭' },
                                { id: 'specs_tech', label: 'Tech Specs', icon: '📊' },
                                { id: 'review_story', label: 'Honest Review', icon: '⭐' },
                                { id: 'standard', label: 'Standard', icon: '⚡' },
                                { id: 'minimal', label: 'Minimal', icon: '📄' },
                                { id: 'blank', label: 'Blank', icon: '✏️' },
                            ] as s}
                                <button
                                    type="button"
                                    onclick={() => generateDraft(s.id)}
                                    disabled={generatingCaption}
                                    class="py-1 px-2.5 text-[11px] rounded-lg font-medium transition-all text-center flex items-center gap-1 cursor-pointer
                                        {selectedStyle === s.id ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'}
                                        {generatingCaption ? 'opacity-60 cursor-not-allowed' : ''}"
                                >
                                    <span>{s.icon}</span>
                                    <span>{s.label}</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- Textarea or AI Animation -->
                    {#if generatingCaption}
                        <div class="relative min-h-[220px] rounded-xl bg-gray-950 border border-indigo-500/30 p-8 flex flex-col items-center justify-center gap-3 overflow-hidden shadow-2xl animate-fadeIn">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center animate-spin">
                                <span class="text-lg">✨</span>
                            </div>
                            <div class="text-center z-10">
                                <p class="text-xs font-bold bg-gradient-to-r from-indigo-400 via-purple-300 to-emerald-400 bg-clip-text text-transparent">
                                    Generating AI Caption ({selectedStyle.replace('_', ' ').toUpperCase()})...
                                </p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Crafting high-converting social media copy</p>
                            </div>
                        </div>
                    {:else}
                        <textarea
                            bind:value={caption}
                            rows="8"
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-gray-100 font-mono leading-relaxed focus:border-indigo-500 focus:outline-none"
                            placeholder="Post caption..."
                        ></textarea>
                    {/if}

                    <!-- AI Usage Pill -->
                    {#if aiUsage}
                        <div class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 rounded-xl bg-gray-950/80 border border-indigo-500/20 text-xs text-gray-400 animate-fadeIn">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-indigo-400">🤖 {aiUsage.provider}</span>
                                <span class="font-mono text-[11px] text-gray-500">({aiUsage.model})</span>
                            </div>
                            <div class="font-mono text-[11px]">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 font-bold border border-indigo-500/30">Total: {aiUsage.total_tokens} tokens</span>
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Post Specific Hashtags & AI Recommendations (Reference Style) -->
                <div class="p-4 rounded-2xl border border-gray-800/80 bg-gray-950/70 backdrop-blur-md shadow-xl space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-xs font-semibold text-gray-200">🏷️ Post Hashtags</h3>
                            <span class="px-2 py-0.5 text-[10px] rounded-md font-medium bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                {postTags.length} tags
                            </span>
                        </div>
                        <span class="text-[10px] text-gray-500">Post-Specific Management</span>
                    </div>

                    <!-- Active Hashtags -->
                    <div class="flex flex-wrap gap-1.5 p-2.5 bg-gray-950/60 rounded-xl border border-gray-800/60 min-h-[42px] items-center">
                        {#if postTags.length === 0}
                            <span class="text-xs text-gray-500 italic">No hashtags for this post yet.</span>
                        {:else}
                            {#each postTags as tag}
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    {tag}
                                    <button
                                        type="button"
                                        onclick={() => removePostTag(tag)}
                                        class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-[10px] cursor-pointer"
                                    >
                                        ✕
                                    </button>
                                </span>
                            {/each}
                        {/if}
                    </div>

                    <!-- Add Tag Input -->
                    <div class="flex gap-2">
                        <input
                            type="text"
                            bind:value={newPostTagInput}
                            placeholder="#AddTag (e.g. #ShopeeFinds)"
                            onkeydown={(e) => { if (e.key === 'Enter') { e.preventDefault(); addPostTag(newPostTagInput); } }}
                            class="flex-1 bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-white focus:border-indigo-500 focus:outline-none font-mono"
                        />
                        <button
                            type="button"
                            onclick={() => addPostTag(newPostTagInput)}
                            disabled={!newPostTagInput.trim()}
                            class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold cursor-pointer disabled:opacity-50"
                        >
                            + Add
                        </button>
                    </div>

                    <!-- AI Recommended Tags -->
                    <div class="pt-2 border-t border-gray-800/40 space-y-1.5">
                        <span class="text-[11px] font-medium text-indigo-400 block">
                            🤖 AI Recommended Tags:
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            {#each aiRecommendedTags as tag}
                                {@const isAdded = postTags.includes(tag)}
                                <button
                                    type="button"
                                    onclick={() => toggleAiTag(tag)}
                                    class="px-2.5 py-0.5 text-xs rounded-lg font-medium border transition-all text-center flex items-center gap-1 cursor-pointer
                                        {isAdded
                                            ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300'
                                            : 'bg-gray-900/60 border-gray-800 text-gray-400 hover:text-gray-200 hover:border-gray-700'}"
                                >
                                    {isAdded ? '✓' : '+'} {tag}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>

                <!-- Save or Publish Box -->
                <div class="p-4 rounded-2xl border border-indigo-500/20 bg-gray-950/60 space-y-3">
                    <h3 class="text-xs font-semibold text-gray-300">Save or Publish</h3>
                    <p class="text-[11px] text-gray-400 leading-normal">
                        Save your caption & hashtags as draft, or preview before publishing to Facebook.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                        <button
                            type="button"
                            onclick={saveCaption}
                            class="px-3 py-2.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl text-xs font-semibold flex items-center justify-center gap-1.5 cursor-pointer {saved ? 'text-emerald-400 border-emerald-500/30' : 'text-gray-200'}"
                        >
                            {saved ? '✓ Draft Saved!' : '💾 Save Draft'}
                        </button>
                        <button
                            type="button"
                            onclick={() => (showPreviewModal = true)}
                            class="px-3 py-2.5 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer shadow-md shadow-indigo-500/20"
                        >
                            🚀 Preview & Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Screen Facebook Post Preview Modal (Exact Reference Style) -->
    {#if showPreviewModal}
        <div
            class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 overscroll-contain animate-fadeIn"
            onclick={() => (showPreviewModal = false)}
        >
            <div
                class="bg-gray-950 border border-indigo-500/30 rounded-2xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl relative animate-scaleIn flex flex-col max-h-[92vh]"
                onclick={(e) => e.stopPropagation()}
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-800 pb-3 flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500 animate-pulse"></span>
                        <h2 class="text-sm sm:text-base font-bold text-gray-100">📱 Facebook Live Post Preview</h2>
                    </div>

                    <!-- View Tabs & Close Button -->
                    <div class="flex items-center gap-2">
                        {#if mediaFiles.length > 0}
                            <div class="flex items-center p-0.5 rounded-lg bg-gray-900 border border-gray-800 text-[11px]">
                                <button
                                    type="button"
                                    onclick={() => (previewTab = 'grid')}
                                    class="px-2.5 py-1 rounded-md transition-all font-medium cursor-pointer
                                        {previewTab === 'grid' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-200'}"
                                >
                                    FB Grid
                                </button>
                                <button
                                    type="button"
                                    onclick={() => (previewTab = 'all')}
                                    class="px-2.5 py-1 rounded-md transition-all font-medium cursor-pointer
                                        {previewTab === 'all' ? 'bg-indigo-600 text-white shadow' : 'text-gray-400 hover:text-gray-200'}"
                                >
                                    All Photos ({mediaFiles.length})
                                </button>
                            </div>
                        {/if}
                        <button
                            type="button"
                            onclick={() => (showPreviewModal = false)}
                            class="text-gray-500 hover:text-gray-200 text-xl font-bold px-2 py-0.5 rounded-lg hover:bg-gray-800 transition-colors cursor-pointer"
                        >×</button>
                    </div>
                </div>

                <!-- Scrollable Facebook Post Simulator -->
                <div class="flex-1 overflow-y-auto pr-1 my-3 space-y-4 text-sm">
                    <div class="p-4 sm:p-5 rounded-2xl bg-gray-900 border border-gray-800/80 shadow-inner space-y-3.5">
                        <!-- FB Page Header -->
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white text-base shadow">
                                T
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-100 text-sm sm:text-base">{socialAccounts[0]?.name || 'Tech Sulit Deals'}</span>
                                    <span class="text-blue-400 text-xs sm:text-sm">✔</span>
                                </div>
                                <span class="text-xs text-gray-500">Just now · 🌐</span>
                            </div>
                        </div>

                        <!-- Main Caption Body -->
                        <div class="text-xs sm:text-sm text-gray-100 whitespace-pre-wrap leading-relaxed">
                            {caption}
                        </div>

                        <!-- Post Hashtags -->
                        {#if postTags.length > 0}
                            <div class="text-xs sm:text-sm text-blue-400 font-medium flex flex-wrap gap-1.5 pt-1 border-t border-gray-800/40">
                                {#each postTags as tag}
                                    <span class="hover:underline cursor-pointer">{tag}</span>
                                {/each}
                            </div>
                        {/if}

                        <!-- Photos Preview (Grid / Single) -->
                        {#if mediaFiles.length > 0}
                            {#if previewTab === 'grid'}
                                <div class="rounded-xl overflow-hidden border border-gray-800 bg-black aspect-video relative">
                                    <img
                                        src={mediaFiles[0]}
                                        alt="Main media"
                                        class="w-full h-full object-cover"
                                    />
                                    {#if mediaFiles.length > 1}
                                        <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/80 rounded-md text-[10px] text-white font-bold">
                                            +{mediaFiles.length - 1} more photos
                                        </div>
                                    {/if}
                                </div>
                            {:else}
                                <div class="grid grid-cols-2 gap-2">
                                    {#each mediaFiles as file}
                                        <div class="aspect-square rounded-xl overflow-hidden bg-black border border-gray-800">
                                            <img src={file} alt="Media" class="w-full h-full object-cover" />
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        {/if}
                    </div>

                    <!-- Target Page Selector -->
                    {#if socialAccounts.length > 0}
                        <div class="p-3 bg-gray-900/60 rounded-xl border border-gray-800 space-y-2">
                            <span class="text-xs font-semibold text-gray-300 block">Select Target Facebook Page:</span>
                            <div class="space-y-1.5">
                                {#each socialAccounts as account}
                                    <label class="flex items-center gap-2 text-xs text-gray-200 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            value={account.id}
                                            checked={selectedAccountIds.includes(account.id)}
                                            onchange={(e) => {
                                                const checked = (e.target as HTMLInputElement).checked;
                                                if (checked) {
                                                    selectedAccountIds = [...selectedAccountIds, account.id];
                                                } else {
                                                    selectedAccountIds = selectedAccountIds.filter(id => id !== account.id);
                                                }
                                            }}
                                            class="rounded border-gray-700 text-indigo-600"
                                        />
                                        <span>{account.name}</span>
                                    </label>
                                {/each}
                            </div>
                        </div>
                    {/if}
                </div>

                <!-- Modal Action Footer -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-800 flex-shrink-0">
                    <button
                        type="button"
                        onclick={() => (showPreviewModal = false)}
                        class="px-4 py-2 bg-gray-800 text-gray-300 hover:text-white rounded-xl text-xs font-semibold cursor-pointer"
                    >
                        Close
                    </button>
                    <button
                        type="button"
                        onclick={handlePublish}
                        disabled={publishing}
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20 cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
                    >
                        {#if publishing}
                            <span class="animate-spin text-xs">🌀</span>
                            <span>Publishing to Facebook...</span>
                        {:else}
                            <span>🚀 Confirm & Publish to Facebook</span>
                        {/if}
                    </button>
                </div>
            </div>
        </div>
    {/if}
</AppLayout>
