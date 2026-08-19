<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { logout } from '@/routes';
    import { send } from '@/routes/verification';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="Email verification" />

<AuthLayout
    title="Email verification"
    description="Please verify your email address by clicking on the link we just sent to your inbox."
>
    {#if status === 'verification-link-sent'}
        <div
            class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 flex items-center gap-2 font-medium"
        >
            <span>✓</span>
            <span
                >A new verification link has been sent to your registered email
                address.</span
            >
        </div>
    {/if}

    <Form {...send.form()} class="flex flex-col gap-4 text-center">
        {#snippet children({ processing })}
            <input type="hidden" name="_token" value={page.props.csrf_token} />
            <button
                type="submit"
                disabled={processing}
                class="w-full py-3.5 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
            >
                {#if processing}
                    <Spinner class="w-4 h-4 text-white" />
                    <span>Sending email...</span>
                {:else}
                    <span>Resend Verification Email ✉️</span>
                {/if}
            </button>

            <div class="pt-2 border-t border-gray-800/60">
                <TextLink
                    href={logout()}
                    as="button"
                    class="text-xs text-gray-400 hover:text-gray-200"
                >
                    Log out
                </TextLink>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
