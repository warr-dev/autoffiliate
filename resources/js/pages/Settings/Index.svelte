<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router } from '@inertiajs/svelte';

    let { settings = {}, socialAccounts = [] } = $props<{ settings: Record<string, string>; socialAccounts: Array<any> }>();

    let disclosure = $state('');
    let default_hashtags = $state('');
    let fb_page_id = $state('');
    let fb_page_name = $state('');
    let fb_page_token = $state('');
    let ai_provider = $state('openai');
    let ai_api_key = $state('');
    let ai_model = $state('gpt-4o-mini');

    $effect(() => {
        disclosure = settings.disclosure || 'Affiliate link. Price and availability may change anytime.';
        default_hashtags = settings.default_hashtags || '#TechSulitDeals #ShopeePH';
        fb_page_id = settings.fb_page_id || '';
        fb_page_name = settings.fb_page_name || 'Tech Sulit Deals';
        fb_page_token = settings.fb_page_token || '';
        ai_provider = settings.ai_provider || 'openai';
        ai_api_key = settings.ai_api_key || '';
        ai_model = settings.ai_model || 'gpt-4o-mini';
    });

    let saved = $state(false);

    function handleSave(e: SubmitEvent) {
        e.preventDefault();
        router.post('/settings/app', {
            disclosure,
            default_hashtags,
            fb_page_id,
            fb_page_name,
            fb_page_token,
            ai_provider,
            ai_api_key,
            ai_model
        }, {
            onSuccess: () => {
                saved = true;
                setTimeout(() => saved = false, 3000);
            }
        });
    }
</script>

<AppLayout title="Settings">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <a href="/dashboard" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors mb-2 inline-block">← Dashboard</a>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
                        Application & AI Provider Settings
                    </span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">Configure Facebook Page publishing, LLM keys, and affiliate disclosures</p>
            </div>
        </div>

        {#if saved}
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
                <span>✓</span> Settings saved successfully!
            </div>
        {/if}

        <form onsubmit={handleSave} class="space-y-6">
            <!-- Facebook Integration Section -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-800/60 pb-3 mb-4">
                    <span class="text-xl">📘</span>
                    <h2 class="text-base font-bold text-gray-100">Facebook Page Integration</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fb_page_name" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Page Name</label>
                        <input id="fb_page_name" type="text" bind:value={fb_page_name} class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                    </div>
                    <div>
                        <label for="fb_page_id" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Facebook Page ID</label>
                        <input id="fb_page_id" type="text" bind:value={fb_page_id} class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label for="fb_page_token" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Page Access Token</label>
                    <input id="fb_page_token" type="password" bind:value={fb_page_token} placeholder="EAAs8X..." class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white font-mono focus:border-indigo-500 focus:outline-none" />
                </div>
            </div>

            <!-- AI Model Provider Section -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-800/60 pb-3 mb-4">
                    <span class="text-xl">🤖</span>
                    <h2 class="text-base font-bold text-gray-100">AI Copywriting & LLM Provider</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ai_provider" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">AI Provider</label>
                        <select id="ai_provider" bind:value={ai_provider} class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none">
                            <option value="deepseek">DeepSeek AI (DeepSeek-V3 / R1)</option>
                            <option value="openai">OpenAI (GPT-4o / GPT-4o-mini)</option>
                            <option value="anthropic">Anthropic (Claude 3.5 Sonnet)</option>
                            <option value="gemini">Google Gemini AI</option>
                        </select>

                    </div>
                    <div>
                        <label for="ai_model" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Model Name</label>
                        <input id="ai_model" type="text" bind:value={ai_model} class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                    </div>
                </div>

                <div>
                    <label for="ai_api_key" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">API Key</label>
                    <input id="ai_api_key" type="password" bind:value={ai_api_key} placeholder="sk-..." class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white font-mono focus:border-indigo-500 focus:outline-none" />
                </div>
            </div>

            <!-- Copywriting Defaults Section -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-800/60 pb-3 mb-4">
                    <span class="text-xl">🏷️</span>
                    <h2 class="text-base font-bold text-gray-100">Affiliate Disclosures & Hashtags</h2>
                </div>

                <div>
                    <label for="disclosure" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Affiliate Link Disclosure</label>
                    <textarea id="disclosure" bind:value={disclosure} rows="2" class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label for="default_hashtags" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Default Hashtag Cluster</label>
                    <input id="default_hashtags" type="text" bind:value={default_hashtags} class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none" />
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white font-semibold text-sm rounded-xl shadow-lg shadow-indigo-500/20 transition-all cursor-pointer">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</AppLayout>
