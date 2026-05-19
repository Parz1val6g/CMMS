import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin'; import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const hmrHost = env.VITE_HMR_HOST || 'localhost';

    return {
        resolve: {
            alias: {
                '@': '/resources/js',
            },
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.jsx'],
                refresh: true,
            }),
            react(),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            origin: `http://${hmrHost}:5173`,
            hmr: {
                host: hmrHost,
                port: 5173,
            },
            cors: {
                origin: '*',
            },
        },
    };
});
