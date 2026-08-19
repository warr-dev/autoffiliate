<script lang="ts">
    import { cn } from '@/lib/utils';
    import Check from 'lucide-svelte/icons/check';

    let {
        checked = $bindable(false),
        disabled = false,
        class: className = '',
        id,
        name,
        value,
        ...rest
    }: {
        checked?: boolean;
        disabled?: boolean;
        class?: string;
        id?: string;
        name?: string;
        value?: string;
        [key: string]: unknown;
    } = $props();
</script>

<button
    type="button"
    role="checkbox"
    aria-checked={checked}
    data-state={checked ? 'checked' : 'unchecked'}
    data-slot="checkbox"
    {disabled}
    {id}
    class={cn(
        'peer size-4 shrink-0 rounded-md border border-gray-700 bg-gray-950/80 transition-all outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/50 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-indigo-600 data-[state=checked]:border-indigo-500 data-[state=checked]:text-white cursor-pointer flex items-center justify-center',
        className,
    )}
    onclick={() => { if (!disabled) checked = !checked; }}
    {...rest}
>
    {#if checked}
        <div data-slot="checkbox-indicator" class="grid place-content-center text-current transition-none">
            <Check class="size-3" />
        </div>
    {/if}
</button>
{#if name}
    <input type="hidden" {name} {value} />
{/if}
