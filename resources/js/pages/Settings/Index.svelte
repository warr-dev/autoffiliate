<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router, page } from '@inertiajs/svelte';

    let {
        settings = {},
        socialAccounts = [],
        users = [],
    } = $props<{
        settings?: Record<string, string>;
        socialAccounts?: Array<any>;
        users?: Array<any>;
    }>();

    // Active Category Navigation
    let activeCategory = $state<'social' | 'ai' | 'security'>('social');

    // General App & AI Settings
    let disclosure = $state('');
    let default_hashtags = $state('');
    let webhook_secret = $state('');
    let n8n_outbound_webhook = $state('');
    let fb_app_id = $state('');
    let fb_app_secret = $state('');
    let ai_provider = $state('openai');
    let ai_api_key = $state('');
    let ai_model = $state('gpt-4o-mini');
    let ai_system_prompt = $state('');

    $effect(() => {
        disclosure =
            settings.disclosure ||
            'Affiliate link. Price and availability may change anytime.';
        default_hashtags =
            settings.default_hashtags || '#TechSulitDeals #ShopeePH';
        webhook_secret = settings.webhook_secret || '';
        n8n_outbound_webhook = settings.n8n_outbound_webhook || '';
        fb_app_id = settings.fb_app_id || '';
        fb_app_secret = settings.fb_app_secret || '';
        ai_provider = settings.ai_provider || 'openai';
        ai_api_key = settings.ai_api_key || '';
        ai_model = settings.ai_model || 'gpt-4o-mini';
        ai_system_prompt =
            settings.ai_system_prompt ||
            'You are an expert affiliate marketer focusing on high-energy, viral product posts. Write an engaging, high-converting social media caption for a product deal using the Product Title and Description. Highlight key selling points, use eye-catching emojis, bullet points, and an urgent call to action.';
    });

    let saving = $state(false);
    let saved = $state(false);
    let error = $state('');

    function handleSaveSettings() {
        saving = true;
        error = '';
        router.post(
            '/settings/app',
            {
                disclosure,
                default_hashtags,
                webhook_secret,
                n8n_outbound_webhook,
                fb_app_id,
                fb_app_secret,
                ai_provider,
                ai_api_key,
                ai_model,
                ai_system_prompt,
            },
            {
                onSuccess: () => {
                    saving = false;
                    saved = true;
                    setTimeout(() => (saved = false), 3000);
                },
                onError: (err) => {
                    saving = false;
                    error = Object.values(err)[0] || 'Failed to save settings';
                },
                onFinish: () => {
                    saving = false;
                },
            },
        );
    }

    // Connect Social Account Modal / Form State
    let showAddSocialModal = $state(false);
    let newPlatform = $state('facebook');
    let newName = $state('');
    let newAccountId = $state('');
    let newAccessToken = $state('');
    let newAiContext = $state('');
    let newDefaultHashtags = $state('');
    let newIsAffiliate = $state(true);
    let newDisclosure = $state(
        'Affiliate link. Price and availability may change anytime.',
    );
    let addingAccount = $state(false);
    let showAddGuide = $state(false);
    let showAddAutoExchange = $state(false);
    let newTagInputAdd = $state('');

    // Edit Social Account Modal State
    let showEditModal = $state(false);
    let editId = $state('');
    let editPlatform = $state('facebook');
    let editName = $state('');
    let editAccountId = $state('');
    let editAccessToken = $state('');
    let editAiContext = $state('');
    let editDefaultHashtags = $state('');
    let editIsAffiliate = $state(true);
    let editDisclosure = $state('');
    let editingAccount = $state(false);
    let showEditGuide = $state(false);
    let showEditAutoExchange = $state(false);
    let editTagInput = $state('');

    // Auto Token Exchange State
    let exchangeUserToken = $state('');
    let exchangeAppId = $state('');
    let exchangeAppSecret = $state('');
    let exchanging = $state(false);
    let exchangeError = $state('');
    let exchangeSuccess = $state('');

    // Webhook Testing State
    let testingWebhook = $state(false);
    let testWebhookResult = $state<any>(null);
    let copiedInboundUrl = $state(false);

    // User Creation in Security Tab
    let regName = $state('');
    let regEmail = $state('');
    let regPassword = $state('');
    let creatingUser = $state(false);
    let userSuccessMsg = $state('');
    let userErrorMsg = $state('');

    // Helper functions for Hashtags
    function parseHashtagString(str: string | undefined): string[] {
        if (!str) return [];
        return str
            .split(/\s+/)
            .map((t) => t.trim())
            .filter((t) => t.length > 0)
            .map((t) => (t.startsWith('#') ? t : `#${t}`));
    }

    function addHashtagToList(currentStr: string, input: string): string {
        const raw = input.trim();
        if (!raw) return currentStr;
        const formatted = raw.startsWith('#') ? raw : `#${raw}`;
        const existing = parseHashtagString(currentStr);
        if (
            !existing.some((t) => t.toLowerCase() === formatted.toLowerCase())
        ) {
            existing.push(formatted);
        }
        return existing.join(' ');
    }

    function removeHashtagFromList(
        currentStr: string,
        tagToRemove: string,
    ): string {
        const existing = parseHashtagString(currentStr);
        return existing
            .filter((t) => t.toLowerCase() !== tagToRemove.toLowerCase())
            .join(' ');
    }

    function handleAddSocialAccount() {
        if (!newName.trim() || !newAccountId.trim()) return;
        addingAccount = true;
        error = '';

        router.post(
            '/settings/social-accounts',
            {
                platform: newPlatform,
                name: newName.trim(),
                account_id: newAccountId.trim(),
                access_token: newAccessToken.trim(),
                extra_config: {
                    ai_context: newAiContext.trim(),
                    default_hashtags: newDefaultHashtags.trim(),
                    is_affiliate: newIsAffiliate,
                    disclosure: newDisclosure.trim(),
                },
                is_enabled: true,
            },
            {
                onSuccess: () => {
                    addingAccount = false;
                    showAddSocialModal = false;
                    newName = '';
                    newAccountId = '';
                    newAccessToken = '';
                    newAiContext = '';
                    newDefaultHashtags = '';
                },
                onError: (err) => {
                    addingAccount = false;
                    error =
                        Object.values(err)[0] || 'Failed to add social account';
                },
            },
        );
    }

    function openEditModal(account: any) {
        editId = account.id;
        editPlatform = account.platform || 'facebook';
        editName = account.name || '';
        editAccountId = account.account_id || '';
        editAccessToken = '••••••••';
        editAiContext = account.extra_config?.ai_context || '';
        editDefaultHashtags = account.extra_config?.default_hashtags || '';
        editIsAffiliate = account.extra_config?.is_affiliate !== false;
        editDisclosure =
            account.extra_config?.disclosure ||
            'Affiliate link. Price and availability may change anytime.';
        exchangeError = '';
        exchangeSuccess = '';
        showEditModal = true;
    }

    function handleEditSocialAccount() {
        if (!editId || !editName.trim() || !editAccountId.trim()) return;
        editingAccount = true;
        error = '';

        router.patch(
            `/settings/social-accounts/${editId}`,
            {
                platform: editPlatform,
                name: editName.trim(),
                account_id: editAccountId.trim(),
                access_token:
                    editAccessToken !== '••••••••'
                        ? editAccessToken.trim()
                        : undefined,
                extra_config: {
                    ai_context: editAiContext.trim(),
                    default_hashtags: editDefaultHashtags.trim(),
                    is_affiliate: editIsAffiliate,
                    disclosure: editDisclosure.trim(),
                },
            },
            {
                onSuccess: () => {
                    editingAccount = false;
                    showEditModal = false;
                },
                onError: (err) => {
                    editingAccount = false;
                    error =
                        Object.values(err)[0] ||
                        'Failed to update social account';
                },
            },
        );
    }

    function handleToggleAccount(id: string) {
        router.post(
            `/settings/social-accounts/${id}/toggle`,
            {},
            { preserveScroll: true },
        );
    }

    function handleDeleteAccount(id: string) {
        if (
            confirm('Are you sure you want to remove this social integration?')
        ) {
            router.delete(`/settings/social-accounts/${id}`, {
                preserveScroll: true,
            });
        }
    }

    async function handleTestWebhook() {
        if (!n8n_outbound_webhook.trim()) return;
        testingWebhook = true;
        testWebhookResult = null;

        try {
            const res = await fetch('/settings/test-webhook', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': page.props.csrf_token as string,
                },
                body: JSON.stringify({ url: n8n_outbound_webhook.trim() }),
            });
            const data = await res.json();
            testWebhookResult = data;
        } catch (e: any) {
            testWebhookResult = {
                success: false,
                error: e.message || 'Network error',
            };
        } finally {
            testingWebhook = false;
        }
    }

    function copyInboundUrl() {
        const origin =
            typeof window !== 'undefined'
                ? window.location.origin
                : 'http://localhost:8000';
        const fullUrl = `${origin}/api/webhooks/incoming-deal`;
        navigator.clipboard.writeText(fullUrl);
        copiedInboundUrl = true;
        setTimeout(() => (copiedInboundUrl = false), 2000);
    }

    function handleCreateUser() {
        if (!regName.trim() || !regEmail.trim() || !regPassword.trim()) return;
        creatingUser = true;
        userSuccessMsg = '';
        userErrorMsg = '';

        router.post(
            '/settings/users',
            {
                name: regName.trim(),
                email: regEmail.trim(),
                password: regPassword.trim(),
            },
            {
                onSuccess: () => {
                    creatingUser = false;
                    userSuccessMsg = `Account [${regEmail}] created successfully!`;
                    regName = '';
                    regEmail = '';
                    regPassword = '';
                },
                onError: (err) => {
                    creatingUser = false;
                    userErrorMsg =
                        Object.values(err)[0] || 'Failed to create user';
                },
            },
        );
    }

    function handleLogout() {
        router.post('/logout');
    }
