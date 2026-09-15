import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
     server: {
        host: '0.0.0.0',          // ✅ dengarkan di semua interface jalakan command ini 
                                    // ( php artisan serve --host=0.0.0.0 --port=8000 lalu untuk browser gunaka ini http://localhost:8000 > untuk mobile tinggal scan qr dan kalau akses sistem pos gunakan localhost 8000)
        hmr: {
            host: '10.216.145.175', // ✅ IP LAN komputer kamu — supaya hot reload jalan dari HP
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
});