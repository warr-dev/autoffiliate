<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { login } from '@/routes';
    import { email } from '@/routes/password';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="Forgot password" />

<AuthLayout
    title="Forgot password"
    description="Enter your email to receive a password reset link."
>
    {#if status}
        <div
            class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 flex items-center gap-2 font-medium"
        >
            <span>✓</span>
            <span>{status}</span>
        </div>
    {/if}

    <Form {...email.form()} class="flex flex-col gap-4">
        {#snippet children({ errors, processing })}
            <input type="hidden" name="_token" value={page.props.csrf_token} />
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label
                        for="email"
                        class="text-xs font-semibold text-gray-300"
                    >
                        Email Address
                    </Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        required
                        placeholder="you@example.com"
                    />
                    <InputError message={errors.email} />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    data-test="email-password-reset-link-button"
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Sending link...</span>
                    {:else}
                        <span>Email Password Reset Link ✉️</span>
                    {/if}
                </button>
            </div>

            <div
                class="text-center text-xs text-gray-400 pt-2 border-t border-gray-800/60"
            >
                Remember your password?
                <TextLink href={login()} class="ml-1">Log in</TextLink>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
