<script lang="ts">
    import { page, Link, router } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';

    let {
        children,
        title = 'Aiffiliate',
    }: { children?: Snippet; title?: string } = $props();

    let showUserDropdown = $state(false);
    let showMobileMenu = $state(false);

    const navItems = [
        { href: '/dashboard', label: 'Dashboard', icon: '◉' },
        { href: '/create', label: 'Create Post', icon: '＋' },
        { href: '/drafts', label: 'Drafts', icon: '📝' },
        { href: '/automated', label: 'Automated', icon: '⚡' },
        { href: '/history', label: 'History', icon: '☰' },
        { href: '/analytics', label: 'Analytics', icon: '📊' },
        { href: '/settings/app', label: 'Settings', icon: '⚙' },
    ];

    const bottomNavItems = [
        { href: '/dashboard', label: 'Dashboard', icon: '◉' },
        { href: '/create', label: 'Create', icon: '＋' },
        { href: '/drafts', label: 'Drafts', icon: '📝' },
        { href: '/automated', label: 'Auto', icon: '⚡' },
        { href: '/analytics', label: 'Analytics', icon: '📊' },
        { href: '/settings/app', label: 'Settings', icon: '⚙' },
    ];

    function isActive(href: string) {
        const path = page.url || '';

        if (href === '/dashboard') {
            return path === '/dashboard' || path === '/';
        }

        return path.startsWith(href);
    }

    function handleLogout() {
        router.post('/logout');
    }
</script>

<svelte:head>
    <title>{title} - Aiffiliate</title>
</svelte:head>

<svelte:window onclick={() => { showUserDropdown = false; }} />

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
        class="sticky top-0 z-50 backdrop-blur-xl bg-gray-950/80 border-b border-gray-800/60"
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

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-1">
                {#each navItems as item}
                    <a
                        href={item.href}
                        class="px-3.5 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-1.5
                            {isActive(item.href)
                            ? 'bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 shadow-sm shadow-indigo-500/10'
                            : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800/50 border border-transparent'}"
                    >
                        <span class="opacity-70 text-xs">{item.icon}</span>
                        {item.label}
                    </a>
                {/each}
            </div>

            <!-- Right Controls: User Dropdown & Mobile Hamburger -->
            <div class="flex items-center gap-2">
                <!-- User Dropdown -->
                <div class="relative">
                    <button
                        type="button"
                        onclick={(e) => {
                            e.stopPropagation();
                            showUserDropdown = !showUserDropdown;
                        }}
                        class="flex items-center gap-2 bg-gray-900 hover:bg-gray-800 border border-gray-800 px-3.5 py-2 rounded-xl text-xs text-white font-semibold cursor-pointer shadow-sm select-none transition-colors"
                    >
                        <span
                            class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
                        ></span>
                        <span class="max-w-[100px] truncate">{page.props.auth?.user?.name || 'Admin'}</span>
                        <span class="text-gray-400 text-[10px] ml-0.5">▼</span>
                    </button>

                    {#if showUserDropdown}
                        <div
                            class="absolute right-0 mt-2 w-52 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl p-1.5 z-50 flex flex-col gap-0.5 backdrop-blur-2xl animate-slideDown"
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

                            <a
                                href="/settings/app"
                                class="w-full text-left px-3 py-2 text-xs text-gray-300 hover:text-white hover:bg-gray-800/60 rounded-xl transition-colors flex items-center gap-2"
                            >
                                <span>⚙</span> Settings
                            </a>

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

                <!-- Mobile Hamburger Toggle Button -->
                <button
                    type="button"
                    onclick={() => (showMobileMenu = !showMobileMenu)}
                    aria-label="Toggle mobile menu"
                    class="md:hidden w-10 h-10 rounded-xl bg-gray-900 hover:bg-gray-800 border border-gray-800 flex items-center justify-center text-gray-300 hover:text-white transition-all cursor-pointer"
                >
                    {#if showMobileMenu}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    {:else}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    {/if}
                </button>
            </div>
        </div>

        <!-- Mobile Header Dropdown Menu Drawer -->
        {#if showMobileMenu}
            <div
                class="md:hidden border-t border-gray-800/80 bg-gray-950/95 backdrop-blur-2xl px-4 py-4 space-y-1.5 animate-slideDown shadow-2xl"
            >
                {#each navItems as item}
                    <a
                        href={item.href}
                        onclick={() => (showMobileMenu = false)}
                        class="px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 flex items-center justify-between
                            {isActive(item.href)
                            ? 'bg-indigo-500/20 text-indigo-200 border border-indigo-500/40 font-semibold shadow-sm'
                            : 'text-gray-300 hover:text-white hover:bg-gray-800/60 border border-transparent'}"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-base opacity-80">{item.icon}</span>
                            <span>{item.label}</span>
                        </div>
                        {#if isActive(item.href)}
                            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                        {/if}
                    </a>
                {/each}

                <div class="pt-3 border-t border-gray-800/80 flex items-center justify-between">
                    <div class="flex flex-col">
                        <span class="text-xs font-bold text-white">{page.props.auth?.user?.name || 'Admin'}</span>
                        <span class="text-[10px] text-gray-400 font-mono">{page.props.auth?.user?.email || ''}</span>
                    </div>
                    <button
                        type="button"
                        onclick={handleLogout}
                        class="px-3.5 py-1.5 text-xs text-red-400 hover:bg-red-500/10 border border-red-500/30 rounded-xl transition-colors font-medium flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>🚪</span> Sign Out
                    </button>
                </div>
            </div>
        {/if}
    </nav>

    <!-- Main Page Content -->
    <main class="flex-1 pb-24 md:pb-8">
        {@render children?.()}
    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav
        class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-gray-950/90 backdrop-blur-xl border-t border-gray-800/80 px-2 py-2 flex items-center justify-around shadow-[0_-4px_20px_rgba(0,0,0,0.5)]"
    >
        {#each bottomNavItems as item}
            {@const active = isActive(item.href)}
            <a
                href={item.href}
                class="flex flex-col items-center justify-center gap-1 px-3 py-1.5 rounded-xl text-[11px] font-medium transition-all duration-200 min-w-[56px]
                    {active
                    ? 'text-indigo-300 font-bold'
                    : 'text-gray-400 hover:text-gray-200'}"
            >
                <span
                    class="text-base transition-transform duration-200 {active ? 'scale-115 text-indigo-400' : 'opacity-70'}"
                >
                    {item.icon}
                </span>
                <span>{item.label}</span>
                {#if active}
                    <span class="w-1 h-1 rounded-full bg-indigo-400"></span>
                {/if}
            </a>
        {/each}
    </nav>

    <!-- Footer -->
    <footer
        class="border-t border-gray-800/40 py-6 text-center text-xs text-gray-500 hidden md:block"
    >
        <p>Aiffiliate Content Generator & Auto-Publisher</p>
    </footer>
</div>
