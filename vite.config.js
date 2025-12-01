import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Enable minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true,
            },
        },
        // Enable CSS minification
        cssMinify: true,
        // Optimize chunk splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs', 'axios'],
                },
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Enable source maps for production debugging (optional)
        sourcemap: false,
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['alpinejs', 'axios'],
    },
});
