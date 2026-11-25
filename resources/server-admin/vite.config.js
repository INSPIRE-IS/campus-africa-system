import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';

export default defineConfig({
    base: './',
    esbuild: { jsx: 'automatic' },
    build: {
        manifest: true,
        outDir: fileURLToPath(new URL('../../public/server-admin-build', import.meta.url)),
        emptyOutDir: true,
        rollupOptions: {
            input: fileURLToPath(new URL('./black-friday.jsx', import.meta.url)),
        },
    },
});
