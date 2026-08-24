import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

const isSvelteCheck = process.argv.some((argument) => argument.includes('svelte-check'));

if (isSvelteCheck) {
    process.env.LARAVEL_BYPASS_ENV_CHECK ??= '1';
}

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        svelte(),
        wayfinder({
            formVariants: true,
        }),
    ],
    build: {
        cssCodeSplit: true,
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('lucide-svelte')) {
                            return 'vendor-icons';
                        }
                        if (
                            id.includes('bits-ui') ||
                            id.includes('clsx') ||
                            id.includes('tailwind-merge') ||
                            id.includes('svelte-sonner') ||
                            id.includes('tw-animate-css')
                        ) {
                            return 'vendor-ui';
                        }
                        if (id.includes('@laravel/passkeys')) {
                            return 'vendor-passkeys';
                        }
                        if (id.includes('@inertiajs') || id.includes('svelte')) {
                            return 'vendor-core';
                        }
                        return 'vendor';
                    }
                },
            },
        },
    },
    server: {
        host: '0.0.0.0',
        hmr: {
            host: 'warpc',
        },
    },
});


