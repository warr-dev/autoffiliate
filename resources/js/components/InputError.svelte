<script lang="ts">
    import { cn } from '@/lib/utils';

    let {
        message = '',
        class: className = '',
    }: {
        message?: string;
        class?: string;
    } = $props();
</script>

{#if message}
    <div class={cn('mt-1', className)}>
        <p class="text-xs text-rose-400 font-medium flex items-center gap-1.5">
            <span>⚠️</span>
            <span>{message}</span>
        </p>
    </div>
{/if}
