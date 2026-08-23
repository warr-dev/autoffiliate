<script lang="ts">
    import { router, page } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

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
    let newGlobalTagInput = $state('');
    const presetHashtags = [
        '#TechSulitDeals',
        '#ShopeePH',
        '#ShopeeFinds',
        '#SulitDeals',
        '#BudolFinds',
        '#ShopeePayDay',
        '#AffiliatePH',
        '#TechDeals',
        '#ShopeeSale',
    ];
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
    let showAddAutoExchange = $state(false);
    let showManualGuideAdd = $state(false);
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
    let showEditAutoExchange = $state(false);
    let showManualGuideEdit = $state(false);
    let editTagInput = $state('');

    // Auto Token Exchange State
    let exchangeUserToken = $state('');
    let exchangeAppId = $state('');
    let exchangeAppSecret = $state('');
    let exchanging = $state(false);
    let exchangeError = $state('');
    let exchangeSuccess = $state('');
    let suggestingTags = $state(false);
    let discoveredPages = $state<Array<{ id: string; name: string; access_token: string }>>([]);
    let tokenVerifyStatuses = $state<Record<string, { valid: boolean; page_name?: string; expires_in_days?: any; is_long_lived?: boolean; error?: string; checking?: boolean }>>({});
    let editVerifyStatus = $state<{ valid: boolean; page_name?: string; expires_in_days?: any; is_long_lived?: boolean; error?: string; checking?: boolean } | null>(null);

    async function handleAiSuggestHashtags(
        accountName: string,
        platform: string,
        aiContext: string | undefined,
        updateCallback: (tagsStr: string) => void,
    ) {
        suggestingTags = true;

        try {
            const csrfToken = (
                document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
            )?.content || '';

            const res = await fetch('/settings/suggest-hashtags', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    name: accountName.trim() || 'Deals',
                    platform,
                    context: aiContext?.trim() || '',
                }),
            });
            const data = await res.json();

            if (data?.hashtags && data.hashtags.length > 0) {
                updateCallback(data.hashtags.join(' '));
            }
        } catch (e: any) {
            console.warn('AI suggest hashtags error:', e);
        } finally {
            suggestingTags = false;
        }
    }

    async function handleAutoExchangeToken(
        targetPageId: string,
        callback: (tok: string, name?: string, id?: string) => void,
    ) {
        if (!exchangeUserToken.trim()) {
            exchangeError = 'User Access Token is required for auto-exchange.';

            return;
        }

        exchanging = true;
        exchangeError = '';
        exchangeSuccess = '';
        discoveredPages = [];

        try {
            const csrfToken = (
                document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
            )?.content;

            const res = await fetch('/settings/token/exchange', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    fb_page_token: exchangeUserToken.trim(),
                    fb_app_id: exchangeAppId.trim() || fb_app_id || undefined,
                    fb_app_secret: exchangeAppSecret.trim() || fb_app_secret || undefined,
                    fb_page_id: targetPageId.trim() || undefined,
                }),
            });

            const data = await res.json();

            if (data.success && data.page_token) {
                callback(data.page_token, data.page_name, data.page_id);

                if (data.pages && data.pages.length > 0) {
                    discoveredPages = data.pages;
                }

                exchangeSuccess = `Permanent Page Token generated for "${data.page_name || 'Facebook Page'}" (ID: ${data.page_id})!`;

                if (exchangeAppId.trim()) {
fb_app_id = exchangeAppId.trim();
}

                if (exchangeAppSecret.trim()) {
fb_app_secret = exchangeAppSecret.trim();
}
            } else {
                exchangeError =
                    data.error ||
                    'Token exchange failed. Please check your App ID, Secret, and permissions.';
            }
        } catch (err: any) {
            exchangeError = err.message || 'Network error during token exchange.';
        } finally {
            exchanging = false;
        }
    }

    async function handleVerifyAccountToken(account: any) {
        const id = account.id;
        tokenVerifyStatuses[id] = { valid: false, checking: true };

        try {
            const csrfToken = (
                document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
            )?.content;

            const res = await fetch('/settings/token/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    account_id: account.account_id,
                    access_token: account.access_token,
                    fb_app_id: fb_app_id || undefined,
                    fb_app_secret: fb_app_secret || undefined,
                }),
            });

            const data = await res.json();
            tokenVerifyStatuses[id] = {
                valid: !!data.valid,
                page_name: data.page_name,
                expires_in_days: data.expires_in_days,
                is_long_lived: data.is_long_lived,
                error: data.error,
                checking: false,
            };
        } catch (err: any) {
            tokenVerifyStatuses[id] = {
                valid: false,
                error: err.message || 'Verification request failed',
                checking: false,
            };
        }
    }

    // Test Post on Social Account State
    let testPostStatuses = $state<
        Record<
            string,
            {
                loading: boolean;
                success?: boolean;
                message?: string;
                error?: string;
                post_id?: string;
                post_url?: string;
            }
        >
    >({});

    async function handleSendTestPost(account: any) {
        const id = account.id;
        testPostStatuses[id] = { loading: true };

        try {
            const csrfToken = (
                document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
            )?.content;

            const res = await fetch(`/settings/social-accounts/${id}/test-post`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            });

            const data = await res.json();

            if (res.ok && data.success) {
                testPostStatuses[id] = {
                    loading: false,
                    success: true,
                    message: data.message || 'Test post published successfully!',
                    post_id: data.facebook_post_id,
                    post_url: data.facebook_post_url,
                };
            } else {
                testPostStatuses[id] = {
                    loading: false,
                    success: false,
                    error: data.error || 'Failed to publish test post.',
                };
            }
        } catch (err: any) {
            testPostStatuses[id] = {
                loading: false,
                success: false,
                error: err.message || 'Request failed',
            };
        }
    }

    async function handleVerifyEditToken() {
        if (!editAccessToken || editAccessToken === '••••••••') {
            editVerifyStatus = { valid: false, error: 'Enter a valid access token to verify.', checking: false };

            return;
        }

        editVerifyStatus = { valid: false, checking: true };

        try {
            const csrfToken = (
                document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement
            )?.content;

            const res = await fetch('/settings/token/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    account_id: editAccountId,
                    access_token: editAccessToken,
                    fb_app_id: fb_app_id || undefined,
                    fb_app_secret: fb_app_secret || undefined,
                }),
            });

            const data = await res.json();
            editVerifyStatus = {
                valid: !!data.valid,
                page_name: data.page_name,
                expires_in_days: data.expires_in_days,
                is_long_lived: data.is_long_lived,
                error: data.error,
                checking: false,
            };
        } catch (err: any) {
            editVerifyStatus = {
                valid: false,
                error: err.message || 'Verification request failed',
                checking: false,
            };
        }
    }

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
        if (!str) {
return [];
}

        return str
            .split(/\s+/)
            .map((t) => t.trim())
            .filter((t) => t.length > 0)
            .map((t) => (t.startsWith('#') ? t : `#${t}`));
    }

    function addHashtagToList(currentStr: string, input: string): string {
        const raw = input.trim();

        if (!raw) {
return currentStr;
}

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
        if (!newName.trim() || !newAccountId.trim()) {
return;
}

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
        showEditAutoExchange = false;
        editVerifyStatus = null;
        discoveredPages = [];
        showEditModal = true;
    }

    function handleEditSocialAccount() {
        if (!editId || !editName.trim() || !editAccountId.trim()) {
return;
}

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
        if (!n8n_outbound_webhook.trim()) {
return;
}

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
        if (!regName.trim() || !regEmail.trim() || !regPassword.trim()) {
return;
}

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
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center cursor-pointer
                    {activeCategory === 'social'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                Social Media
            </button>

            <button
                type="button"
                onclick={() => (activeCategory = 'ai')}
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center cursor-pointer
                    {activeCategory === 'ai'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                AI Captions
            </button>

            <button
                type="button"
                onclick={() => (activeCategory = 'security')}
                class="py-3 px-2 sm:px-4 rounded-xl font-semibold text-xs sm:text-sm transition-all duration-200 flex items-center justify-center cursor-pointer
                    {activeCategory === 'security'
                    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-600/30'
                    : 'text-gray-400 hover:text-gray-200 hover:bg-gray-900/60'}"
            >
                Security & Users
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
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold shadow-lg shadow-indigo-500/25 transition-all cursor-pointer self-start sm:self-auto"
                        >
                            Connect Account
                        </button>
                    </div>

                    <!-- Add Social Account Form -->
                    {#if showAddSocialModal}
                        <div class="mb-6 p-6 rounded-2xl bg-indigo-950/30 border border-indigo-500/30 space-y-5 animate-slideUp">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-gray-100">Connect Social Media Account</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">These connect the platform to your social media account so posts can be published.</p>
                                </div>
                                <button type="button" onclick={() => showAddSocialModal = false} class="text-gray-500 hover:text-gray-300 p-1 cursor-pointer">✕</button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="new_account_platform">Platform Network</label>
                                    <select
                                        id="new_account_platform"
                                        class="input text-xs bg-gray-900"
                                        bind:value={newPlatform}
                                    >
                                        <option value="facebook">📘 Facebook Page</option>
                                        <option value="tiktok">🎵 TikTok Account</option>
                                        <option value="shopee">🛍️ Shopee Shop</option>
                                        <option value="instagram">📸 Instagram Account</option>
                                        <option value="youtube">🎥 YouTube Channel</option>
                                        <option value="telegram">✈️ Telegram Channel</option>
                                    </select>
                                </div>

                                <!-- Account / Page ID -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="new_fb_id">
                                        {newPlatform === 'facebook' ? 'Facebook Page ID' : 'Account / Channel ID'}
                                    </label>
                                    <input
                                        id="new_fb_id"
                                        class="input font-mono text-xs"
                                        bind:value={newAccountId}
                                        placeholder={newPlatform === 'facebook' ? '1184127881441932' : '@handle or Channel ID'}
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">
                                        {#if newPlatform === 'facebook'}
                                            Found in your Page's <strong>About</strong> section → <strong>Page ID</strong>. Or check the URL: <code class="text-gray-400">facebook.com/<strong class="text-indigo-400">1184127881441932</strong></code>
                                        {:else}
                                            Unique handle or ID for this connected account.
                                        {/if}
                                    </p>
                                </div>

                                <!-- Account / Page Name -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="new_fb_name">
                                        {newPlatform === 'facebook' ? 'Facebook Page Name' : 'Account Display Name'}
                                    </label>
                                    <input
                                        id="new_fb_name"
                                        class="input text-xs"
                                        bind:value={newName}
                                        placeholder="Tech Sulit Deals"
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">Just a label for display — doesn't affect posting.</p>
                                </div>

                                <!-- Access Token -->
                                <div>
                                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="new_fb_token">
                                        {newPlatform === 'facebook' ? 'Page Access Token' : 'Access Token / API Key'}
                                    </label>
                                    <input
                                        id="new_fb_token"
                                        type="password"
                                        class="input font-mono text-xs"
                                        bind:value={newAccessToken}
                                        placeholder={newPlatform === 'facebook' ? 'EAAl4jZCR...' : 'Token or Secret'}
                                    />
                                    <p class="text-xs text-gray-500 mt-1.5">
                                        Paste a <strong>Page-scoped token</strong> directly, or use the <strong>Auto-Exchange</strong> flow below to convert a short-lived User Token automatically.
                                    </p>
                                </div>

                                <!-- Per-Page AI Context -->
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-300 mb-1.5" for="new_fb_ai_context">
                                        🤖 Custom AI System Prompt / Brand Context (Optional)
                                    </label>
                                    <textarea
                                        id="new_fb_ai_context"
                                        rows="3"
                                        class="input text-xs resize-none bg-gray-900/90"
                                        bind:value={newAiContext}
                                        placeholder="e.g. Focus on gaming gear & tech gadgets. Use high-energy Taglish tone, emphasize discount percentages, and add emojis!"
                                    ></textarea>
                                    <p class="text-[11px] text-gray-500 mt-1">Appended to AI system prompts whenever generating captions for this page.</p>
                                </div>

                                <!-- Per-Page Default Hashtags (Tagified + AI Suggestions) -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-semibold text-gray-300" for="new_tag_input_add">
                                            🏷️ Default Hashtags for this Account
                                        </label>
                                        <button
                                            type="button"
                                            onclick={() => handleAiSuggestHashtags(newName, newPlatform, newAiContext, (tagsStr) => {
                                                newDefaultHashtags = tagsStr;
                                            })}
                                            disabled={suggestingTags}
                                            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition-colors cursor-pointer disabled:opacity-50"
                                        >
                                            {#if suggestingTags}
                                                <div class="loading-spinner w-2.5 h-2.5"></div>
                                                <span>Suggesting...</span>
                                            {:else}
                                                <span>Ask AI for Suggested Tags</span>
                                            {/if}
                                        </button>
                                    </div>

                                    <!-- Active Tag Pills -->
                                    <div class="flex flex-wrap gap-1.5 p-3 bg-gray-950/80 rounded-xl border border-gray-800 min-h-[46px] items-center mb-2">
                                        {#if parseHashtagString(newDefaultHashtags).length === 0}
                                            <span class="text-xs text-gray-600 italic">No default tags set. Type a tag below or click Ask AI above.</span>
                                        {:else}
                                            {#each parseHashtagString(newDefaultHashtags) as tag (tag)}
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                                    {tag}
                                                    <button
                                                        type="button"
                                                        onclick={() => newDefaultHashtags = removeHashtagFromList(newDefaultHashtags, tag)}
                                                        class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-xs transition-colors cursor-pointer"
                                                        title="Remove tag"
                                                    >
                                                        ✕
                                                    </button>
                                                </span>
                                            {/each}
                                        {/if}
                                    </div>

                                    <!-- Add Tag Input Row -->
                                    <div class="flex gap-2">
                                        <input
                                            id="new_tag_input_add"
                                            class="input text-xs font-mono flex-1"
                                            bind:value={newTagInputAdd}
                                            placeholder="#TechSulitDeals or ShopeePH"
                                            onkeydown={(e) => {
                                                if (e.key === 'Enter') {
                                                    e.preventDefault();

                                                    if (newTagInputAdd.trim()) {
                                                        newDefaultHashtags = addHashtagToList(newDefaultHashtags, newTagInputAdd);
                                                        newTagInputAdd = '';
                                                    }
                                                }
                                            }}
                                        />
                                        <button
                                            type="button"
                                            onclick={() => {
                                                if (newTagInputAdd.trim()) {
                                                    newDefaultHashtags = addHashtagToList(newDefaultHashtags, newTagInputAdd);
                                                    newTagInputAdd = '';
                                                }
                                            }}
                                            disabled={!newTagInputAdd.trim()}
                                            class="btn-secondary text-xs px-3 py-1.5 flex-shrink-0 cursor-pointer"
                                        >
                                            Add Tag
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-gray-500 mt-1">Automatically appended to posts published on this specific account.</p>
                                </div>

                                <!-- Per-Page Affiliate Compliance & Disclaimer Ticker -->
                                <div class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 space-y-3">
                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            bind:checked={newIsAffiliate}
                                            class="w-4 h-4 rounded text-indigo-600 bg-gray-900 border-gray-700 focus:ring-indigo-500 cursor-pointer"
                                        />
                                        <div>
                                            <span class="text-xs font-semibold text-gray-200">⚡ Is Affiliate Page (Auto-Append Compliance Disclaimers)</span>
                                            <p class="text-[11px] text-gray-400">Automatically appends affiliate link disclaimers to posts published on this page.</p>
                                        </div>
                                    </label>

                                    {#if newIsAffiliate}
                                        <div class="pt-2 border-t border-indigo-500/20 animate-fadeIn">
                                            <label class="block text-[11px] font-medium text-gray-300 mb-1" for="new_disclosure">
                                                📜 Per-Page Affiliate Disclosure / Disclaimer
                                            </label>
                                            <input
                                                id="new_disclosure"
                                                class="input text-xs"
                                                bind:value={newDisclosure}
                                                placeholder="Affiliate link. Price and availability may change anytime."
                                            />
                                        </div>
                                    {/if}
                                </div>

                                <!-- Guide accordion & Auto Exchange -->
                                {#if newPlatform === 'facebook'}
                                    <div>
                                        <button
                                            type="button"
                                            onclick={() => showManualGuideAdd = !showManualGuideAdd}
                                            class="flex items-center gap-2 text-xs text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer"
                                        >
                                            <span class="text-xs transition-transform duration-200 {showManualGuideAdd ? 'rotate-90' : ''}">▶</span>
                                            How to get a Page Access Token (manual)
                                        </button>

                                        {#if showManualGuideAdd}
                                            <div class="mt-3 p-4 rounded-xl bg-gray-900/80 border border-gray-800 text-xs space-y-2.5 animate-slideUp">
                                                <p class="text-gray-300 font-medium">Step-by-step:</p>
                                                <ol class="text-gray-400 space-y-1.5 list-decimal list-inside">
                                                    <li>Go to <a href="https://developers.facebook.com/tools/explorer" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline">Graph API Explorer</a></li>
                                                    <li>Select your app → <strong>Page Access Token</strong></li>
                                                    <li>Click <strong>Generate Access Token</strong> → check <code>pages_manage_posts</code></li>
                                                    <li>Click <strong>Get Page Token</strong> dropdown → select your Facebook Page</li>
                                                    <li>Copy the long string (starts with <code>EAAl...</code>) and paste above</li>
                                                </ol>
                                                <p class="text-amber-400/80 text-[11px] mt-1">
                                                    ⚠️ Manually obtained tokens expire eventually. For a cleaner flow, use <strong>Auto-Exchange</strong> below.
                                                </p>
                                            </div>
                                        {/if}
                                    </div>

                                    <!-- Auto Token Exchange per page -->
                                    <div class="pt-3 border-t border-gray-800/80">
                                        <button
                                            type="button"
                                            onclick={() => showAddAutoExchange = !showAddAutoExchange}
                                            class="flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer"
                                        >
                                            <span class="text-xs transition-transform duration-200 {showAddAutoExchange ? 'rotate-90' : ''}">▶</span>
                                            Auto Token Exchange (Convert short User Token -> 60-day Page Token)
                                        </button>

                                        {#if showAddAutoExchange}
                                            <div class="mt-3 p-4 rounded-xl bg-gray-900/90 border border-indigo-500/30 space-y-3.5 animate-slideUp">
                                                <div class="p-3 rounded-lg bg-indigo-950/40 border border-indigo-500/20 text-xs space-y-2">
                                                    <p class="font-semibold text-indigo-300 flex items-center gap-1.5">
                                                        <span>📘</span> How to get your App ID, Secret & Short-Lived User Token:
                                                    </p>
                                                    <ol class="text-gray-400 space-y-1 list-decimal list-inside text-[11px]">
                                                        <li>Go to <a href="https://developers.facebook.com/apps" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline font-mono">developers.facebook.com/apps</a> → Select your App.</li>
                                                        <li>Copy <strong>App ID</strong> & <strong>App Secret</strong> from <strong>App Settings → Basic</strong>.</li>
                                                        <li>Open <a href="https://developers.facebook.com/tools/explorer" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline font-mono">Graph API Explorer</a> → Set <strong>User Token</strong>.</li>
                                                        <li>Grant <code>pages_manage_posts</code>, <code>pages_show_list</code>, & <code>pages_read_engagement</code> permissions → Click <strong>Generate Access Token</strong>.</li>
                                                        <li>Paste the <strong>App ID</strong>, <strong>App Secret</strong>, and <strong>User Token</strong> below, then click <strong>Generate & Set Token</strong>!</li>
                                                    </ol>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-[11px] font-medium text-gray-300 mb-1" for="new_exchange_app_id">Facebook App ID</label>
                                                        <input
                                                            id="new_exchange_app_id"
                                                            class="input text-xs font-mono"
                                                            bind:value={exchangeAppId}
                                                            placeholder={fb_app_id || 'App ID'}
                                                        />
                                                    </div>
                                                    <div>
                                                        <label class="block text-[11px] font-medium text-gray-300 mb-1" for="new_exchange_app_secret">Facebook App Secret</label>
                                                        <input
                                                            id="new_exchange_app_secret"
                                                            type="password"
                                                            class="input text-xs font-mono"
                                                            bind:value={exchangeAppSecret}
                                                            placeholder={fb_app_secret ? '••••••••' : 'App Secret'}
                                                        />
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-[11px] font-medium text-gray-300 mb-1" for="new_exchange_user_token">Short-Lived User Access Token</label>
                                                    <input
                                                        id="new_exchange_user_token"
                                                        type="password"
                                                        class="input text-xs font-mono"
                                                        bind:value={exchangeUserToken}
                                                        placeholder="EAAB..."
                                                    />
                                                </div>

                                                {#if exchangeError}
                                                    <p class="text-xs text-red-400 bg-red-500/10 p-2.5 rounded-lg border border-red-500/20">{exchangeError}</p>
                                                {/if}
                                                {#if exchangeSuccess}
                                                    <p class="text-xs text-emerald-400 bg-emerald-500/10 p-2.5 rounded-lg border border-emerald-500/20">{exchangeSuccess}</p>
                                                {/if}

                                                {#if discoveredPages.length > 1}
                                                    <div class="p-3 bg-gray-900 rounded-lg border border-indigo-500/30 space-y-2">
                                                        <span class="block text-[11px] font-semibold text-indigo-300">Select Facebook Page:</span>
                                                        <div class="grid grid-cols-1 gap-1.5 max-h-36 overflow-y-auto custom-scrollbar">
                                                            {#each discoveredPages as pg (pg.id)}
                                                                <button
                                                                    type="button"
                                                                    onclick={() => {
                                                                        newAccountId = pg.id;
                                                                        newName = pg.name;
                                                                        newAccessToken = pg.access_token;
                                                                        exchangeSuccess = `Selected "${pg.name}" (${pg.id})!`;
                                                                    }}
                                                                    class="p-2 rounded-lg bg-gray-950 hover:bg-indigo-950/60 border border-gray-800 hover:border-indigo-500/50 text-left text-xs text-gray-200 flex items-center justify-between cursor-pointer transition-all {newAccountId === pg.id ? 'border-indigo-500 bg-indigo-950/40 text-white' : ''}"
                                                                >
                                                                    <span class="font-semibold truncate">{pg.name}</span>
                                                                    <span class="text-[10px] font-mono text-gray-400 flex-shrink-0 ml-2">ID: {pg.id}</span>
                                                                </button>
                                                            {/each}
                                                        </div>
                                                    </div>
                                                {/if}

                                                <div class="flex justify-end">
                                                    <button
                                                        type="button"
                                                        onclick={() => handleAutoExchangeToken(newAccountId, (tok, pName, pId) => {
                                                            newAccessToken = tok;

                                                            if (pName && !newName) {
newName = pName;
}

                                                            if (pId && !newAccountId) {
newAccountId = pId;
}
                                                        })}
                                                        disabled={exchanging || !exchangeUserToken.trim()}
                                                        class="btn-secondary text-xs px-4 py-2 flex items-center gap-2 cursor-pointer"
                                                    >
                                                        {#if exchanging}
                                                            <div class="loading-spinner"></div>
                                                            <span>Exchanging Token...</span>
                                                        {:else}
                                                            <span>Generate & Set Token</span>
                                                        {/if}
                                                    </button>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" onclick={() => showAddSocialModal = false} class="btn-secondary text-xs px-4 py-2 cursor-pointer">Cancel</button>
                                <button
                                    type="button"
                                    onclick={handleAddSocialAccount}
                                    disabled={addingAccount || !newAccountId.trim()}
                                    class="btn-primary text-xs px-6 py-2 flex items-center gap-2 font-semibold cursor-pointer"
                                >
                                    {#if addingAccount}
                                        <div class="loading-spinner"></div>
                                        <span>Saving...</span>
                                    {:else}
                                        <span>Connect Account</span>
                                    {/if}
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
                                                class="text-xs text-gray-500 font-mono mt-0.5 truncate flex items-center gap-2 flex-wrap"
                                            >
                                                <span>ID: {account.account_id || 'Not set'}</span>
                                                {#if account.access_token}
                                                    <span class="text-emerald-400 font-sans">✓ Token Configured</span>
                                                {:else}
                                                    <span class="text-amber-400 font-sans">⚠️ No Token</span>
                                                {/if}

                                                {#if account.platform === 'facebook' && account.access_token}
                                                    {@const st = tokenVerifyStatuses[account.id]}
                                                    {#if st}
                                                        {#if st.checking}
                                                            <span class="text-indigo-400 text-[11px] font-sans animate-pulse">🌀 Checking...</span>
                                                        {:else if st.valid}
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-sans font-semibold {st.is_long_lived ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30'}">
                                                                {st.expires_in_days === 'never' ? '♾️ Permanent Page Token' : `⏱️ ${st.expires_in_days}d remaining`}
                                                            </span>
                                                        {:else}
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-sans font-semibold bg-red-500/20 text-red-300 border border-red-500/30">
                                                                ❌ Invalid
                                                            </span>
                                                        {/if}
                                                    {/if}
                                                {/if}
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="flex items-center gap-1 flex-shrink-0"
                                    >
                                        {#if account.platform === 'facebook'}
                                            <button
                                                type="button"
                                                onclick={() => handleSendTestPost(account)}
                                                disabled={testPostStatuses[account.id]?.loading}
                                                class="p-2 rounded-lg text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10 transition-colors cursor-pointer disabled:opacity-50"
                                                title="Publish live test post to Facebook"
                                            >
                                                {#if testPostStatuses[account.id]?.loading}
                                                    <div class="loading-spinner w-4 h-4"></div>
                                                {:else}
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                {/if}
                                            </button>
                                            <button
                                                type="button"
                                                onclick={() => handleVerifyAccountToken(account)}
                                                class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors cursor-pointer"
                                                title="Verify Token & Expiry Status"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                            </button>
                                        {/if}
                                        <button
                                            type="button"
                                            onclick={() => handleToggleAccount(account.id)}
                                            class="p-2 rounded-lg transition-colors cursor-pointer {account.is_enabled ? 'text-emerald-400 hover:bg-emerald-500/10' : 'text-gray-500 hover:bg-gray-800'}"
                                            title={account.is_enabled ? 'Pause / Disable account' : 'Activate / Enable account'}
                                        >
                                            {#if account.is_enabled}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            {:else}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {/if}
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() => openEditModal(account)}
                                            class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors cursor-pointer"
                                            title="Edit account details"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            onclick={() => handleDeleteAccount(account.id)}
                                            class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer"
                                            title="Delete account"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {#if testPostStatuses[account.id]}
                                    {@const tp = testPostStatuses[account.id]}
                                    {#if tp.loading}
                                        <div class="mt-2 p-2.5 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-indigo-300 text-xs flex items-center gap-2 animate-fadeIn">
                                            <div class="loading-spinner w-3.5 h-3.5"></div>
                                            <span>Publishing live verification test post to <strong>{account.name}</strong>...</span>
                                        </div>
                                    {:else if tp.success}
                                        <div class="mt-2 p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between gap-2 animate-fadeIn">
                                            <span>✅ {tp.message}</span>
                                            {#if tp.post_url}
                                                <a href={tp.post_url} target="_blank" rel="noreferrer" class="underline text-indigo-300 hover:text-white font-semibold flex-shrink-0">
                                                    View Post on Facebook ➔
                                                </a>
                                            {/if}
                                        </div>
                                    {:else if tp.error}
                                        <div class="mt-2 p-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs animate-fadeIn">
                                            ❌ {tp.error}
                                        </div>
                                    {/if}
                                {/if}
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
                                        ? 'Copied!'
                                        : 'Copy Inbound URL'}
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
                                Outbound Webhook (Broadcast Published Post to
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
                                        : 'Test Webhook'}
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

                    <!-- Global Default Hashtags -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="glob_tags_input" class="block text-xs font-semibold text-gray-300">
                                🏷️ Global Default Hashtags
                            </label>
                            <span class="text-[11px] text-gray-500">Click preset to add/remove</span>
                        </div>

                        <!-- Preset Hashtags row -->
                        <div class="flex flex-wrap gap-1.5">
                            {#each presetHashtags as preset}
                                {@const isActive = parseHashtagString(default_hashtags).some(t => t.toLowerCase() === preset.toLowerCase())}
                                <button
                                    type="button"
                                    onclick={() => {
                                        if (isActive) {
                                            default_hashtags = removeHashtagFromList(default_hashtags, preset);
                                        } else {
                                            default_hashtags = addHashtagToList(default_hashtags, preset);
                                        }
                                    }}
                                    class="px-2.5 py-1 text-[11px] font-mono rounded-lg transition-all cursor-pointer font-medium border {isActive ? 'bg-indigo-600/30 text-indigo-200 border-indigo-500/50 shadow-sm' : 'bg-gray-800/80 text-gray-400 border-gray-700 hover:text-gray-200 hover:bg-gray-800'}"
                                >
                                    {preset}
                                </button>
                            {/each}
                        </div>

                        <!-- Active Tag Pills -->
                        <div class="flex flex-wrap gap-1.5 p-3 bg-gray-950/80 rounded-xl border border-gray-800 min-h-[44px] items-center">
                            {#if parseHashtagString(default_hashtags).length === 0}
                                <span class="text-xs text-gray-600 italic">No global tags configured. Click presets above or add below.</span>
                            {:else}
                                {#each parseHashtagString(default_hashtags) as tag (tag)}
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                        {tag}
                                        <button
                                            type="button"
                                            onclick={() => (default_hashtags = removeHashtagFromList(default_hashtags, tag))}
                                            class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-xs cursor-pointer"
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
                                id="glob_tags_input"
                                bind:value={newGlobalTagInput}
                                placeholder="#TechSulitDeals or ShopeePH"
                                onkeydown={(e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();

                                        if (newGlobalTagInput.trim()) {
                                            default_hashtags = addHashtagToList(default_hashtags, newGlobalTagInput);
                                            newGlobalTagInput = '';
                                        }
                                    }
                                }}
                                class="flex-1 h-10 rounded-xl border border-gray-800 bg-gray-950/80 px-3.5 text-xs font-mono text-gray-100 placeholder-gray-600 focus:border-indigo-500 outline-none"
                            />
                            <button
                                type="button"
                                onclick={() => {
                                    if (newGlobalTagInput.trim()) {
                                        default_hashtags = addHashtagToList(default_hashtags, newGlobalTagInput);
                                        newGlobalTagInput = '';
                                    }
                                }}
                                disabled={!newGlobalTagInput.trim()}
                                class="px-4 py-2 bg-gray-800 hover:bg-gray-700 disabled:opacity-40 text-gray-200 text-xs font-semibold rounded-xl transition-all cursor-pointer"
                            >
                                Add Tag
                            </button>
                        </div>
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
                class="px-8 py-3 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/30 transition-all active:scale-[0.98] cursor-pointer"
            >
                {#if saving}
                    <span>Saving...</span>
                {:else}
                    <span>Save Settings</span>
                {/if}
            </button>
        </div>
    </div>
</AppLayout>

<!-- Edit Facebook Page Modal -->
{#if showEditModal}
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 sm:p-6 animate-fadeIn">
        <div class="card p-6 w-full max-w-lg md:max-w-2xl lg:max-w-3xl max-h-[90vh] flex flex-col shadow-2xl border border-gray-700/80 animate-scaleIn">
            <!-- Header (Fixed) -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-800/80 flex-shrink-0">
                <div>
                    <h3 class="text-lg font-bold text-gray-100">Edit Connected Account</h3>
                    <p class="text-xs text-gray-400 mt-0.5">These connect the platform to your Facebook Page so posts can be published.</p>
                </div>
                <button type="button" onclick={() => showEditModal = false} class="text-gray-400 hover:text-white p-1 cursor-pointer">✕</button>
            </div>

            <!-- Body Content (Scrollable) -->
            <div class="space-y-4 overflow-y-auto py-4 pr-1 flex-1 custom-scrollbar">
                <!-- Page ID -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="edit_fb_page_id">
                        {editPlatform === 'facebook' ? 'Facebook Page ID' : 'Account / Channel ID'}
                    </label>
                    <input
                        id="edit_fb_page_id"
                        bind:value={editAccountId}
                        placeholder={editPlatform === 'facebook' ? '1184127881441932' : '@handle or Channel ID'}
                        class="input text-xs font-mono"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">
                        {#if editPlatform === 'facebook'}
                            Found in your Page's <strong>About</strong> section → <strong>Page ID</strong>. Or check the URL: <code class="text-gray-400">facebook.com/<strong class="text-indigo-400">1184127881441932</strong></code>
                        {:else}
                            Unique handle or ID for this connected account.
                        {/if}
                    </p>
                </div>

                <!-- Page Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="edit_fb_page_name">
                        {editPlatform === 'facebook' ? 'Facebook Page Name' : 'Account Display Name'}
                    </label>
                    <input
                        id="edit_fb_page_name"
                        bind:value={editName}
                        placeholder="Tech Sulit Deals"
                        class="input text-xs"
                    />
                    <p class="text-xs text-gray-500 mt-1.5">Just a label for display — doesn't affect posting.</p>
                </div>

                <!-- Page Access Token -->
                <div>
                    <label class="block text-xs font-semibold text-gray-300 mb-1.5" for="edit_fb_page_token">
                        {editPlatform === 'facebook' ? 'Page Access Token' : 'Access Token / API Key'}
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="edit_fb_page_token"
                            type="password"
                            bind:value={editAccessToken}
                            placeholder={editPlatform === 'facebook' ? 'EAAl4jZCR...' : 'Token or Secret'}
                            class="input text-xs font-mono flex-1"
                        />
                        {#if editPlatform === 'facebook'}
                            <button
                                type="button"
                                onclick={handleVerifyEditToken}
                                class="btn-secondary text-xs px-3 py-2 flex-shrink-0 cursor-pointer"
                            >
                                Verify
                            </button>
                            <button
                                type="button"
                                onclick={() => handleSendTestPost({ id: editId, platform: editPlatform, name: editName, account_id: editAccountId, access_token: editAccessToken })}
                                disabled={testPostStatuses[editId]?.loading}
                                class="btn-primary text-xs px-3 py-2 flex-shrink-0 cursor-pointer flex items-center gap-1.5 font-semibold"
                                title="Publish live test post to this Facebook Page"
                            >
                                {#if testPostStatuses[editId]?.loading}
                                    <div class="loading-spinner w-3 h-3"></div>
                                    <span>Testing...</span>
                                {:else}
                                    <span>Test Post</span>
                                {/if}
                            </button>
                        {/if}
                    </div>
                    {#if editVerifyStatus}
                        <div class="mt-2 p-2.5 rounded-lg text-xs font-mono {editVerifyStatus.valid ? (editVerifyStatus.is_long_lived ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400 border border-amber-500/30') : 'bg-red-500/10 text-red-400 border border-red-500/30'}">
                            {#if editVerifyStatus.checking}
                                <span>Verifying token with Graph API...</span>
                            {:else if editVerifyStatus.valid}
                                <span>Valid Token for "{editVerifyStatus.page_name || 'Page'}" — {editVerifyStatus.expires_in_days === 'never' ? 'Never Expires (Permanent Page Token)' : `Expires in ${editVerifyStatus.expires_in_days} days`}</span>
                            {:else}
                                <span>{editVerifyStatus.error || 'Token verification failed'}</span>
                            {/if}
                        </div>
                    {/if}
                    {#if testPostStatuses[editId]}
                        {@const tp = testPostStatuses[editId]}
                        {#if tp.loading}
                            <div class="mt-2 p-2.5 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-indigo-300 text-xs flex items-center gap-2 animate-fadeIn">
                                <div class="loading-spinner w-3.5 h-3.5"></div>
                                <span>Publishing live verification test post to <strong>{editName}</strong>...</span>
                            </div>
                        {:else if tp.success}
                            <div class="mt-2 p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between gap-2 animate-fadeIn">
                                <span>✅ {tp.message}</span>
                                {#if tp.post_url}
                                    <a href={tp.post_url} target="_blank" rel="noreferrer" class="underline text-indigo-300 hover:text-white font-semibold flex-shrink-0">
                                        View on Facebook ➔
                                    </a>
                                {/if}
                            </div>
                        {:else if tp.error}
                            <div class="mt-2 p-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 text-xs animate-fadeIn">
                                ❌ {tp.error}
                            </div>
                        {/if}
                    {/if}
                    <p class="text-xs text-gray-500 mt-1.5">
                        Paste a <strong>Page-scoped token</strong> directly, or use the <strong>Auto-Exchange</strong> flow below to convert a short-lived User Token automatically.
                        <span class="text-gray-600 block mt-0.5">Leave as <code>••••••••</code> to keep existing token unchanged.</span>
                    </p>
                </div>

                <!-- Per-Page AI Context -->
                <div>
                    <label class="block text-xs font-semibold text-indigo-300 mb-1.5" for="edit_fb_ai_context">
                        🤖 Custom AI System Prompt / Brand Context (Optional)
                    </label>
                    <textarea
                        id="edit_fb_ai_context"
                        rows="3"
                        class="input text-xs resize-none bg-gray-900/90"
                        bind:value={editAiContext}
                        placeholder="e.g. Focus on gaming gear & tech gadgets. Use high-energy Taglish tone, emphasize discount percentages, and add emojis!"
                    ></textarea>
                    <p class="text-[11px] text-gray-500 mt-1">Appended to AI system prompts whenever generating captions for this page.</p>
                </div>

                <!-- Per-Page Default Hashtags (Tagified + AI Suggestions) -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-gray-300" for="edit_tag_input_field">
                            🏷️ Default Hashtags for this Account
                        </label>
                        <button
                            type="button"
                            onclick={() => handleAiSuggestHashtags(editName, editPlatform, editAiContext, (tagsStr) => {
                                editDefaultHashtags = tagsStr;
                            })}
                            disabled={suggestingTags}
                            class="text-xs text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1 transition-colors cursor-pointer disabled:opacity-50"
                        >
                            {#if suggestingTags}
                                <div class="loading-spinner w-2.5 h-2.5"></div>
                                <span>Suggesting...</span>
                            {:else}
                                <span>Ask AI for Suggested Tags</span>
                            {/if}
                        </button>
                    </div>

                    <!-- Active Tag Pills -->
                    <div class="flex flex-wrap gap-1.5 p-3 bg-gray-950/80 rounded-xl border border-gray-800 min-h-[46px] items-center mb-2">
                        {#if parseHashtagString(editDefaultHashtags).length === 0}
                            <span class="text-xs text-gray-600 italic">No default tags set. Type a tag below or click Ask AI above.</span>
                        {:else}
                            {#each parseHashtagString(editDefaultHashtags) as tag (tag)}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-mono font-medium rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    {tag}
                                    <button
                                        type="button"
                                        onclick={() => editDefaultHashtags = removeHashtagFromList(editDefaultHashtags, tag)}
                                        class="text-indigo-400 hover:text-red-400 font-bold ml-0.5 text-xs transition-colors cursor-pointer"
                                        title="Remove tag"
                                    >
                                        ✕
                                    </button>
                                </span>
                            {/each}
                        {/if}
                    </div>

                    <!-- Add Tag Input Row -->
                    <div class="flex gap-2">
                        <input
                            id="edit_tag_input_field"
                            class="input text-xs font-mono flex-1"
                            bind:value={editTagInput}
                            placeholder="#TechSulitDeals or ShopeePH"
                            onkeydown={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();

                                    if (editTagInput.trim()) {
                                        editDefaultHashtags = addHashtagToList(editDefaultHashtags, editTagInput);
                                        editTagInput = '';
                                    }
                                }
                            }}
                        />
                        <button
                            type="button"
                            onclick={() => {
                                if (editTagInput.trim()) {
                                    editDefaultHashtags = addHashtagToList(editDefaultHashtags, editTagInput);
                                    editTagInput = '';
                                }
                            }}
                            disabled={!editTagInput.trim()}
                            class="btn-secondary text-xs px-3 py-1.5 flex-shrink-0 cursor-pointer"
                        >
                            Add Tag
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Automatically appended to posts published on this specific account.</p>
                </div>

                <!-- Per-Page Affiliate Compliance & Disclaimer Ticker -->
                <div class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 space-y-3">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input
                            type="checkbox"
                            bind:checked={editIsAffiliate}
                            class="w-4 h-4 rounded text-indigo-600 bg-gray-900 border-gray-700 focus:ring-indigo-500 cursor-pointer"
                        />
                        <div>
                            <span class="text-xs font-semibold text-gray-200">⚡ Is Affiliate Page (Auto-Append Compliance Disclaimers)</span>
                            <p class="text-[11px] text-gray-400">Automatically appends affiliate link disclaimers to posts published on this page.</p>
                        </div>
                    </label>

                    {#if editIsAffiliate}
                        <div class="pt-2 border-t border-indigo-500/20 animate-fadeIn">
                            <label class="block text-[11px] font-medium text-gray-300 mb-1" for="edit_disclosure">
                                📜 Per-Page Affiliate Disclosure / Disclaimer
                            </label>
                            <input
                                id="edit_disclosure"
                                class="input text-xs"
                                bind:value={editDisclosure}
                                placeholder="Affiliate link. Price and availability may change anytime."
                            />
                        </div>
                    {/if}
                </div>

                <!-- Guide Accordion -->
                {#if editPlatform === 'facebook'}
                    <div>
                        <button
                            type="button"
                            onclick={() => showManualGuideEdit = !showManualGuideEdit}
                            class="flex items-center gap-2 text-xs text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer"
                        >
                            <span class="text-xs transition-transform duration-200 {showManualGuideEdit ? 'rotate-90' : ''}">▶</span>
                            How to get a Page Access Token (manual)
                        </button>

                        {#if showManualGuideEdit}
                            <div class="mt-3 p-4 rounded-xl bg-gray-900/80 border border-gray-800 text-xs space-y-2.5 animate-slideUp">
                                <p class="text-gray-300 font-medium">Step-by-step:</p>
                                <ol class="text-gray-400 space-y-1.5 list-decimal list-inside">
                                    <li>Go to <a href="https://developers.facebook.com/tools/explorer" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline">Graph API Explorer</a></li>
                                    <li>Select your app → <strong>Page Access Token</strong></li>
                                    <li>Click <strong>Generate Access Token</strong> → check <code>pages_manage_posts</code></li>
                                    <li>Click <strong>Get Page Token</strong> dropdown → select <strong>Tech Sulit Deals</strong></li>
                                    <li>Copy the long string (starts with <code>EAAl...</code>) and paste above</li>
                                </ol>
                                <p class="text-amber-400/80 text-[11px] mt-1">
                                    ⚠️ Manually obtained tokens expire eventually. For a cleaner flow, use <strong>Auto-Exchange</strong> below.
                                </p>
                            </div>
                        {/if}
                    </div>

                    <!-- Auto Token Exchange per page -->
                    <div class="pt-3 border-t border-gray-800/80">
                        <button
                            type="button"
                            onclick={() => showEditAutoExchange = !showEditAutoExchange}
                            class="flex items-center gap-2 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors cursor-pointer"
                        >
                            <span class="text-xs transition-transform duration-200 {showEditAutoExchange ? 'rotate-90' : ''}">▶</span>
                            Auto Token Exchange (Convert short User Token -> 60-day Page Token)
                        </button>

                        {#if showEditAutoExchange}
                            <div class="mt-3 p-4 rounded-xl bg-gray-900/90 border border-indigo-500/30 space-y-3.5 animate-slideUp">
                                <div class="p-3 rounded-lg bg-indigo-950/40 border border-indigo-500/20 text-xs space-y-2">
                                    <p class="font-semibold text-indigo-300 flex items-center gap-1.5">
                                        <span>📘</span> How to get your App ID, Secret & Short-Lived User Token:
                                    </p>
                                    <ol class="text-gray-400 space-y-1 list-decimal list-inside text-[11px]">
                                        <li>Go to <a href="https://developers.facebook.com/apps" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline font-mono">developers.facebook.com/apps</a> → Select your App.</li>
                                        <li>Copy <strong>App ID</strong> & <strong>App Secret</strong> from <strong>App Settings → Basic</strong>.</li>
                                        <li>Open <a href="https://developers.facebook.com/tools/explorer" target="_blank" rel="noreferrer" class="text-indigo-400 hover:underline font-mono">Graph API Explorer</a> → Set <strong>User Token</strong>.</li>
                                        <li>Grant <code>pages_manage_posts</code>, <code>pages_show_list</code>, & <code>pages_read_engagement</code> permissions → Click <strong>Generate Access Token</strong>.</li>
                                        <li>Paste the <strong>App ID</strong>, <strong>App Secret</strong>, and <strong>User Token</strong> below, then click <strong>Generate & Set Token</strong>!</li>
                                    </ol>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-300 mb-1" for="edit_ex_appid">Facebook App ID</label>
                                        <input
                                            id="edit_ex_appid"
                                            class="input text-xs font-mono"
                                            bind:value={exchangeAppId}
                                            placeholder={fb_app_id || 'App ID'}
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-300 mb-1" for="edit_ex_appsec">Facebook App Secret</label>
                                        <input
                                            id="edit_ex_appsec"
                                            type="password"
                                            class="input text-xs font-mono"
                                            bind:value={exchangeAppSecret}
                                            placeholder={fb_app_secret ? '••••••••' : 'App Secret'}
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-medium text-gray-300 mb-1" for="edit_ex_token">Short-Lived User Access Token</label>
                                    <input
                                        id="edit_ex_token"
                                        type="password"
                                        class="input text-xs font-mono"
                                        bind:value={exchangeUserToken}
                                        placeholder="EAAB..."
                                    />
                                </div>

                                {#if exchangeError}
                                    <p class="text-xs text-red-400 bg-red-500/10 p-2.5 rounded-lg border border-red-500/20">{exchangeError}</p>
                                {/if}
                                {#if exchangeSuccess}
                                    <p class="text-xs text-emerald-400 bg-emerald-500/10 p-2.5 rounded-lg border border-emerald-500/20">{exchangeSuccess}</p>
                                {/if}

                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        onclick={() => handleAutoExchangeToken(editAccountId, (tok, pName, pId) => {
                                            editAccessToken = tok;

                                            if (pName) {
editName = pName;
}

                                            if (pId) {
editAccountId = pId;
}
                                        })}
                                        disabled={exchanging || !exchangeUserToken.trim()}
                                        class="btn-secondary text-xs px-4 py-2 flex items-center gap-2 cursor-pointer"
                                    >
                                        {#if exchanging}
                                            <div class="loading-spinner"></div>
                                            <span>Exchanging Token...</span>
                                        {:else}
                                            <span>Generate & Set Token</span>
                                        {/if}
                                    </button>
                                </div>
                            </div>
                        {/if}
                    </div>
                {/if}
            </div>

            <!-- Footer (Fixed) -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800/80 flex-shrink-0">
                <button type="button" onclick={() => showEditModal = false} class="btn-secondary text-xs px-4 py-2 cursor-pointer">
                    Cancel
                </button>
                <button
                    type="button"
                    onclick={handleEditSocialAccount}
                    disabled={editingAccount || !editAccountId.trim()}
                    class="btn-primary text-xs px-6 py-2.5 flex items-center gap-2 font-semibold cursor-pointer"
                >
                    {#if editingAccount}
                        <div class="loading-spinner"></div>
                        <span>Saving Changes...</span>
                    {:else}
                        <span>Save Changes</span>
                    {/if}
                </button>
            </div>
        </div>
    </div>
{/if}
