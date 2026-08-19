<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import {
        index as confirmOptions,
        store as confirmStore,
    } from '@/actions/Laravel/Passkeys/Http/Controllers/PasskeyConfirmationController';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { store } from '@/routes/password/confirm';
</script>

<AppHead title="Confirm password" />

<AuthLayout
    title="Confirm password"
    description="This is a secure area. Please confirm your password to proceed."
>
    <PasskeyVerify
        routes={{
            options: confirmOptions(),
            submit: confirmStore(),
        }}
        label="Confirm with passkey"
        loadingLabel="Confirming..."
        separator="Or confirm with password"
    />

    <Form {...store.form()} resetOnSuccess class="flex flex-col gap-4">
        {#snippet children({ errors, processing })}
            <input type="hidden" name="_token" value={page.props.csrf_token} />
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label
                        for="password"
                        class="text-xs font-semibold text-gray-300"
                    >
                        Password
                    </Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError message={errors.password} />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    data-test="confirm-password-button"
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Confirming...</span>
                    {:else}
                        <span>Confirm Password 🔒</span>
                    {/if}
                </button>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
