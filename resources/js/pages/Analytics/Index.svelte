<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';

    let {
        analytics = {
            period: '30d',
            summary: {
                total_generations: 0,
                prompt_tokens_total: 0,
                completion_tokens_total: 0,
                total_tokens_used: 0,
                estimated_cost_usd: 0,
                avg_tokens_per_gen: 0,
                avg_cost_per_gen: 0,
                avg_execution_time_ms: 0,
                active_provider: 'openai',
                active_model: 'gpt-4o-mini',
            },
            timeline: [],
            by_provider: [],
            by_model: [],
            by_style: [],
            by_source: [],
            recent_activity: [],
            available_providers: [],
            available_styles: [],
            available_sources: [],
        },
        filters = {
            period: '30d',
            provider: 'all',
            style: 'all',
            source: 'all',
        },
        currentProvider = 'openai',
        currentModel = 'gpt-4o-mini',
    } = $props<{
        analytics?: any;
        filters?: {
            period?: string;
            provider?: string;
            style?: string;
            source?: string;
        };
        currentProvider?: string;
        currentModel?: string;
    }>();

    let activePeriod = $state(filters.period || '30d');
    let selectedProvider = $state(filters.provider || 'all');
    let selectedStyle = $state(filters.style || 'all');
    let selectedSource = $state(filters.source || 'all');
    let searchQuery = $state('');
    let isExporting = $state(false);
    let showClearModal = $state(false);
    let clearDays = $state('30');
    let isClearing = $state(false);
    let notificationMsg = $state<string | null>(null);

    const periods = [
        { key: 'today', label: 'Today' },
        { key: '7d', label: '7 Days' },
        { key: '30d', label: '30 Days' },
        { key: '90d', label: '90 Days' },
        { key: 'all', label: 'All Time' },
    ];

    function applyFilter(key: string, value: string) {
        if (key === 'period') activePeriod = value;
        if (key === 'provider') selectedProvider = value;
        if (key === 'style') selectedStyle = value;
        if (key === 'source') selectedSource = value;

        router.get(
            '/analytics',
            {
                period: key === 'period' ? value : activePeriod,
                provider: key === 'provider' ? value : selectedProvider,
                style: key === 'style' ? value : selectedStyle,
                source: key === 'source' ? value : selectedSource,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    }

    function formatTokenCount(num: number): string {
        if (!num) return '0';
        if (num >= 1000000) return (num / 1000000).toFixed(2) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toLocaleString();
    }

    function formatCost(cost: number): string {
        if (cost === undefined || cost === null) return '$0.0000';
        if (cost === 0) return '$0.00';
        if (cost < 0.0001) return '<$0.0001';
        return '$' + cost.toFixed(4);
    }

    function handleExport(format: 'csv' | 'json') {
        const url = `/analytics/export?format=${format}&period=${activePeriod}`;
        window.open(url, '_blank');
    }

    async function handleClearLogs() {
        isClearing = true;
        try {
            const resp = await fetch('/api/analytics/ai/clear', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({ older_than_days: parseInt(clearDays) || 0 }),
            });
            const data = await resp.json();
            if (data.success) {
                notificationMsg = data.message || 'AI logs cleared successfully.';
                showClearModal = false;
                setTimeout(() => {
                    notificationMsg = null;
                    router.reload();
                }, 1500);
            }
        } catch (e) {
            alert('Failed to clear logs.');
        } finally {
            isClearing = false;
        }
    }

    let filteredActivity = $derived(
        (analytics.recent_activity || []).filter((log: any) => {
            if (!searchQuery.trim()) return true;
            const q = searchQuery.toLowerCase();
            return (
                (log.product_title && log.product_title.toLowerCase().includes(q)) ||
                (log.provider && log.provider.toLowerCase().includes(q)) ||
                (log.model && log.model.toLowerCase().includes(q)) ||
                (log.style && log.style.toLowerCase().includes(q)) ||
                (log.source && log.source.toLowerCase().includes(q)) ||
                (log.id && log.id.toLowerCase().includes(q))
            );
        })
    );

    // Max tokens in timeline for scaling the bar chart
    let maxTimelineTokens = $derived(
        Math.max(
            1,
            ...(analytics.timeline || []).map((t: any) => t.tokens || 0)
        )
    );
</script>

<AppLayout title="AI Usage & Analytics">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <a
                        href="/dashboard"
                        class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors inline-block"
                    >
                        ← Dashboard
                    </a>
                    <span class="text-gray-600 text-xs">/</span>
                    <span class="text-xs text-gray-400">AI Analytics & Telemetry</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent">
                        AI Usage & Analytics
                    </span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Real-time token metrics, prompt completion cost calculation, and latency tracking.
                </p>
            </div>

            <!-- Export & Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    onclick={() => handleExport('csv')}
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-200 hover:text-white transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
                    title="Export as CSV spreadsheet"
                >
                    <span>📥</span> Export CSV
                </button>
                <button
                    onclick={() => handleExport('json')}
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-gray-900 hover:bg-gray-800 border border-gray-800 text-gray-200 hover:text-white transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
                    title="Export as JSON"
                >
                    <span>💾</span> Export JSON
                </button>
                <button
                    onclick={() => (showClearModal = true)}
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-red-950/30 hover:bg-red-900/40 border border-red-500/30 text-red-300 hover:text-red-200 transition-all flex items-center gap-1.5 shadow-sm cursor-pointer"
                >
                    <span>🗑️</span> Prune Logs
                </button>
                <a
                    href="/settings/app"
                    class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition-all flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 cursor-pointer"
                >
                    <span>⚙️</span> Model Settings
                </a>
            </div>
        </div>

        {#if notificationMsg}
            <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold flex items-center gap-2 animate-fadeIn">
                <span>✓</span> {notificationMsg}
            </div>
        {/if}

        <!-- Period Selector Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-8 bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-2.5 rounded-2xl shadow-xl">
            <!-- Period Tabs -->
            <div class="flex items-center gap-1">
                {#each periods as p}
                    <button
                        onclick={() => applyFilter('period', p.key)}
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all duration-200 cursor-pointer
                            {activePeriod === p.key
                            ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'
                            : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/60'}"
                    >
                        {p.label}
                    </button>
                {/each}
            </div>

            <!-- Provider Filter Dropdown -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">Provider:</span>
                <select
                    value={selectedProvider}
                    onchange={(e) => applyFilter('provider', (e.target as HTMLSelectElement).value)}
                    class="bg-gray-950/80 border border-gray-800 text-gray-300 text-xs rounded-xl px-3 py-1.5 outline-none focus:border-indigo-500 cursor-pointer"
                >
                    <option value="all">All Providers</option>
                    {#each analytics.available_providers || [] as prov}
                        <option value={prov}>{prov}</option>
                    {/each}
                </select>

                <!-- Style Filter -->
                <span class="text-xs text-gray-500 ml-2">Tone:</span>
                <select
                    value={selectedStyle}
                    onchange={(e) => applyFilter('style', (e.target as HTMLSelectElement).value)}
                    class="bg-gray-950/80 border border-gray-800 text-gray-300 text-xs rounded-xl px-3 py-1.5 outline-none focus:border-indigo-500 cursor-pointer"
                >
                    <option value="all">All Tones</option>
                    {#each analytics.available_styles || [] as st}
                        <option value={st}>{st}</option>
                    {/each}
                </select>
            </div>
        </div>

        <!-- KPI Summary Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <!-- Total Generations -->
            <div class="p-5 rounded-2xl border border-indigo-500/20 bg-indigo-950/20 backdrop-blur-xl relative overflow-hidden shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-300">Generations</span>
                    <span class="text-lg">🤖</span>
                </div>
                <div class="text-3xl font-extrabold text-white">
                    {analytics.summary?.total_generations || 0}
                </div>
                <div class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    <span>AI Caption Runs</span>
                </div>
            </div>

            <!-- Total Tokens -->
            <div class="p-5 rounded-2xl border border-purple-500/20 bg-purple-950/20 backdrop-blur-xl relative overflow-hidden shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-300">Total Tokens</span>
                    <span class="text-lg">⚡</span>
                </div>
                <div class="text-3xl font-extrabold text-purple-300 font-mono">
                    {formatTokenCount(analytics.summary?.total_tokens_used || 0)}
                </div>
                <div class="text-[11px] text-gray-400 mt-2 flex items-center justify-between font-mono">
                    <span>In: {formatTokenCount(analytics.summary?.prompt_tokens_total || 0)}</span>
                    <span>Out: {formatTokenCount(analytics.summary?.completion_tokens_total || 0)}</span>
                </div>
            </div>

            <!-- Cumulative Cost -->
            <div class="p-5 rounded-2xl border border-emerald-500/20 bg-emerald-950/20 backdrop-blur-xl relative overflow-hidden shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-300">Est. Total Cost</span>
                    <span class="text-lg">💰</span>
                </div>
                <div class="text-3xl font-extrabold text-emerald-400 font-mono">
                    {formatCost(analytics.summary?.estimated_cost_usd || 0)}
                </div>
                <div class="text-xs text-gray-400 mt-2 flex items-center gap-1.5">
                    <span class="text-emerald-400 font-mono">{formatCost(analytics.summary?.avg_cost_per_gen || 0)}</span>
                    <span>/ avg per run</span>
                </div>
            </div>

            <!-- Latency & Model -->
            <div class="p-5 rounded-2xl border border-amber-500/20 bg-amber-950/20 backdrop-blur-xl relative overflow-hidden shadow-xl">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-300">Active Config</span>
                    <span class="text-lg">⚡</span>
                </div>
                <div class="text-base font-extrabold text-amber-200 capitalize truncate">
                    {analytics.summary?.active_provider || currentProvider}
                </div>
                <div class="text-xs font-mono text-amber-400/90 truncate mt-1">
                    {analytics.summary?.active_model || currentModel}
                </div>
                <div class="text-[11px] text-gray-400 mt-1 font-mono">
                    Avg: {analytics.summary?.avg_execution_time_ms || 0} ms
                </div>
            </div>
        </div>

        <!-- Activity Timeline Trend Chart -->
        {#if analytics.timeline && analytics.timeline.length > 0}
            <div class="mb-8 p-6 rounded-2xl bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-gray-300 flex items-center gap-2">
                            <span>📈</span> Daily Token Activity & Cost Trend
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Token consumption volume by day for selected period</p>
                    </div>
                </div>

                <!-- Bar Visualization -->
                <div class="flex items-end gap-2 h-44 pt-4 pb-2 border-b border-gray-800/60 overflow-x-auto">
                    {#each analytics.timeline as day}
                        {@const heightPct = Math.max(8, Math.min(100, (day.tokens / maxTimelineTokens) * 100))}
                        <div class="flex-1 min-w-[36px] flex flex-col items-center justify-end h-full group relative">
                            <!-- Tooltip -->
                            <div class="absolute -top-14 hidden group-hover:flex flex-col items-center z-30 pointer-events-none">
                                <div class="bg-gray-950 border border-gray-700 text-[11px] text-white px-2.5 py-1.5 rounded-xl shadow-2xl font-mono whitespace-nowrap">
                                    <div class="font-bold text-indigo-300">{day.label}</div>
                                    <div>{day.tokens.toLocaleString()} tok ({day.count} runs)</div>
                                    <div class="text-emerald-400">{formatCost(day.cost)}</div>
                                </div>
                                <div class="w-2 h-2 bg-gray-950 border-r border-b border-gray-700 transform rotate-45 -mt-1"></div>
                            </div>

                            <!-- Bar Column -->
                            <div class="w-full flex flex-col items-center">
                                <div
                                    class="w-full rounded-t-lg bg-gradient-to-t from-indigo-600/80 via-purple-600/80 to-emerald-400 group-hover:brightness-125 transition-all duration-300"
                                    style="height: {day.tokens > 0 ? heightPct : 4}%"
                                ></div>
                            </div>
                            <span class="text-[10px] text-gray-500 font-mono mt-2 truncate max-w-[36px]">{day.label}</span>
                        </div>
                    {/each}
                </div>
            </div>
        {/if}

        <!-- Breakdown Grid: Providers, Models, Caption Styles, Sources -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Provider Breakdown -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <span>⚡</span> By AI Provider
                </h3>
                {#if !analytics.by_provider || analytics.by_provider.length === 0}
                    <p class="text-xs text-gray-500 py-6 text-center">No provider data</p>
                {:else}
                    <div class="space-y-3.5">
                        {#each analytics.by_provider as prov}
                            <div>
                                <div class="flex justify-between text-xs font-medium mb-1">
                                    <span class="text-gray-200 capitalize">{prov.provider}</span>
                                    <span class="font-mono text-gray-400">{prov.count} runs ({prov.percentage}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-800 overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-400"
                                        style="width: {prov.percentage}%"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500 font-mono mt-1">
                                    <span>{formatTokenCount(prov.total_tokens)} tok</span>
                                    <span class="text-emerald-400">{formatCost(prov.total_cost)}</span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Model Breakdown -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <span>🧠</span> By Model
                </h3>
                {#if !analytics.by_model || analytics.by_model.length === 0}
                    <p class="text-xs text-gray-500 py-6 text-center">No model data</p>
                {:else}
                    <div class="space-y-3.5">
                        {#each analytics.by_model as mod}
                            <div>
                                <div class="flex justify-between text-xs font-medium mb-1">
                                    <span class="text-gray-200 font-mono text-[11px] truncate max-w-[120px]">{mod.model}</span>
                                    <span class="font-mono text-gray-400">{mod.count} runs</span>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500 font-mono">
                                    <span>{formatTokenCount(mod.total_tokens)} tok</span>
                                    <span class="text-emerald-400">{formatCost(mod.total_cost)}</span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Tone / Style Breakdown -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <span>✨</span> By Caption Style
                </h3>
                {#if !analytics.by_style || analytics.by_style.length === 0}
                    <p class="text-xs text-gray-500 py-6 text-center">No style data</p>
                {:else}
                    <div class="space-y-3.5">
                        {#each analytics.by_style as st}
                            <div>
                                <div class="flex justify-between text-xs font-medium mb-1">
                                    <span class="text-gray-200 uppercase tracking-wider text-[10px]">{st.style.replace('_', ' ')}</span>
                                    <span class="font-mono text-gray-400">{st.count} runs</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-800 overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-purple-500 to-indigo-400"
                                        style="width: {st.percentage}%"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500 font-mono mt-1">
                                    <span>{formatTokenCount(st.total_tokens)} tok</span>
                                    <span class="text-emerald-400">{formatCost(st.total_cost)}</span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Source Breakdown -->
            <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 p-5 rounded-2xl shadow-xl">
                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4 flex items-center gap-2">
                    <span>🎯</span> By Entry Source
                </h3>
                {#if !analytics.by_source || analytics.by_source.length === 0}
                    <p class="text-xs text-gray-500 py-6 text-center">No source data</p>
                {:else}
                    <div class="space-y-3.5">
                        {#each analytics.by_source as src}
                            <div class="p-2.5 rounded-xl bg-gray-950/60 border border-gray-800/60">
                                <div class="flex justify-between text-xs font-semibold mb-1">
                                    <span class="text-gray-200 capitalize">{src.source.replace('_', ' ')}</span>
                                    <span class="text-indigo-400 font-mono">{src.count} calls</span>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-500 font-mono">
                                    <span>{formatTokenCount(src.total_tokens)} tok</span>
                                    <span class="text-emerald-400">{formatCost(src.total_cost)}</span>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </div>
        </div>

        <!-- Detailed AI Generation Logs Audit Table -->
        <div class="bg-gray-900/70 backdrop-blur-xl border border-gray-800/80 rounded-2xl shadow-2xl overflow-hidden">
            <div class="p-5 border-b border-gray-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-200 flex items-center gap-2">
                        <span>📋</span> Generation Activity & Audit Logs
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Showing latest {filteredActivity.length} AI execution records</p>
                </div>

                <!-- Search Input -->
                <div class="relative w-full sm:w-72">
                    <input
                        type="text"
                        bind:value={searchQuery}
                        placeholder="Search by post, model, tone..."
                        class="w-full h-9 rounded-xl border border-gray-800 bg-gray-950/90 pl-8 pr-3 text-xs text-gray-200 placeholder-gray-600 focus:border-indigo-500 outline-none"
                    />
                    <span class="absolute left-2.5 top-2.5 text-xs text-gray-500">🔍</span>
                </div>
            </div>

            {#if filteredActivity.length === 0}
                <div class="p-16 text-center">
                    <div class="text-4xl mb-3 opacity-30">📭</div>
                    <p class="text-gray-400 text-sm font-medium">No AI execution logs found</p>
                    <p class="text-gray-600 text-xs mt-1">Generate a post draft or run a workflow to start logging AI usage</p>
                </div>
            {:else}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-950/60 text-gray-400 font-semibold border-b border-gray-800/60 uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-4">Date / Time</th>
                                <th class="py-3 px-4">Target Post</th>
                                <th class="py-3 px-4">Provider & Model</th>
                                <th class="py-3 px-4">Tone / Preset</th>
                                <th class="py-3 px-4">Source</th>
                                <th class="py-3 px-4 text-right">Tokens (In / Out)</th>
                                <th class="py-3 px-4 text-right">Latency</th>
                                <th class="py-3 px-4 text-right">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/50">
                            {#each filteredActivity as log}
                                <tr class="hover:bg-gray-800/30 transition-colors">
                                    <!-- Timestamp -->
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-400 font-mono text-[11px]">
                                        {new Date(log.timestamp).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}
                                        <span class="text-gray-500">{new Date(log.timestamp).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</span>
                                    </td>

                                    <!-- Target Post -->
                                    <td class="py-3 px-4 max-w-[220px]">
                                        {#if log.post_id}
                                            <a
                                                href={`/drafts/${log.post_id}`}
                                                class="font-medium text-indigo-400 hover:text-indigo-300 truncate block"
                                                title={log.product_title}
                                            >
                                                {log.product_title || 'Post Draft'}
                                            </a>
                                        {:else}
                                            <span class="text-gray-300 truncate block font-medium">
                                                {log.product_title || 'Standalone AI Generation'}
                                            </span>
                                        {/if}
                                    </td>

                                    <!-- Provider & Model -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase
                                                {log.provider === 'openai' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
                                                log.provider === 'deepseek' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' :
                                                log.provider === 'gemini' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' :
                                                'bg-purple-500/10 text-purple-400 border border-purple-500/20'}">
                                                {log.provider}
                                            </span>
                                            <span class="text-gray-400 font-mono text-[11px] truncate max-w-[100px]">{log.model}</span>
                                        </span>
                                    </td>

                                    <!-- Tone / Style -->
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-300 uppercase tracking-wider text-[10px]">
                                        {log.style ? log.style.replace('_', ' ') : 'default'}
                                    </td>

                                    <!-- Source -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-gray-800/80 text-gray-400 border border-gray-700/50">
                                            {log.source || 'manual'}
                                        </span>
                                    </td>

                                    <!-- Tokens -->
                                    <td class="py-3 px-4 whitespace-nowrap text-right font-mono">
                                        <span class="font-bold text-gray-200">{log.total_tokens}</span>
                                        <span class="text-[10px] text-gray-500">({log.prompt_tokens}/{log.completion_tokens})</span>
                                    </td>

                                    <!-- Latency -->
                                    <td class="py-3 px-4 whitespace-nowrap text-right font-mono text-gray-400 text-[11px]">
                                        {log.execution_time_ms ? `${log.execution_time_ms} ms` : '—'}
                                    </td>

                                    <!-- Cost -->
                                    <td class="py-3 px-4 whitespace-nowrap text-right font-mono text-emerald-400 font-bold">
                                        {formatCost(log.estimated_cost)}
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            {/if}
        </div>
    </div>

    <!-- Prune / Clear Modal -->
    {#if showClearModal}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm animate-fadeIn">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-md w-full shadow-2xl">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 text-lg">
                        🗑️
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Prune AI Logs</h3>
                        <p class="text-xs text-gray-400">Clean up old telemetry & generation logs</p>
                    </div>
                </div>

                <div class="mb-6 space-y-3">
                    <label class="block text-xs font-semibold text-gray-300">
                        Prune Range:
                    </label>
                    <select
                        bind:value={clearDays}
                        class="w-full h-10 rounded-xl border border-gray-800 bg-gray-950 px-3 text-xs text-gray-100 focus:border-indigo-500 outline-none cursor-pointer"
                    >
                        <option value="30">Logs older than 30 days</option>
                        <option value="60">Logs older than 60 days</option>
                        <option value="90">Logs older than 90 days</option>
                        <option value="0">Clear ALL logs (Reset to zero)</option>
                    </select>
                    <p class="text-[11px] text-gray-500">
                        This action permanently deletes AI generation log records. Aggregate stats will recalculate automatically.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        onclick={() => (showClearModal = false)}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        disabled={isClearing}
                        onclick={handleClearLogs}
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-500 text-white transition-colors cursor-pointer disabled:opacity-50"
                    >
                        {isClearing ? 'Pruning...' : 'Confirm Prune'}
                    </button>
                </div>
            </div>
        </div>
    {/if}
</AppLayout>
