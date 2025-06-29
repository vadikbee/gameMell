import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'; // Добавьте импорт Vue

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/views/**/*.blade.php' // Добавьте шаблоны Blade
            ],
            refresh: true,
        }),
        vue() // Добавьте плагин Vue
    ],
    resolve: {
        alias: {
            '@': '/resources/js', // Добавьте алиас
        },
    },
    server: {
        host: '0.0.0.0',
        port: 3000,
        strictPort: true,
        hmr: {
            host: 'localhost',
            protocol: 'ws'
        },
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
                rewrite: (path) => path.replace(/^\/api/, '/api'), // Исправлено
                secure: false,
            }
        }
    }
});