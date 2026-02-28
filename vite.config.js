import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/site.js', 'resources/js/admin.js'],
            refresh: true,
        }),
    ],
});
