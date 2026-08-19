<script lang="ts">
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { router } from '@inertiajs/svelte';

    let { workflows = [] } = $props<{ workflows: Array<any> }>();

    let activeTab = $state<'scheduled' | 'event'>('scheduled');
    let showModal = $state(false);

    let name = $state('');
    let category = $state('Connection & Community');
    let frequency = $state('daily');
    let target_page = $state('Tech Sulit Deals');

    function handleCreate(e: SubmitEvent) {
        e.preventDefault();
        router.post(
            '/automated',
            { name, category, frequency, target_page },
            {
                onSuccess: () => {
                    showModal = false;
                    name = '';
                },
            },
        );
    }

    function handleDelete(id: string) {
        if (confirm('Delete this automated workflow rule?')) {
            router.delete(`/automated/${id}`);
        }
    }
</script>

<AppLayout title="Automated Workflows">
    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8"
        >
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                    >
                        Automated Posting & Workflow Engine
                    </span>
                </h1>
                <p class="text-gray-400 text-sm mt-1">
                    Configure scheduled time-aware rules and event-based deal
                    triggers
                </p>
            </div>
            <button
                onclick={() => (showModal = true)}
                class="px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-emerald-500 hover:from-indigo-600 hover:to-emerald-600 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/20 transition-all cursor-pointer flex items-center gap-2"
            >
                <span class="text-base">＋</span> Create Workflow Rule
            </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-gray-800/80 mb-6 gap-2">
            <button
                onclick={() => (activeTab = 'scheduled')}
                class="px-4 py-2.5 font-semibold text-sm transition-all border-b-2 cursor-pointer flex items-center gap-2
                    {activeTab === 'scheduled'
                    ? 'border-indigo-500 text-indigo-400 bg-indigo-500/10 rounded-t-xl'
                    : 'border-transparent text-gray-400 hover:text-gray-200'}"
            >
                <span>📅 Scheduled Rules</span>
            </button>
            <button
                onclick={() => (activeTab = 'event')}
                class="px-4 py-2.5 font-semibold text-sm transition-all border-b-2 cursor-pointer flex items-center gap-2
                    {activeTab === 'event'
                    ? 'border-indigo-500 text-indigo-400 bg-indigo-500/10 rounded-t-xl'
                    : 'border-transparent text-gray-400 hover:text-gray-200'}"
            >
                <span>⚡ Event Triggers (Webhooks / Price Drops)</span>
            </button>
        </div>

        <!-- Rules Display Grid -->
        {#if activeTab === 'scheduled'}
            {@const scheduledList = workflows.filter(
                (w) => w.frequency !== 'event_based',
            )}
            {#if scheduledList.length === 0}
                <div
                    class="p-12 text-center border border-gray-800/80 rounded-2xl bg-gray-900/60"
                >
                    <p class="text-gray-400 font-medium">
                        No scheduled workflow rules configured.
                    </p>
                </div>
            {:else}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    {#each scheduledList as wf (wf.id)}
                        <div
                            class="bg-gray-900/70 backdrop-blur-xl rounded-2xl border border-gray-800/80 p-5 flex flex-col justify-between hover:border-indigo-500/40 transition-all shadow-xl"
                        >
                            <div>
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <span
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 capitalize"
                                    >
                                        {wf.status}
                                    </span>
                                    <span
                                        class="text-[11px] text-gray-500 font-mono"
                                        >FREQ: {wf.frequency}</span
                                    >
                                </div>
                                <h3
                                    class="text-base font-bold text-gray-100 mb-2"
                                >
                                    {wf.name}
                                </h3>
                                <p class="text-xs text-gray-400 mb-1">
                                    Category: <span
                                        class="font-semibold text-indigo-300"
                                        >{wf.category}</span
                                    >
                                </p>
                                <p class="text-xs text-gray-400 mb-1">
                                    Target Page: <span
                                        class="font-semibold text-gray-200"
                                        >{wf.target_page}</span
                                    >
                                </p>
                                {#if wf.general_context}
                                    <p
                                        class="text-xs text-gray-500 mt-2 bg-gray-950/60 p-2.5 rounded-xl border border-gray-800/40 italic"
                                    >
                                        "{wf.general_context}"
                                    </p>
                                {/if}
                            </div>
                            <div
                                class="pt-4 border-t border-gray-800/60 flex items-center justify-between mt-4"
                            >
                                <span
                                    class="text-[11px] text-gray-500 font-mono"
                                    >ID: {wf.id}</span
                                >
                                <button
                                    onclick={() => handleDelete(wf.id)}
                                    class="text-xs text-red-400 hover:text-red-300 font-medium cursor-pointer"
                                >
                                    Delete Rule
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        {:else}
            {@const eventList = workflows.filter(
                (w) => w.frequency === 'event_based',
            )}
            {#if eventList.length === 0}
                <div
                    class="p-12 text-center border border-gray-800/80 rounded-2xl bg-gray-900/60"
                >
                    <p class="text-gray-400 font-medium">
                        No event-based triggers configured.
                    </p>
                </div>
            {:else}
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    {#each eventList as wf (wf.id)}
                        <div
                            class="bg-gray-900/70 backdrop-blur-xl rounded-2xl border border-purple-500/30 p-5 flex flex-col justify-between hover:border-purple-500/60 transition-all shadow-xl"
                        >
                            <div>
                                <div
                                    class="flex items-center justify-between mb-3"
                                >
                                    <span
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-300"
                                    >
                                        ⚡ Event Trigger
                                    </span>
                                    <span
                                        class="text-[11px] text-emerald-400 font-mono font-semibold"
                                        >Active</span
                                    >
                                </div>
                                <h3
                                    class="text-base font-bold text-gray-100 mb-2"
                                >
                                    {wf.name}
                                </h3>
                                <p class="text-xs text-gray-400 mb-1">
                                    Target Page: <span
                                        class="font-semibold text-gray-200"
                                        >{wf.target_page}</span
                                    >
                                </p>
                                {#if wf.general_context}
                                    <p
                                        class="text-xs text-gray-500 mt-2 bg-gray-950/60 p-2.5 rounded-xl border border-gray-800/40 italic"
                                    >
                                        "{wf.general_context}"
                                    </p>
                                {/if}
                            </div>
                            <div
                                class="pt-4 border-t border-gray-800/60 flex items-center justify-between mt-4"
                            >
                                <span
                                    class="text-[11px] text-gray-500 font-mono"
                                    >ID: {wf.id}</span
                                >
                                <button
                                    onclick={() => handleDelete(wf.id)}
                                    class="text-xs text-red-400 hover:text-red-300 font-medium cursor-pointer"
                                >
                                    Delete Rule
                                </button>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        {/if}

        <!-- Create Modal -->
        {#if showModal}
            <div
                class="fixed inset-0 bg-black/70 backdrop-blur-md flex items-center justify-center p-4 z-50"
            >
                <div
                    class="bg-gray-900 border border-gray-800 rounded-2xl p-6 max-w-md w-full shadow-2xl"
                >
                    <h2 class="text-lg font-bold text-white mb-4">
                        Create Workflow Rule
                    </h2>
                    <form onsubmit={handleCreate} class="space-y-4">
                        <div>
                            <label
                                for="name"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Rule Name</label
                            >
                            <input
                                id="name"
                                type="text"
                                bind:value={name}
                                required
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label
                                for="category"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Category</label
                            >
                            <select
                                id="category"
                                bind:value={category}
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            >
                                <option value="Connection & Community"
                                    >Connection & Community</option
                                >
                                <option value="Deals & Promotions"
                                    >Deals & Promotions</option
                                >
                                <option value="Product Highlights"
                                    >Product Highlights</option
                                >
                            </select>
                        </div>
                        <div>
                            <label
                                for="frequency"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Frequency</label
                            >
                            <select
                                id="frequency"
                                bind:value={frequency}
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            >
                                <option value="daily">Daily Schedule</option>
                                <option value="weekly">Weekly Schedule</option>
                                <option value="event_based"
                                    >⚡ Event-Based (Webhook / Price Alert)</option
                                >
                                <option value="custom">Custom Schedule</option>
                            </select>
                        </div>
                        <div>
                            <label
                                for="target_page"
                                class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1"
                                >Target Page</label
                            >
                            <input
                                id="target_page"
                                type="text"
                                bind:value={target_page}
                                required
                                class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none"
                            />
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
                                >Create</button
                            >
                        </div>
                    </form>
                </div>
            </div>
        {/if}
    </div>
</AppLayout>
