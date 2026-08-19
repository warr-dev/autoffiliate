<script lang="ts">
    import type { UrlMethodPair } from '@inertiajs/core';
    import { router } from '@inertiajs/svelte';
    import { usePasskeyVerify } from '@laravel/passkeys/svelte';
    import KeyRound from 'lucide-svelte/icons/key-round';
    import { untrack } from 'svelte';
    import InputError from '@/components/InputError.svelte';
    import { Spinner } from '@/components/ui/spinner';

    type Props = {
        routes?: {
            options: UrlMethodPair;
            submit: UrlMethodPair;
        };
        label?: string;
        loadingLabel?: string;
        separator?: string;
    };

    let props: Props = $props();
    const initialRoutes = untrack(() => props.routes);

    const passkeyVerify = usePasskeyVerify({
        ...(initialRoutes
            ? {
                  routes: {
                      options: initialRoutes.options.url,
                      submit: initialRoutes.submit.url,
                  },
              }
            : {}),
        onSuccess: (response) => {
            const redirect = response.redirect;
            router.visit(redirect ?? '/dashboard');
        },
    });
</script>

{#if passkeyVerify.isSupported}
    <div class="flex flex-col gap-2">
        <button
            type="button"
            class="w-full py-3 px-4 bg-gray-900/90 hover:bg-gray-800 border border-gray-800 hover:border-gray-700 text-gray-200 hover:text-white rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200 shadow-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
            onclick={passkeyVerify.verify}
            disabled={passkeyVerify.isLoading}
        >
            {#if passkeyVerify.isLoading}
                <Spinner class="w-4 h-4 text-indigo-400" />
            {:else}
                <KeyRound class="h-4 w-4 text-indigo-400" />
            {/if}
            <span>
                {passkeyVerify.isLoading
                    ? (props.loadingLabel ?? 'Authenticating...')
                    : (props.label ?? 'Sign in with a passkey')}
            </span>
        </button>

        {#if passkeyVerify.error}
            <div class="text-center">
                <InputError message={passkeyVerify.error} />
            </div>
        {/if}
    </div>

    <div class="relative my-2">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-800"></div>
        </div>
        <div
            class="relative flex justify-center text-[11px] uppercase tracking-wider"
        >
            <span
                class="bg-gray-900/90 px-3 text-gray-500 rounded-full font-medium select-none"
            >
                {props.separator ?? 'Or continue with email'}
            </span>
        </div>
    </div>
{/if}
