<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="min-h-screen bg-gray-950 text-white flex items-center justify-center p-4 sm:p-6 relative overflow-hidden selection:bg-indigo-500 selection:text-white"
>
    <!-- Ambient Background Glows -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div
            class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-indigo-950 opacity-95"
        ></div>
        <div
            class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"
        ></div>
        <div
            class="absolute -bottom-40 -right-40 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"
        ></div>
        <div
            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"
        ></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Auth Card -->
    <div
        class="w-full max-w-md bg-gray-900/70 border border-gray-800/80 rounded-3xl p-6 sm:p-8 backdrop-blur-2xl shadow-2xl relative z-10 flex flex-col gap-6"
    >
        <!-- Top subtle accent line -->
        <div
            class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"
        ></div>

        <!-- Header / Logo -->
        <div class="flex flex-col items-center text-center gap-2">
            <Link
                href={home.url ? home.url() : '/'}
                class="group flex flex-col items-center gap-3 select-none"
            >
                <div
                    class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/30 text-2xl font-bold group-hover:scale-105 transition-transform duration-300"
                >
                    ⚡
                </div>
                <div class="flex flex-col items-center">
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-white mt-1"
                    >
                        <span
                            class="bg-gradient-to-r from-indigo-400 via-purple-400 to-emerald-400 bg-clip-text text-transparent"
                        >
                            Aiffiliate
                        </span>
                        <span class="text-white ml-1.5 text-xl font-bold"
                            >CreatorStudio</span
                        >
                    </h1>
                </div>
            </Link>

            {#if description}
                <p
                    class="text-xs text-gray-400 mt-1 max-w-xs text-center leading-relaxed"
                >
                    {description}
                </p>
            {/if}
        </div>

        <!-- Dynamic Form Content -->
        {@render children?.()}
    </div>
</div>
