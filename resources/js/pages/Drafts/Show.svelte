<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
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

    // Editable state
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
    let error = $state('');
    let successMessage = $state('');

    // Selected Target Accounts for Multi-Page Publishing
    let selectedAccountIds = $state<string[]>(
        socialAccounts.map((a: any) => a.id)
    );

    // Selected Media for Preview
    let activeMediaIndex = $state(0);
    let previewDevice = $state<'mobile' | 'desktop'>('mobile');

    // New tag input
    let newTagInput = $state('');

    function parseTags(tagStr: string): string[] {
        if (!tagStr) return [];
        return tagStr.split(/\s+/).filter((t: string) => t.trim().length > 0);
    }

    let tagList = $derived(parseTags(tags));

    function addTag(tag: string) {
        let clean = tag.trim();
        if (!clean) return;
        if (!clean.startsWith('#')) clean = `#${clean}`;

        const current = parseTags(tags);
        if (!current.includes(clean)) {
            tags = [...current, clean].join(' ');
        }
        newTagInput = '';
    }

    function removeTag(tagToRemove: string) {
        tags = parseTags(tags).filter((t) => t !== tagToRemove).join(' ');
    }

    // Save Draft Updates
    function handleSave() {
        saving = true;
        error = '';
        saved = false;

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
                    setTimeout(() => (saved = false), 3000);
                },
                onError: (err) => {
                    saving = false;
                    error = Object.values(err)[0] || 'Failed to save changes';
                },
                onFinish: () => {
                    saving = false;
                },
            }
        );
    }

    // Generate AI Caption with specific style
    async function handleGenerateStyle(style: string) {
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

            saved = true;
            setTimeout(() => (saved = false), 3000);
        } catch (e: any) {
            error = e.message || 'Failed to generate AI caption';
        } finally {
            generatingCaption = false;
        }
    }

    // Upload Media File
    let fileInputRef: HTMLInputElement | null = null;
    let uploadingMedia = $state(false);

    async function handleUploadMedia(e: Event) {
        const input = e.target as HTMLInputElement;
        if (!input.files || input.files.length === 0) return;

        const file = input.files[0];
        uploadingMedia = true;
        error = '';

        try {
            const formData = new FormData();
            formData.append('file', file);

            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '';
            const res = await fetch(`/drafts/${post.id}/media`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: formData,
            });

            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.error || errData.message || 'Media upload failed');
            }

            const data = await res.json();
            mediaFiles = data.media_files;
            activeMediaIndex = mediaFiles.length - 1;
            successMessage = 'Media uploaded successfully!';
            setTimeout(() => (successMessage = ''), 3000);
        } catch (e: any) {
            error = e.message || 'Upload failed';
        } finally {
            uploadingMedia = false;
            if (input) input.value = '';
        }
    }

    // Delete Media
    async function handleDeleteMedia(fileUrl: string) {
        const filename = fileUrl.split('/').pop() || '';
        if (!filename) return;

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
                if (activeMediaIndex >= mediaFiles.length) {
                    activeMediaIndex = Math.max(0, mediaFiles.length - 1);
                }
            }
        } catch (e) {
            console.error('Delete media failed:', e);
        }
    }

    // Publish to Facebook
    function handlePublish() {
        if (!confirm('Are you sure you want to publish this post to Facebook now?')) {
            return;
        }

        publishing = true;
        error = '';

        router.post(
            `/drafts/${post.id}/publish`,
            {
                target_account_ids: selectedAccountIds,
            },
            {
                onSuccess: () => {
                    publishing = false;
                },
                onError: (err) => {
                    publishing = false;
                    error = Object.values(err)[0] || 'Publishing failed. Please check Facebook tokens in Settings.';
                },
                onFinish: () => {
                    publishing = false;
                },
            }
        );
    }

    function handleDeleteDraft() {
        if (confirm('Are you sure you want to delete this draft post?')) {
            router.delete(`/drafts/${post.id}`);
        }
    }
</script>