</script>

<AppLayout title="Settings">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <a
                href="/dashboard"
                class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors mb-2 inline-block"
            >
                ← Dashboard
            </a>
            <h1 class="text-3xl font-extrabold tracking-tight">
                <span
                    class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                >
                    Application & AI Settings
                </span>
            </h1>
            <p class="text-gray-400 text-sm mt-1">
                Manage multi-channel social integrations, AI model providers,
                webhooks, and team access.
            </p>
        </div>

        <!-- Categorized Navigation Tabs -->
        <div
            class="grid grid-cols-3 gap-2 p-1.5 bg-gray-950/80 rounded-2xl border border-gray-800/80 mb-8 backdrop-blur-xl shadow-xl"
        >
            <button
                type="button"
                onclick={() => (activeCategory = 'social')}
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer
                    {activeCategory === 'social'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                <span>🌐</span>
                <span>Social Media</span>
            </button>

            <button
                type="button"
                onclick={() => (activeCategory = 'ai')}
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer
                    {activeCategory === 'ai'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                <span>🤖</span>
                <span>AI Captions</span>
            </button>

            <button
                type="button"
                onclick={() => (activeCategory = 'security')}
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer
                    {activeCategory === 'security'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                <span>🔑</span>
                <span>Security & Users</span>
            </button>
        </div>

        <!-- CATEGORY 1: SOCIAL MEDIA & WEBHOOKS -->
        {#if activeCategory === 'social'}
            <div class="space-y-6">
                <!-- Connected Social Pages Card -->
                <div
                    class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800/60 pb-5"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">📘</span>
                                <h2 class="text-lg font-bold text-gray-100">
                                    Connected Social Accounts
                                </h2>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Manage connected Facebook Pages and social
                                channels for multi-channel affiliate publishing.
                            </p>
                        </div>
                        <button
                            type="button"
                            onclick={() =>
                                (showAddSocialModal = !showAddSocialModal)}
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 transition-all cursor-pointer flex items-center gap-1.5 self-start sm:self-auto"
                        >
                            <span>＋</span> Connect Account
                        </button>
                    </div>

                    <!-- Connect Account Modal / Form -->
                    {#if showAddSocialModal}
                        <div
                            class="p-6 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 space-y-5"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-100">
                                        Connect Social Media Account
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Link your Facebook Page or channel to
                                        enable 1-click publishing.
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    onclick={() => (showAddSocialModal = false)}
                                    class="text-gray-400 hover:text-white p-1 text-sm cursor-pointer"
                                >
                                    ✕
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                                >
                                    <div>
                                        <label
                                            for="new_platform"
                                            class="block text-xs font-semibold text-gray-300 mb-1.5"
                                        >
                                            Platform Network
                                        </label>
                                        <select
                                            id="new_platform"
                                            bind:value={newPlatform}
                                            class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none"
                                        >
                                            <option value="facebook"
                                                >📘 Facebook Page</option
                                            >
                                            <option value="tiktok"
                                                >🎵 TikTok Account</option
                                            >
                                            <option value="shopee"
                                                >🛍️ Shopee Shop</option
                                            >
                                            <option value="instagram"
                                                >📸 Instagram Account</option
                                            >
                                            <option value="youtube"
                                                >🎥 YouTube Channel</option
                                            >
                                            <option value="telegram"
                                                >✈️ Telegram Channel</option
                                            >
                                        </select>
                                    </div>

                                    <div>
                                        <label
                                            for="new_name"
                                            class="block text-xs font-semibold text-gray-300 mb-1.5"
                                        >
                                            Display Name
                                        </label>
                                        <input
                                            id="new_name"
                                            type="text"
                                            bind:value={newName}
                                            placeholder="Tech Sulit Deals"
                                            class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="new_account_id"
                                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                                    >
                                        {newPlatform === 'facebook'
                                            ? 'Facebook Page ID'
                                            : 'Account / Channel ID'}
                                    </label>
                                    <input
                                        id="new_account_id"
                                        type="text"
                                        bind:value={newAccountId}
                                        placeholder={newPlatform === 'facebook'
                                            ? '1184127881441932'
                                            : '@handle or Channel ID'}
                                        class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none"
                                    />
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        {newPlatform === 'facebook'
                                            ? 'Found in your Page About section → Page ID'
                                            : 'Unique identifier for posting.'}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        for="new_access_token"
                                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                                    >
                                        {newPlatform === 'facebook'
                                            ? 'Page Access Token'
                                            : 'Access Token / API Secret'}
                                    </label>
                                    <input
                                        id="new_access_token"
                                        type="password"
                                        bind:value={newAccessToken}
                                        placeholder={newPlatform === 'facebook'
                                            ? 'EAAl4jZCR...'
                                            : 'Token or Secret'}
                                        class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none"
                                    />
                                </div>

                                <!-- Custom AI Context for this account -->
                                <div>
                                    <label
                                        for="new_ai_context"
                                        class="block text-xs font-semibold text-indigo-300 mb-1.5"
                                    >
                                        🤖 Custom AI System Context for this
                                        Page (Optional)
                                    </label>
                                    <textarea
                                        id="new_ai_context"
                                        rows="2"
                                        bind:value={newAiContext}
                                        placeholder="e.g. Focus on budget gadgets. Use high-energy Taglish tone, emphasize discounts, and add emojis!"
                                        class="w-full rounded-xl border border-gray-800 bg-gray-950/80 p-3 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none resize-none"
                                    ></textarea>
                                </div>

                                <!-- Default Hashtags with Tag Pills -->
                                <div>
                                    <span
                                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                                    >
                                        🏷️ Default Hashtags for this Page
                                    </span>
                                    <div
                                        class="flex flex-wrap gap-1.5 p-3 bg-gray-950/80 rounded-xl border border-gray-800 min-h-[44px] items-center mb-2"
                                    >
                                        {#if parseHashtagString(newDefaultHashtags).length === 0}
                                            <span
                                                class="text-xs text-gray-600 italic"
                                                >No default tags set. Type a tag
                                                below.</span
                                            >
                                        {:else}
                                            {#each parseHashtagString(newDefaultHashtags) as tag}
                                                <span
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30"
                                                >
                                                    {tag}
                                                    <button
                                                        type="button"
                                                        onclick={() =>
                                                            (newDefaultHashtags =
                                                                removeHashtagFromList(
                                                                    newDefaultHashtags,
                                                                    tag,
                                                                ))}
                                                        class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-xs cursor-pointer"
                                                    >
                                                        ✕
                                                    </button>
                                                </span>
                                            {/each}
                                        {/if}
                                    </div>
                                    <div class="flex gap-2">
                                        <input
                                            bind:value={newTagInputAdd}
                                            placeholder="#TechSulitDeals or ShopeePH"
                                            onkeydown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();
                                                    if (newTagInputAdd.trim()) {
                                                        newDefaultHashtags =
                                                            addHashtagToList(
                                                                newDefaultHashtags,
                                                                newTagInputAdd,
                                                            );
                                                        newTagInputAdd = '';
                                                    }
                                                }
                                            }}
                                            class="flex-1 h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                                        />
                                        <button
                                            type="button"
                                            onclick={() => {
                                                if (newTagInputAdd.trim()) {
                                                    newDefaultHashtags =
                                                        addHashtagToList(
                                                            newDefaultHashtags,
                                                            newTagInputAdd,
                                                        );
                                                    newTagInputAdd = '';
                                                }
                                            }}
                                            disabled={!newTagInputAdd.trim()}
                                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 text-gray-200 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                                        >
                                            + Add Tag
                                        </button>
                                    </div>
                                </div>

                                <!-- Affiliate Compliance Checkbox -->
                                <div
                                    class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 space-y-3"
                                >
                                    <label
                                        class="flex items-center gap-2.5 cursor-pointer select-none"
                                    >
                                        <input
                                            type="checkbox"
                                            bind:checked={newIsAffiliate}
                                            class="w-4 h-4 rounded text-indigo-600 bg-gray-900 border-gray-700 cursor-pointer"
                                        />
                                        <span
                                            class="text-xs font-semibold text-gray-200"
                                        >
                                            ⚡ Is Affiliate Page (Auto-Append
                                            Compliance Disclaimers)
                                        </span>
                                    </label>

                                    {#if newIsAffiliate}
                                        <div
                                            class="pt-2 border-t border-indigo-500/20"
                                        >
                                            <label
                                                for="new_disc"
                                                class="block text-[11px] font-medium text-gray-400 mb-1"
                                            >
                                                Affiliate Disclosure Text
                                            </label>
                                            <input
                                                id="new_disc"
                                                type="text"
                                                bind:value={newDisclosure}
                                                class="w-full h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs text-gray-100 focus:border-indigo-500 outline-none"
                                            />
                                        </div>
                                    {/if}
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 pt-2"
                            >
                                <button
                                    type="button"
                                    onclick={() => (showAddSocialModal = false)}
                                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-gray-300 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="button"
                                    onclick={handleAddSocialAccount}
                                    disabled={addingAccount ||
                                        !newName.trim() ||
                                        !newAccountId.trim()}
                                    class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all cursor-pointer"
                                >
                                    {addingAccount
                                        ? 'Connecting...'
                                        : 'Connect Account'}
                                </button>
                            </div>
                        </div>
                    {/if}

                    <!-- Social Accounts List -->
                    {#if socialAccounts.length === 0}
                        <div
                            class="p-8 text-center bg-gray-950/40 border border-gray-800/60 rounded-xl space-y-2"
                        >
                            <span class="text-3xl opacity-40">🌐</span>
                            <p class="text-sm font-semibold text-gray-300">
                                No social accounts connected yet
                            </p>
                            <p class="text-xs text-gray-500">
                                Click "Connect Account" above to link your
                                Facebook Page.
                            </p>
                        </div>
                    {:else}
                        <div class="space-y-3">
                            {#each socialAccounts as account (account.id)}
                                {@const icon =
                                    account.platform === 'facebook'
                                        ? '📘'
                                        : account.platform === 'tiktok'
                                          ? '🎵'
                                          : account.platform === 'shopee'
                                            ? '🛍️'
                                            : account.platform === 'instagram'
                                              ? '📸'
                                              : account.platform === 'youtube'
                                                ? '🎥'
                                                : '✈️'}
                                <div
                                    class="p-4 rounded-xl bg-gray-950/60 border border-gray-800/80 hover:border-indigo-500/40 transition-all flex items-center justify-between gap-4"
                                >
                                    <div
                                        class="flex items-center gap-3.5 min-w-0"
                                    >
                                        <div
                                            class="w-11 h-11 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-xl flex-shrink-0"
                                        >
                                            {icon}
                                        </div>
                                        <div class="min-w-0">
                                            <div
                                                class="flex items-center gap-2 flex-wrap"
                                            >
                                                <h3
                                                    class="text-sm font-bold text-gray-100 truncate"
                                                >
                                                    {account.name}
                                                </h3>
                                                <span
                                                    class="px-2 py-0.5 text-[9px] font-bold uppercase rounded-md bg-indigo-950/80 text-indigo-300 border border-indigo-500/30"
                                                >
                                                    {account.platform}
                                                </span>
                                                <button
                                                    type="button"
                                                    onclick={() =>
                                                        handleToggleAccount(
                                                            account.id,
                                                        )}
                                                    class="px-2 py-0.5 text-[10px] font-bold rounded-full border cursor-pointer transition-all
                                                        {account.is_enabled
                                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 hover:bg-emerald-500/20'
                                                        : 'bg-gray-800 text-gray-400 border-gray-700 hover:bg-gray-700'}"
                                                >
                                                    {account.is_enabled
                                                        ? '● Active'
                                                        : '○ Paused'}
                                                </button>
                                            </div>
                                            <p
                                                class="text-xs text-gray-500 font-mono mt-0.5 truncate"
                                            >
                                                ID: {account.account_id ||
                                                    'Not set'}
                                                {#if account.access_token}
                                                    <span
                                                        class="text-emerald-400 font-sans ml-2"
                                                        >✓ Token Configured</span
                                                    >
                                                {:else}
                                                    <span
                                                        class="text-amber-400 font-sans ml-2"
                                                        >⚠️ No Token</span
                                                    >
                                                {/if}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-1.5 flex-shrink-0"
                                    >
                                        <button
                                            type="button"
                                            onclick={() =>
                                                openEditModal(account)}
                                            class="p-2 rounded-lg bg-gray-900 hover:bg-gray-800 text-gray-300 hover:text-white border border-gray-800 transition-all cursor-pointer"
                                            title="Edit account details"
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() =>
                                                handleDeleteAccount(account.id)}
                                            class="p-2 rounded-lg bg-gray-900 hover:bg-red-500/10 text-gray-400 hover:text-red-400 border border-gray-800 hover:border-red-500/30 transition-all cursor-pointer"
                                            title="Delete account"
                                        >
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>

                <!-- n8n & Webhooks Automation Card -->
                <div
                    class="bg-gray-900/70 backdrop-blur-xl border border-indigo-500/30 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-800/60 pb-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">⚡</span>
                                <h2 class="text-lg font-bold text-gray-100">
                                    n8n & Webhooks Automation
                                </h2>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Automate deal extraction from n8n or broadcast
                                published posts to external webhooks.
                            </p>
                        </div>
                        <span
                            class="px-2.5 py-1 text-xs rounded-lg font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30"
                        >
                            n8n Hybrid Ready
                        </span>
                    </div>

                    <div class="space-y-5">
                        <!-- 1. Inbound Webhook -->
                        <div
                            class="p-4 rounded-xl bg-gray-950/80 border border-gray-800 space-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs font-bold text-gray-200">
                                    📥 Inbound Webhook (Trigger Deal Extraction
                                    from n8n)
                                </h3>
                                <button
                                    type="button"
                                    onclick={copyInboundUrl}
                                    class="text-xs px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 border border-indigo-500/40 hover:bg-indigo-600/50 transition-all cursor-pointer font-semibold"
                                >
                                    {copiedInboundUrl
                                        ? '✓ Copied!'
                                        : '📋 Copy Inbound URL'}
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">
                                Send Shopee PH links to this endpoint to
                                automatically extract media and create drafts.
                            </p>
                            <input
                                readonly
                                class="w-full h-10 rounded-xl border border-gray-800 bg-gray-900/90 px-3.5 text-xs font-mono text-gray-400 select-all outline-none"
                                value={typeof window !== 'undefined'
                                    ? `${window.location.origin}/api/webhooks/incoming-deal`
                                    : 'http://localhost:8000/api/webhooks/incoming-deal'}
                            />

                            <div>
                                <label
                                    for="webhook_sec"
                                    class="block text-xs font-medium text-gray-400 mb-1"
                                >
                                    Webhook Security Secret (Optional)
                                </label>
                                <input
                                    id="webhook_sec"
                                    type="text"
                                    bind:value={webhook_secret}
                                    placeholder="Custom secret key for X-Aiffiliate-Secret header"
                                    class="w-full h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                                />
                            </div>
                        </div>

                        <!-- 2. Outbound Webhook -->
                        <div
                            class="p-4 rounded-xl bg-gray-950/80 border border-gray-800 space-y-3"
                        >
                            <h3 class="text-xs font-bold text-gray-200">
                                📤 Outbound Webhook (Broadcast Published Post to
                                n8n)
                            </h3>
                            <p class="text-xs text-gray-500">
                                Automatically dispatches post media, captions,
                                and affiliate links to your n8n workflow upon
                                publication.
                            </p>
                            <div class="flex gap-2">
                                <input
                                    id="n8n_out"
                                    type="text"
                                    bind:value={n8n_outbound_webhook}
                                    placeholder="https://n8n.yourdomain.com/webhook/aiffiliate-published"
                                    class="flex-1 h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                                />
                                <button
                                    type="button"
                                    onclick={handleTestWebhook}
                                    disabled={testingWebhook ||
                                        !n8n_outbound_webhook.trim()}
                                    class="px-4 py-2 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 text-gray-200 text-xs font-semibold rounded-xl transition-all cursor-pointer flex-shrink-0"
                                >
                                    {testingWebhook
                                        ? 'Testing...'
                                        : '🧪 Test Webhook'}
                                </button>
                            </div>

                            {#if testWebhookResult}
                                <div
                                    class="p-3 rounded-xl text-xs font-medium
                                    {testWebhookResult.success
                                        ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400'
                                        : 'bg-red-500/10 border border-red-500/20 text-red-400'}"
                                >
                                    {#if testWebhookResult.success}
                                        ✅ Test payload delivered successfully!
                                        (Status: {testWebhookResult.status_code})
                                    {:else}
                                        ❌ Webhook test failed: {testWebhookResult.error}
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <!-- CATEGORY 2: AI PROVIDER & CAPTIONS -->
        {:else if activeCategory === 'ai'}
            <div class="space-y-6">
                <div
                    class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6"
                >
                    <div
                        class="flex items-center justify-between border-b border-gray-800/60 pb-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xl">🤖</span>
                                <h2 class="text-lg font-bold text-gray-100">
                                    AI Model & Copywriting Engine
                                </h2>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Choose your AI LLM provider, enter credentials,
                                and configure system prompts for generating
                                viral captions.
                            </p>
                        </div>
                        <span
                            class="px-2.5 py-1 text-xs rounded-lg font-bold
                            {ai_api_key.trim()
                                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
                                : 'bg-gray-800 text-gray-400 border border-gray-700'}"
                        >
                            {ai_api_key.trim()
                                ? '🤖 AI Enabled'
                                : '⚡ Template Fallback'}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                for="ai_prov"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                AI Provider
                            </label>
                            <select
                                id="ai_prov"
                                bind:value={ai_provider}
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 focus:border-indigo-500 outline-none cursor-pointer"
                            >
                                <option value="openai">OpenAI (ChatGPT)</option>
                                <option value="deepseek">DeepSeek AI</option>
                                <option value="gemini">Google Gemini</option>
                                <option value="openrouter">OpenRouter</option>
                                <option value="internal"
                                    >Built-in Templates</option
                                >
                            </select>
                        </div>

                        <div>
                            <label
                                for="ai_mod"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                AI Model Name
                            </label>
                            <input
                                id="ai_mod"
                                type="text"
                                bind:value={ai_model}
                                placeholder="gpt-4o-mini / deepseek-chat / gemini-1.5-flash"
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                        </div>

                        <div>
                            <label
                                for="ai_key"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                API Key
                            </label>
                            <input
                                id="ai_key"
                                type="password"
                                bind:value={ai_api_key}
                                placeholder="sk-... or AIzaSy..."
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                        </div>
                    </div>

                    <!-- System Prompt & Presets -->
                    <div>
                        <div
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2"
                        >
                            <label
                                for="sys_prompt"
                                class="block text-xs font-semibold text-gray-300"
                            >
                                AI System Prompt Template
                            </label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[11px] text-gray-500 mr-1"
                                    >Load Preset:</span
                                >
                                <button
                                    type="button"
                                    onclick={() =>
                                        (ai_system_prompt =
                                            'You are an expert affiliate marketer focusing on high-energy, viral product posts. Write an engaging, high-converting social media caption for a product deal using the Product Title and Description. Highlight key selling points, use eye-catching emojis, bullet points, and an urgent call to action.')}
                                    class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-800 hover:bg-gray-700 text-indigo-300 transition-colors cursor-pointer font-medium"
                                >
                                    ✨ Viral Hype
                                </button>
                                <button
                                    type="button"
                                    onclick={() =>
                                        (ai_system_prompt =
                                            "You are a popular Filipino affiliate content creator on Facebook. Write a super relatable, friendly, and enthusiastic Shopee 'budol' post in Taglish (mix of Tagalog and English). Use catchphrases like 'Mga besh!', 'Super sulit!', 'Budol find of the day!'. Include bullet points, emojis, and affiliate disclosure.")}
                                    class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-800 hover:bg-gray-700 text-indigo-300 transition-colors cursor-pointer font-medium"
                                >
                                    🇵🇭 Taglish
                                </button>
                                <button
                                    type="button"
                                    onclick={() =>
                                        (ai_system_prompt =
                                            'You are a professional technology and product reviewer. Write a structured, informative product breakdown focusing on technical specifications, key features, performance highlights, and value-for-money. Use clean bullet points and clear section headers.')}
                                    class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-800 hover:bg-gray-700 text-indigo-300 transition-colors cursor-pointer font-medium"
                                >
                                    📊 Tech Specs
                                </button>
                                <button
                                    type="button"
                                    onclick={() =>
                                        (ai_system_prompt =
                                            'You are an authentic reviewer sharing your personal experience with a product deal. Write a warm, conversational review-style caption explaining why this product is worth buying, its pros, and who it is best suited for.')}
                                    class="px-2.5 py-1 text-[11px] rounded-lg bg-gray-800 hover:bg-gray-700 text-indigo-300 transition-colors cursor-pointer font-medium"
                                >
                                    ⭐ Review
                                </button>
                            </div>
                        </div>

                        <textarea
                            id="sys_prompt"
                            rows="4"
                            bind:value={ai_system_prompt}
                            placeholder="You are an expert affiliate marketer..."
                            class="w-full rounded-xl border border-gray-800 bg-gray-950/80 p-4 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 outline-none resize-y leading-relaxed"
                        ></textarea>
                    </div>

                    <!-- Global Default Compliance Disclosure -->
                    <div>
                        <label
                            for="glob_disc"
                            class="block text-xs font-semibold text-gray-300 mb-1.5"
                        >
                            📜 Global Default Affiliate Disclosure
                        </label>
                        <input
                            id="glob_disc"
                            type="text"
                            bind:value={disclosure}
                            class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 focus:border-indigo-500 outline-none"
                        />
                    </div>
                </div>

                <!-- AI Extraction Notice Card -->
                <div
                    class="bg-gray-900/60 border border-indigo-500/20 rounded-2xl p-6 space-y-2"
                >
                    <h3
                        class="text-xs font-bold text-gray-200 flex items-center gap-2"
                    >
                        <span>💡</span> Full Specification & Description Scraper
                    </h3>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        When generating captions via AI, the system
                        automatically scrapes the full product title,
                        og:description, price, and shop metadata from Shopee PH
                        and supplies it to your LLM system prompt.
                    </p>
                </div>
            </div>

            <!-- CATEGORY 3: SECURITY & USERS -->
        {:else if activeCategory === 'security'}
            <div class="space-y-6">
                <!-- User Profile Card -->
                <div
                    class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center text-2xl font-bold text-white shadow-lg shadow-indigo-600/30"
                        >
                            👤
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-lg font-bold text-white">
                                    {page.props.auth?.user?.name ||
                                        'Administrator'}
                                </h2>
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 uppercase"
                                >
                                    Admin
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">
                                {page.props.auth?.user?.email ||
                                    'admin@example.com'} · Verified Admin Session
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onclick={handleLogout}
                        class="px-4 py-2.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/30 rounded-xl text-xs font-semibold transition-all cursor-pointer self-start sm:self-auto"
                    >
                        🚪 Sign Out
                    </button>
                </div>

                <!-- Create Team Member Account Form -->
                <div
                    class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-6"
                >
                    <div>
                        <h2 class="text-base font-bold text-gray-100">
                            ➕ Register New Creator Account
                        </h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Add team members or co-creators with access to the
                            Aiffiliate pipeline.
                        </p>
                    </div>

                    {#if userSuccessMsg}
                        <div
                            class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 font-medium"
                        >
                            ✓ {userSuccessMsg}
                        </div>
                    {/if}
                    {#if userErrorMsg}
                        <div
                            class="p-3.5 bg-red-500/10 border border-red-500/30 rounded-xl text-xs text-red-300 font-medium"
                        >
                            ⚠️ {userErrorMsg}
                        </div>
                    {/if}

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label
                                for="u_name"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                Full Name *
                            </label>
                            <input
                                id="u_name"
                                type="text"
                                bind:value={regName}
                                placeholder="Jane Doe"
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                        </div>

                        <div>
                            <label
                                for="u_email"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                Email Address *
                            </label>
                            <input
                                id="u_email"
                                type="email"
                                bind:value={regEmail}
                                placeholder="jane@example.com"
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                        </div>

                        <div>
                            <label
                                for="u_pass"
                                class="block text-xs font-semibold text-gray-300 mb-1.5"
                            >
                                Password *
                            </label>
                            <input
                                id="u_pass"
                                type="password"
                                bind:value={regPassword}
                                placeholder="••••••••"
                                class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="button"
                            onclick={handleCreateUser}
                            disabled={creatingUser ||
                                !regName.trim() ||
                                !regEmail.trim() ||
                                !regPassword.trim()}
                            class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all cursor-pointer"
                        >
                            {creatingUser
                                ? 'Creating Account...'
                                : '➕ Create Account'}
                        </button>
                    </div>
                </div>

                <!-- Existing Users List -->
                {#if users.length > 0}
                    <div
                        class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-4"
                    >
                        <h3 class="text-sm font-bold text-gray-200">
                            👥 Existing Team Members ({users.length})
                        </h3>
                        <div class="divide-y divide-gray-800/60">
                            {#each users as u (u.id)}
                                <div
                                    class="py-3 flex items-center justify-between"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-bold text-gray-100"
                                        >
                                            {u.name}
                                        </p>
                                        <p
                                            class="text-[11px] text-gray-500 font-mono"
                                        >
                                            {u.email}
                                        </p>
                                    </div>
                                    <span
                                        class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30"
                                    >
                                        Active
                                    </span>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>
        {/if}

        <!-- Sticky Save Bar at the bottom -->
        <div
            class="flex items-center justify-between bg-gray-900/90 p-4 rounded-2xl border border-gray-800 shadow-2xl sticky bottom-4 z-20 backdrop-blur-2xl mt-8"
        >
            <div class="text-xs flex items-center gap-2">
                {#if saved}
                    <span
                        class="text-emerald-400 flex items-center gap-1 font-semibold"
                    >
                        ✓ Settings saved successfully!
                    </span>
                {/if}
                {#if error}
                    <span class="text-rose-400 font-semibold">{error}</span>
                {/if}
            </div>
            <button
                type="button"
                onclick={handleSaveSettings}
                disabled={saving}
                class="px-8 py-3 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98] cursor-pointer flex items-center gap-2"
            >
                {#if saving}
                    <span class="inline-block animate-spin">🌀</span>
                    <span>Saving...</span>
                {:else}
                    <span>💾 Save Settings</span>
                {/if}
            </button>
        </div>
    </div>
</AppLayout>

<!-- Edit Modal -->
{#if showEditModal}
    <div
        class="fixed inset-0 bg-black/75 backdrop-blur-md z-50 flex items-center justify-center p-4 sm:p-6 animate-fadeIn"
    >
        <div
            class="bg-gray-900 border border-gray-700/80 rounded-3xl p-6 sm:p-8 w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl animate-scaleIn"
        >
            <div
                class="flex items-center justify-between pb-4 border-b border-gray-800 flex-shrink-0"
            >
                <div>
                    <h3 class="text-base font-bold text-gray-100">
                        Edit Social Account: {editName}
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Update channel credentials, access tokens, and per-page
                        AI settings.
                    </p>
                </div>
                <button
                    type="button"
                    onclick={() => (showEditModal = false)}
                    class="text-gray-400 hover:text-white p-1 text-sm cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <div
                class="space-y-4 overflow-y-auto py-4 flex-1 pr-1 custom-scrollbar"
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label
                            for="ed_plat"
                            class="block text-xs font-semibold text-gray-300 mb-1.5"
                        >
                            Platform
                        </label>
                        <select
                            id="ed_plat"
                            bind:value={editPlatform}
                            class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 focus:border-indigo-500 outline-none"
                        >
                            <option value="facebook">📘 Facebook Page</option>
                            <option value="tiktok">🎵 TikTok Account</option>
                            <option value="shopee">🛍️ Shopee Shop</option>
                            <option value="instagram"
                                >📸 Instagram Account</option
                            >
                            <option value="youtube">🎥 YouTube Channel</option>
                            <option value="telegram">✈️ Telegram Channel</option
                            >
                        </select>
                    </div>

                    <div>
                        <label
                            for="ed_name"
                            class="block text-xs font-semibold text-gray-300 mb-1.5"
                        >
                            Display Name
                        </label>
                        <input
                            id="ed_name"
                            type="text"
                            bind:value={editName}
                            class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs text-gray-100 focus:border-indigo-500 outline-none"
                        />
                    </div>
                </div>

                <div>
                    <label
                        for="ed_acc_id"
                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                    >
                        Account / Page ID
                    </label>
                    <input
                        id="ed_acc_id"
                        type="text"
                        bind:value={editAccountId}
                        class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs font-mono text-gray-100 focus:border-indigo-500 outline-none"
                    />
                </div>

                <div>
                    <label
                        for="ed_token"
                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                    >
                        Access Token (Leave as •••••••• to keep unchanged)
                    </label>
                    <input
                        id="ed_token"
                        type="password"
                        bind:value={editAccessToken}
                        class="w-full h-11 rounded-xl border border-gray-800 bg-gray-950/80 px-4 text-xs font-mono text-gray-100 focus:border-indigo-500 outline-none"
                    />
                </div>

                <!-- Custom AI Context -->
                <div>
                    <label
                        for="ed_ai"
                        class="block text-xs font-semibold text-indigo-300 mb-1.5"
                    >
                        🤖 Per-Page Custom AI System Context
                    </label>
                    <textarea
                        id="ed_ai"
                        rows="2"
                        bind:value={editAiContext}
                        class="w-full rounded-xl border border-gray-800 bg-gray-950/80 p-3 text-xs text-gray-100 focus:border-indigo-500 outline-none resize-none"
                    ></textarea>
                </div>

                <!-- Default Hashtags -->
                <div>
                    <span
                        class="block text-xs font-semibold text-gray-300 mb-1.5"
                    >
                        🏷️ Default Hashtags
                    </span>
                    <div
                        class="flex flex-wrap gap-1.5 p-3 bg-gray-950/80 rounded-xl border border-gray-800 min-h-[44px] items-center mb-2"
                    >
                        {#if parseHashtagString(editDefaultHashtags).length === 0}
                            <span class="text-xs text-gray-600 italic"
                                >No tags set.</span
                            >
                        {:else}
                            {#each parseHashtagString(editDefaultHashtags) as tag}
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30"
                                >
                                    {tag}
                                    <button
                                        type="button"
                                        onclick={() =>
                                            (editDefaultHashtags =
                                                removeHashtagFromList(
                                                    editDefaultHashtags,
                                                    tag,
                                                ))}
                                        class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-xs cursor-pointer"
                                    >
                                        ✕
                                    </button>
                                </span>
                            {/each}
                        {/if}
                    </div>
                    <div class="flex gap-2">
                        <input
                            bind:value={editTagInput}
                            placeholder="#TechSulitDeals or ShopeePH"
                            onkeydown={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    if (editTagInput.trim()) {
                                        editDefaultHashtags = addHashtagToList(
                                            editDefaultHashtags,
                                            editTagInput,
                                        );
                                        editTagInput = '';
                                    }
                                }
                            }}
                            class="flex-1 h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                        />
                        <button
                            type="button"
                            onclick={() => {
                                if (editTagInput.trim()) {
                                    editDefaultHashtags = addHashtagToList(
                                        editDefaultHashtags,
                                        editTagInput,
                                    );
                                    editTagInput = '';
                                }
                            }}
                            disabled={!editTagInput.trim()}
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 text-gray-200 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                        >
                            + Add Tag
                        </button>
                    </div>
                </div>

                <!-- Compliance -->
                <div
                    class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 space-y-3"
                >
                    <label
                        class="flex items-center gap-2.5 cursor-pointer select-none"
                    >
                        <input
                            type="checkbox"
                            bind:checked={editIsAffiliate}
                            class="w-4 h-4 rounded text-indigo-600 bg-gray-900 border-gray-700 cursor-pointer"
                        />
                        <span class="text-xs font-semibold text-gray-200">
                            ⚡ Is Affiliate Page (Auto-Append Compliance
                            Disclaimers)
                        </span>
                    </label>

                    {#if editIsAffiliate}
                        <div class="pt-2 border-t border-indigo-500/20">
                            <label
                                for="ed_disc"
                                class="block text-[11px] font-medium text-gray-400 mb-1"
                            >
                                Affiliate Disclosure Text
                            </label>
                            <input
                                id="ed_disc"
                                type="text"
                                bind:value={editDisclosure}
                                class="w-full h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs text-gray-100 focus:border-indigo-500 outline-none"
                            />
                        </div>
                    {/if}
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800 flex-shrink-0"
            >
                <button
                    type="button"
                    onclick={() => (showEditModal = false)}
                    class="px-4 py-2 bg-gray-900 hover:bg-gray-800 text-gray-300 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    onclick={handleEditSocialAccount}
                    disabled={editingAccount ||
                        !editName.trim() ||
                        !editAccountId.trim()}
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition-all cursor-pointer"
                >
                    {editingAccount ? 'Saving...' : 'Save Changes'}
                </button>
            </div>
        </div>
    </div>
{/if}
