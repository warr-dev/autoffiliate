<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { update } from '@/routes/password';

    let {
        token,
        email,
        passwordRules,
    }: {
        token: string;
        email: string;
        passwordRules?: string;
    } = $props();
</script>

<AppHead title="Reset password" />

<AuthLayout
    title="Reset password"
    description="Please enter your new password below."
>
    <Form
        {...update.form()}
        transform={(data) => ({ ...data, token, email })}
        resetOnSuccess={['password', 'password_confirmation']}
        class="flex flex-col gap-4"
    >
        {#snippet children({ errors, processing })}
            <input type="hidden" name="_token" value={page.props.csrf_token} />
            <div class="flex flex-col gap-3.5">
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
                        autocomplete="email"
                        value={email}
                        readonly
                        class="opacity-70 cursor-not-allowed"
                    />
                    <InputError message={errors.email} />
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label
                        for="password"
                        class="text-xs font-semibold text-gray-300"
                    >
                        New Password
                    </Label>
                    <PasswordInput
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        passwordrules={passwordRules}
                    />
                    <InputError message={errors.password} />
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label
                        for="password_confirmation"
                        class="text-xs font-semibold text-gray-300"
                    >
                        Confirm New Password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="••••••••"
                        passwordrules={passwordRules}
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    data-test="reset-password-button"
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Updating password...</span>
                    {:else}
                        <span>Reset Password 🔒</span>
                    {/if}
                </button>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
