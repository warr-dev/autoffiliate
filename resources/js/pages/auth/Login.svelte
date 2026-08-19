<script lang="ts">
    import { Form, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasskeyVerify from '@/components/PasskeyVerify.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';

    let {
        status = '',
        canResetPassword = true,
    }: {
        status?: string;
        canResetPassword?: boolean;
    } = $props();
</script>

<AppHead title="Log in" />

<AuthLayout
    title="Log in"
    description="Sign in to access your automated affiliate content pipeline."
>
    {#if status}
        <div
            class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 flex items-center gap-2 font-medium"
        >
            <span>✓</span>
            <span>{status}</span>
        </div>
    {/if}

    <PasskeyVerify />

    <Form
        {...store.form()}
        resetOnSuccess={['password']}
        class="flex flex-col gap-4"
    >
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
                        required
                        autocomplete="email"
                        placeholder="you@example.com"
                    />
                    <InputError message={errors.email} />
                </div>

                <div class="flex flex-col gap-1.5">
                    <div class="flex items-center justify-between">
                        <Label
                            for="password"
                            class="text-xs font-semibold text-gray-300"
                        >
                            Password
                        </Label>
                        {#if canResetPassword}
                            <TextLink href={request()} class="text-xs">
                                Forgot password?
                            </TextLink>
                        {/if}
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError message={errors.password} />
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label
                        for="remember"
                        class="flex items-center gap-2 text-xs text-gray-400 cursor-pointer select-none"
                    >
                        <Checkbox id="remember" name="remember" />
                        <span>Remember me</span>
                    </label>
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    data-test="login-button"
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Authenticating...</span>
                    {:else}
                        <span>Sign In 🚀</span>
                    {/if}
                </button>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
