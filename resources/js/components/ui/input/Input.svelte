<script lang="ts">
    import type { HTMLInputAttributes } from 'svelte/elements';
    import { cn } from '@/lib/utils';

    let {
        class: className = '',
        type = 'text',
        value = $bindable(),
        ...rest
    }: HTMLInputAttributes = $props();
</script>

<input
    {type}
    bind:value
    class={cn(
        'flex h-11 w-full rounded-xl border border-gray-800 bg-gray-950/80 px-4 py-2.5 text-xs sm:text-sm text-gray-100 placeholder:text-gray-600 shadow-sm transition-all focus-visible:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-50 font-sans',
        className,
    )}
    {...rest}
/>
