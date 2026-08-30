import { defineConfig } from 'vite';
import { fileURLToPath, URL } from 'node:url';
import laravel from 'laravel-vite-plugin';
import { bunny, local } from 'laravel-vite-plugin/fonts';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import ui from '@nuxt/ui/vite';

/**
 * Catalogue des polices proposées dans le générateur de CV.
 *
 * Chaque famille expose une variable CSS `--font-{alias}` que le sélecteur de
 * polices affecte ensuite à `--cv-font-title` / `--cv-font-body` sur la racine
 * de l'aperçu. Ajouter une police au catalogue = ajouter une entrée ici plus une
 * entrée dans `resources/js/lib/fonts.ts`.
 *
 * `preload` reste à false sauf pour les deux polices par défaut : les fichiers
 * sont bien tous téléchargés au build, mais le navigateur ne récupère une
 * famille que lorsqu'elle est réellement appliquée dans l'aperçu.
 */
const cvFonts = [
    local('Satoshi', {
        alias: 'satoshi',
        variants: [
            {
                src: ['resources/fonts/Satoshi-Variable.woff2', 'resources/fonts/Satoshi-Variable.woff'],
                weight: '300 900',
                style: 'normal',
            },
            {
                src: ['resources/fonts/Satoshi-VariableItalic.woff2', 'resources/fonts/Satoshi-VariableItalic.woff'],
                weight: '300 900',
                style: 'italic',
            },
        ],
        fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
    }),
    bunny('Inter', {
        alias: 'inter',
        weights: [400, 500, 600, 700, 800, 900],
        fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
    }),
    bunny('Outfit', {
        alias: 'outfit',
        weights: [400, 500, 600, 700, 800, 900],
        preload: false,
        fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
    }),
    bunny('Space Grotesk', {
        alias: 'space-grotesk',
        weights: [400, 500, 600, 700],
        preload: false,
        fallbacks: ['ui-sans-serif', 'system-ui', 'sans-serif'],
    }),
    bunny('Fraunces', {
        alias: 'fraunces',
        weights: [400, 500, 600, 700, 800, 900],
        preload: false,
        fallbacks: ['ui-serif', 'Georgia', 'serif'],
    }),
    bunny('Playfair Display', {
        alias: 'playfair',
        weights: [400, 500, 600, 700, 800, 900],
        preload: false,
        fallbacks: ['ui-serif', 'Georgia', 'serif'],
    }),
    bunny('Lora', {
        alias: 'lora',
        weights: [400, 500, 600, 700],
        preload: false,
        fallbacks: ['ui-serif', 'Georgia', 'serif'],
    }),
];

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: cvFonts,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
        ui(),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
