import { defineConfig } from 'vite';
import { resolve } from 'node:path';

export default defineConfig({
    build: {
        outDir: resolve(__dirname, '../public/vendor'),
        emptyOutDir: false,
        cssCodeSplit: false,
        lib: {
            entry: resolve(__dirname, 'src/trade-in-calculator.js'),
            name: 'TradeInCalculator',
            formats: ['iife'],
            fileName: () => 'trade-in-widget.js',
        },
        rollupOptions: {
            output: {
                // single self-executing file so a plain <script> tag works on any vendor site
                inlineDynamicImports: true,
            },
        },
    },
});
