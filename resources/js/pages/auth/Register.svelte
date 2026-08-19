<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import AuthLayout from '@/layouts/AuthLayout.svelte';
    import { login } from '@/routes';

    let { passwordRules }: { passwordRules?: string } = $props();
</script>

<AppHead title="Register" />

<AuthLayout
    title="Create an account"
    description="Join Aiffiliate to automate Shopee PH affiliate campaigns."
>
    <Form
        action="/register"
        method="post"
        resetOnSuccess={['password', 'password_confirmation']}
        class="flex flex-col gap-4"
    >
        {#snippet children({ errors, processing })}
            <div class="flex flex-col gap-3.5">
                <div class="flex flex-col gap-1.5">
                    <Label
                        for="name"
                        class="text-xs font-semibold text-gray-300"
                    >
                        Full Name
                    </Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autocomplete="name"
                        name="name"
                        placeholder="John Doe"
                    />
                    <InputError message={errors.name} />
                </div>

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
                        required
                        autocomplete="email"
                        name="email"
                        placeholder="you@example.com"
                    />
                    <InputError message={errors.email} />
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label
                        for="password"
                        class="text-xs font-semibold text-gray-300"
                    >
                        Password
                    </Label>
                    <PasswordInput
                        id="password"
                        required
                        autocomplete="new-password"
                        name="password"
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
                        Confirm Password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        passwordrules={passwordRules}
                    />
                    <InputError message={errors.password_confirmation} />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    data-test="register-user-button"
                    class="w-full py-3.5 mt-2 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-bold rounded-xl text-xs sm:text-sm shadow-lg shadow-indigo-500/25 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2"
                >
                    {#if processing}
                        <Spinner class="w-4 h-4 text-white" />
                        <span>Creating account...</span>
                    {:else}
                        <span>Create Account 🚀</span>
                    {/if}
                </button>
            </div>

            <div
                class="text-center text-xs text-gray-400 pt-2 border-t border-gray-800/60"
            >
                Already have an account?
                <TextLink href={login()} class="ml-1">Log in</TextLink>
            </div>
        {/snippet}
    </Form>
</AuthLayout>