<AppLayout title="Edit Draft: {product_title || 'Post'}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Top Navigation & Action Header -->
        <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-4 sm:p-6 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a
                    href="/drafts"
                    class="p-2 bg-gray-800/80 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl text-xs font-semibold transition-colors flex items-center gap-1 cursor-pointer"
                >
                    <span>←</span>
                    <span class="hidden sm:inline">Drafts</span>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider
                            {post.status === 'published' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30'}">
                            {post.status || 'Draft'}
                        </span>
                        <span class="text-xs text-gray-500 font-mono">ID: {post.id}</span>
                    </div>
                    <h1 class="text-lg font-bold text-white mt-1 line-clamp-1">
                        {product_title || 'Untitled Post Draft'}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap self-start sm:self-auto">
                <button
                    type="button"
                    onclick={handleSave}
                    disabled={saving}
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold border border-gray-700 transition-all cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
                >
                    {#if saving}
                        <span class="animate-spin">🌀</span>
                        <span>Saving...</span>
                    {:else if saved}
                        <span>✓ Saved!</span>
                    {:else}
                        <span>💾 Save Draft</span>
                    {/if}
                </button>

                <button
                    type="button"
                    onclick={handlePublish}
                    disabled={publishing}
                    class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20 transition-all cursor-pointer flex items-center gap-1.5 disabled:opacity-50"
                >
                    {#if publishing}
                        <span class="animate-spin">🌀</span>
                        <span>Publishing to FB...</span>
                    {:else}
                        <span>🚀 Publish to Facebook</span>
                    {/if}
                </button>

                <button
                    type="button"
                    onclick={handleDeleteDraft}
                    title="Delete Draft"
                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/30 rounded-xl transition-all cursor-pointer"
                >
                    🗑️
                </button>
            </div>
        </div>

        {#if error}
            <div class="p-4 rounded-xl bg-red-950/80 border border-red-500/50 text-red-200 text-xs font-medium animate-slideDown">
                ⚠️ {error}
            </div>
        {/if}

        {#if successMessage}
            <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs font-medium animate-slideDown">
                ✓ {successMessage}
            </div>
        {/if}

        <!-- Main 2-Column Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Column: Draft Content & AI Editor (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Product Details Card -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-5 space-y-4 shadow-xl">
                    <h2 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2">
                        <span>📦</span> Product Info & Affiliate Link
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label for="p_title" class="block text-xs font-semibold text-gray-400 mb-1">Product Title</label>
                            <input
                                id="p_title"
                                type="text"
                                bind:value={product_title}
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label for="p_price" class="block text-xs font-semibold text-gray-400 mb-1">Price / Discount</label>
                            <input
                                id="p_price"
                                type="text"
                                bind:value={product_price}
                                placeholder="₱899 (50% OFF)"
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 focus:outline-none font-mono"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="p_url" class="block text-xs font-semibold text-gray-400 mb-1">Shopee PH Affiliate URL</label>
                        <div class="flex items-center gap-2">
                            <input
                                id="p_url"
                                type="url"
                                bind:value={affiliate_url}
                                class="flex-1 bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs text-indigo-400 font-mono focus:border-indigo-500 focus:outline-none"
                            />
                            {#if affiliate_url}
                                <a
                                    href={affiliate_url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="px-3 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-xl text-xs font-semibold transition-colors"
                                >
                                    Open ↗
                                </a>
                            {/if}
                        </div>
                    </div>
                </div>

                <!-- AI Copywriting Generator & Caption Editor Card -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-5 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2">
                            <span>✍️</span> AI Copywriting & Caption
                        </h2>
                        {#if generatingCaption}
                            <span class="text-xs text-indigo-400 font-semibold animate-pulse">🤖 AI Writing Post Copy...</span>
                        {/if}
                    </div>

                    <!-- AI Style Buttons Toolbar -->
                    <div>
                        <span class="block text-[11px] text-gray-400 font-semibold uppercase tracking-wider mb-2">
                            Generate With AI Style (Click to re-write):
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            {#each [
                                { id: 'viral_ai', label: '✨ Viral Hook', emoji: '🔥' },
                                { id: 'pinoy_taglish', label: '🇵🇭 Pinoy Tropa', emoji: '😍' },
                                { id: 'specs_tech', label: '💻 Tech Specs', emoji: '⚡' },
                                { id: 'urgency_flash', label: '🚨 Flash Sale', emoji: '💥' },
                                { id: 'review_story', label: '⭐ Review', emoji: '⭐' },
                                { id: 'aesthetic', label: '🌸 Aesthetic', emoji: '🌿' },
                                { id: 'minimal', label: '📄 Minimalist', emoji: '✨' },
                                { id: 'standard', label: '🔥 Standard', emoji: '🛒' },
                            ] as style}
                                <button
                                    type="button"
                                    onclick={() => handleGenerateStyle(style.id)}
                                    disabled={generatingCaption}
                                    class="p-2 bg-gray-950/80 hover:bg-indigo-950/40 border border-gray-800 hover:border-indigo-500/50 rounded-xl text-left transition-all cursor-pointer group disabled:opacity-50"
                                >
                                    <div class="text-xs font-bold text-gray-200 group-hover:text-indigo-300 truncate">
                                        {style.label}
                                    </div>
                                    <span class="text-[10px] text-gray-500">Regenerate</span>
                                </button>
                            {/each}
                        </div>
                    </div>

                    <!-- Editable Textarea -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="post_caption" class="text-xs font-semibold text-gray-400">Post Caption</label>
                            <span class="text-[10px] text-gray-500 font-mono">{caption.length} characters | {caption.split(/\s+/).filter(Boolean).length} words</span>
                        </div>
                        <textarea
                            id="post_caption"
                            bind:value={caption}
                            rows="9"
                            placeholder="Write or generate your high converting post caption here..."
                            class="w-full bg-gray-950 border border-gray-800 rounded-xl p-3 text-xs text-gray-100 font-sans leading-relaxed focus:border-indigo-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <!-- Hashtag Pill Manager -->
                    <div class="space-y-2 pt-2 border-t border-gray-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hashtags</span>
                            <span class="text-[10px] text-gray-500">{tagList.length} tag(s)</span>
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap">
                            {#each tagList as tag}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 rounded-lg text-xs font-mono">
                                    <span>{tag}</span>
                                    <button
                                        type="button"
                                        onclick={() => removeTag(tag)}
                                        class="hover:text-red-400 font-bold ml-0.5 cursor-pointer"
                                    >×</button>
                                </span>
                            {/each}
                        </div>

                        <!-- Add Tag Form -->
                        <div class="flex items-center gap-2 mt-2">
                            <input
                                type="text"
                                bind:value={newTagInput}
                                placeholder="Add hashtag (e.g. #ShopeeFinds)"
                                onkeydown={(e) => { if (e.key === 'Enter') { e.preventDefault(); addTag(newTagInput); } }}
                                class="flex-1 bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-white focus:border-indigo-500 focus:outline-none"
                            />
                            <button
                                type="button"
                                onclick={() => addTag(newTagInput)}
                                class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer"
                            >
                                + Add
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Media Files Uploader Card -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-5 space-y-4 shadow-xl">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2">
                            <span>🖼️</span> Photos & Media ({mediaFiles.length})
                        </h2>

                        <!-- Hidden File Input -->
                        <input
                            type="file"
                            accept="image/*,video/mp4"
                            bind:this={fileInputRef}
                            onchange={handleUploadMedia}
                            class="hidden"
                        />

                        <button
                            type="button"
                            onclick={() => fileInputRef?.click()}
                            disabled={uploadingMedia}
                            class="px-3 py-1.5 bg-indigo-600/80 hover:bg-indigo-600 text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1 disabled:opacity-50"
                        >
                            {#if uploadingMedia}
                                <span class="animate-spin">🌀</span>
                                <span>Uploading...</span>
                            {:else}
                                <span>+ Upload Photo</span>
                            {/if}
                        </button>
                    </div>

                    {#if mediaFiles.length === 0}
                        <div class="p-6 text-center border border-dashed border-gray-800 rounded-xl bg-gray-950/40">
                            <p class="text-xs text-gray-500">No media attached yet. Upload photos to enhance Facebook conversion!</p>
                        </div>
                    {:else}
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            {#each mediaFiles as file, idx}
                                <div class="relative group rounded-xl overflow-hidden border transition-all aspect-square bg-gray-950
                                    {activeMediaIndex === idx ? 'border-indigo-500 ring-2 ring-indigo-500/30' : 'border-gray-800'}">
                                    <button
                                        type="button"
                                        onclick={() => (activeMediaIndex = idx)}
                                        class="w-full h-full cursor-pointer"
                                    >
                                        <img src={file} alt="Media thumbnail {idx+1}" class="w-full h-full object-cover" />
                                    </button>

                                    <button
                                        type="button"
                                        onclick={() => handleDeleteMedia(file)}
                                        title="Delete photo"
                                        class="absolute top-1 right-1 p-1 rounded-md bg-black/70 hover:bg-red-600 text-white text-[10px] opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                    >
                                        ✕
                                    </button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>

                <!-- Target Facebook Pages Checkbox Card -->
                <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-5 space-y-3 shadow-xl">
                    <h2 class="text-sm font-bold text-gray-200 uppercase tracking-wider flex items-center gap-2">
                        <span>📘</span> Target Facebook Pages
                    </h2>
                    <p class="text-xs text-gray-400">Select which connected Facebook pages will receive this post when published:</p>

                    {#if socialAccounts.length === 0}
                        <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300 flex items-center justify-between">
                            <span>No Facebook pages connected.</span>
                            <a href="/settings/app" class="underline font-semibold">Connect in Settings ➔</a>
                        </div>
                    {:else}
                        <div class="space-y-2">
                            {#each socialAccounts as account}
                                <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-950/60 border border-gray-800/80 hover:border-gray-700 cursor-pointer transition-colors">
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
                                        class="rounded border-gray-700 text-indigo-600 focus:ring-indigo-500"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white truncate">{account.name}</p>
                                        <p class="text-[10px] text-gray-500 font-mono">Page ID: {account.account_id || account.id}</p>
                                    </div>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                </label>
                            {/each}
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Right Column: Live Facebook Post Simulator / Preview (5 Cols) -->
            <div class="lg:col-span-5 space-y-4 lg:sticky lg:top-20">
                <div class="flex items-center justify-between px-1">
                    <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                        <span>📱</span> Live Facebook Post Preview
                    </span>
                    <div class="flex items-center gap-1 bg-gray-900 border border-gray-800 p-1 rounded-xl">
                        <button
                            type="button"
                            onclick={() => (previewDevice = 'mobile')}
                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-colors cursor-pointer
                                {previewDevice === 'mobile' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'text-gray-400 hover:text-white'}"
                        >
                            Mobile
                        </button>
                        <button
                            type="button"
                            onclick={() => (previewDevice = 'desktop')}
                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold transition-colors cursor-pointer
                                {previewDevice === 'desktop' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40' : 'text-gray-400 hover:text-white'}"
                        >
                            Desktop
                        </button>
                    </div>
                </div>

                <!-- Facebook Feed Post Simulator Card -->
                <div class="bg-[#242526] border border-[#393a3b] rounded-2xl p-4 sm:p-5 shadow-2xl text-[#e4e6eb] font-sans space-y-3">
                    <!-- Post Header: Avatar, Page Name, Timestamp -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center font-bold text-white text-sm shadow-md">
                                📘
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-white hover:underline cursor-pointer">
                                    {socialAccounts[0]?.name || 'Tech Sulit Deals'}
                                </h3>
                                <div class="flex items-center gap-1 text-[11px] text-[#b0b3b8]">
                                    <span>Just now</span>
                                    <span>·</span>
                                    <span>🌐</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-[#b0b3b8] text-sm cursor-pointer">•••</span>
                    </div>

                    <!-- Caption Body with Live Line Breaks -->
                    <div class="text-xs sm:text-[13px] leading-relaxed whitespace-pre-line text-[#e4e6eb] font-normal">
                        {caption || 'Start typing a caption or click an AI style above to generate copy...'}
                    </div>

                    <!-- Tags Preview -->
                    {#if tags}
                        <div class="text-xs text-[#2d88ff] font-medium leading-relaxed">
                            {tags}
                        </div>
                    {/if}

                    <!-- Media Photo / Carousel Preview -->
                    {#if mediaFiles.length > 0}
                        <div class="rounded-xl overflow-hidden border border-[#393a3b] bg-black aspect-video relative group">
                            <img
                                src={mediaFiles[activeMediaIndex] || mediaFiles[0]}
                                alt="Facebook post visual"
                                class="w-full h-full object-cover"
                            />
                            {#if mediaFiles.length > 1}
                                <div class="absolute bottom-2 right-2 px-2 py-1 bg-black/80 rounded-md text-[10px] text-white font-semibold">
                                    {activeMediaIndex + 1} / {mediaFiles.length}
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <!-- Shopee Product Card Attachment (if affiliate URL present) -->
                    {#if affiliate_url}
                        <a
                            href={affiliate_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block rounded-xl border border-[#393a3b] bg-[#18191a] p-3 hover:bg-[#202122] transition-colors"
                        >
                            <div class="text-[10px] text-[#b0b3b8] uppercase font-mono tracking-wider">SHOPEE.PH</div>
                            <div class="text-xs font-bold text-white line-clamp-1 mt-0.5">{product_title || 'Shopee Sulit Deal'}</div>
                            {#if product_price}
                                <div class="text-xs text-emerald-400 font-bold font-mono mt-0.5">{product_price}</div>
                            {/if}
                        </a>
                    {/if}

                    <!-- Social Interactions Count Bar -->
                    <div class="flex items-center justify-between text-[11px] text-[#b0b3b8] pt-2 border-t border-[#393a3b]/80">
                        <div class="flex items-center gap-1">
                            <span class="text-xs">👍❤️🔥</span>
                            <span>428</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span>86 comments</span>
                            <span>34 shares</span>
                        </div>
                    </div>

                    <!-- Facebook Action Buttons (Like / Comment / Share) -->
                    <div class="grid grid-cols-3 gap-1 pt-1 border-t border-[#393a3b]/80 text-center text-xs text-[#b0b3b8] font-semibold">
                        <div class="py-1.5 hover:bg-[#3a3b3c] rounded-lg cursor-pointer transition-colors flex items-center justify-center gap-1.5">
                            <span>👍</span> Like
                        </div>
                        <div class="py-1.5 hover:bg-[#3a3b3c] rounded-lg cursor-pointer transition-colors flex items-center justify-center gap-1.5">
                            <span>💬</span> Comment
                        </div>
                        <div class="py-1.5 hover:bg-[#3a3b3c] rounded-lg cursor-pointer transition-colors flex items-center justify-center gap-1.5">
                            <span>↗️</span> Share
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AppLayout>
