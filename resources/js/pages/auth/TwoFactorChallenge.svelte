<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Input } from '@/components/ui/input';
    import {
        InputOTP,
        InputOTPGroup,
        InputOTPSlot,
    } from '@/components/ui/input-otp';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { store } from '@/routes/two-factor/login';
    import type { TwoFactorConfigContent } from '@/types';

    let showRecoveryInput = $state(false);
    let code = $state('');

    const authConfigContent: TwoFactorConfigContent = $derived.by(() => {
        if (showRecoveryInput) {
            return {
                title: 'Recovery code',
                description:
                    'Please confirm access to your account by entering one of your emergency recovery codes.',
                buttonText: 'login using an authentication code',
            };
        }

        return {
            title: 'Authentication code',
            description:
                'Enter the 6-digit authentication code provided by your authenticator app.',
            buttonText: 'login using a recovery code',
        };
    });

    function toggleRecoveryMode(clearErrors: () => void) {
        showRecoveryInput = !showRecoveryInput;
        clearErrors();
        code = '';
    }
</script>

<AppHead title="Two-factor authentication" />

<AuthLayout
    title={authConfigContent.title}
    description={authConfigContent.description}
>
    {#if !showRecoveryInput}
        <Form
            {...store.form()}
            class="flex flex-col gap-4"
            resetOnError
            onError={() => (code = '')}
        >
            {#snippet children({ errors, processing, clearErrors })}
                <input
                    type="hidden"
                    name="_token"
                    value={page.props.csrf_token}
                />
                <input type="hidden" name="code" value={code} />
                <div
                    class="flex flex-col items-center justify-center gap-3 text-center my-2"
                >
                    <div class="flex w-full items-center justify-center">
                        <InputOTP
                            id="otp"
                            bind:value={code}
                            maxlength={6}
                            disabled={processing}
                            autofocus
                        >
                            <InputOTPGroup>
                                {#each { length: 6 } as _, i (i)}
                                    <InputOTPSlot index={i} />
                                {/each}
                            </InputOTPGroup>
                        </InputOTP>
                    </div>
                    <InputError message={errors.code} />
                </div>

                <button
                    type="submit"
                    disabled={processing || code.length < 6}
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Verifying...</span>
                    {:else}
                        <span>Verify & Continue 🚀</span>
                    {/if}
                </button>

                <div
                    class="text-center text-xs text-gray-400 pt-2 border-t border-gray-800/60"
                >
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer underline hover:text-white transition-colors"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {:else}
        <Form {...store.form()} class="flex flex-col gap-4" resetOnError>
            {#snippet children({ errors, processing, clearErrors })}
                <input
                    type="hidden"
                    name="_token"
                    value={page.props.csrf_token}
                />
                <div class="flex flex-col gap-1.5">
                    <label
                        for="recovery_code"
                        class="text-xs font-semibold text-gray-300"
                        >Recovery Code</label
                    >
                    <Input
                        id="recovery_code"
                        name="recovery_code"
                        type="text"
                        placeholder="Enter recovery code"
                        required
                    />
                    <InputError message={errors.recovery_code} />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Verifying...</span>
                    {:else}
                        <span>Verify & Continue 🚀</span>
                    {/if}
                </button>

                <div
                    class="text-center text-xs text-gray-400 pt-2 border-t border-gray-800/60"
                >
                    <span>or you can </span>
                    <button
                        type="button"
                        class="text-indigo-400 hover:text-indigo-300 font-semibold cursor-pointer underline hover:text-white transition-colors"
                        onclick={() => toggleRecoveryMode(clearErrors)}
                    >
                        {authConfigContent.buttonText}
                    </button>
                </div>
            {/snippet}
        </Form>
    {/if}
</AuthLayout>
