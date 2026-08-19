<script lang="ts">
    import { page, Link, router } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    let {
        children,
        title = 'Aiffiliate',
    }: { children?: Snippet; title?: string } = $props();

    let showUserDropdown = $state(false);

    const navItems = [
        { href: '/dashboard', label: 'Dashboard', icon: '◉' },
        { href: '/create', label: 'Create Post', icon: '＋' },
        { href: '/drafts', label: 'Drafts', icon: '📝' },
        { href: '/automated', label: 'Automated', icon: '⚡' },
        { href: '/history', label: 'History', icon: '☰' },
        { href: '/settings/app', label: 'Settings', icon: '⚙' },
    ];

    function isActive(href: string) {
        const path = page.url || '';
        if (href === '/dashboard') return path === '/dashboard' || path === '/';
        return path.startsWith(href);
    }

    function handleLogout() {
        router.post('/logout');
    }
</script>

<svelte:head>
    <title>{title} - Aiffiliate</title>
</svelte:head>

<div
    class="min-h-screen flex flex-col bg-gray-950 text-gray-100 selection:bg-indigo-500 selection:text-white"
>
    <!-- Ambient background -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div
            class="absolute inset-0 bg-gradient-to-br from-gray-950 via-gray-900 to-indigo-950 opacity-90"
        ></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Navigation Bar -->
    <nav
        class="sticky top-0 z-50 backdrop-blur-xl bg-gray-950/70 border-b border-gray-800/50"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between"
        >
            <a href="/dashboard" class="flex items-center gap-3 group">
                <div
                    class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-emerald-500 flex items-center justify-center font-bold text-sm text-white group-hover:scale-110 transition-transform duration-300 shadow-md shadow-indigo-500/20"
                >
                    A
                </div>
                <span class="font-bold text-xl tracking-tight">
                    <span
                        class="bg-gradient-to-r from-indigo-400 to-emerald-400 bg-clip-text text-transparent"
                        >Aiffiliate</span
                    >
                </span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                {#each navItems as item}
                    <a
                        href={item.href}
                        class="px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                            {isActive(item.href)
                            ? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 shadow-sm shadow-indigo-500/10'
                            : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/50'}"
                    >
                        <span class="opacity-70 text-xs">{item.icon}</span>
                        {item.label}
                    </a>
                {/each}
            </div>

            <!-- User Dropdown -->
            <div class="relative">
                <button
                    type="button"
                    onclick={(e) => {
                        e.stopPropagation();
                        showUserDropdown = !showUserDropdown;
                    }}
                    class="flex items-center gap-2 bg-gray-900 hover:bg-gray-800 border border-gray-800 px-3.5 py-2 rounded-xl text-xs text-white font-semibold cursor-pointer shadow-sm select-none"
                >
                    <span
                        class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                    ></span>
                    <span>{page.props.auth?.user?.name || 'Admin'}</span>
                    <span class="text-gray-400 text-[10px] ml-0.5">▼</span>
                </button>

                {#if showUserDropdown}
                    <div
                        class="absolute right-0 mt-2 w-48 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl p-1.5 z-50 flex flex-col gap-0.5 backdrop-blur-2xl"
                    >
                        <div class="px-3 py-2 border-b border-gray-800/80 mb-1">
                            <p class="text-xs font-bold text-white truncate">
                                {page.props.auth?.user?.name || 'User'}
                            </p>
                            <p
                                class="text-[10px] text-indigo-400 font-mono truncate"
                            >
                                {page.props.auth?.user?.email || ''}
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick={handleLogout}
                            class="w-full text-left px-3 py-2 text-xs text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-colors flex items-center gap-2 cursor-pointer font-medium"
                        >
                            <span>🚪</span> Sign Out
                        </button>
                    </div>
                {/if}
            </div>
        </div>
    </nav>

    <!-- Main Page Content -->
    <main class="flex-1">
        {@render children?.()}
    </main>

    <!-- Footer -->
    <footer
        class="border-t border-gray-800/40 py-6 text-center text-xs text-gray-500"
    >
        <p>Aiffiliate Content Generator & Auto-Publisher</p>
    </footer>
</div>
