<script setup lang="ts">
import { computed } from 'vue';

/**
 * Jeu d'icones du CV, repris tel quel du CV statique d'origine.
 *
 * Les traces sont inlines : le CV doit rester fidele a l'impression et en PDF,
 * or une police d'icones ou une image externe ne sont pas garanties de charger
 * a temps dans la fenetre d'impression du navigateur.
 */
const props = withDefaults(
    defineProps<{
        name: string;
        /** Certaines icones sont pleines et ne doivent pas etre tracees. */
        filled?: boolean;
    }>(),
    { filled: false },
);

const PATHS: Record<string, string> = {
    user: '<circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/>',
    mail: '<path d="M4 5h16v14H4z"/><path d="m4 7 8 6 8-6"/>',
    phone: '<path d="M6.5 3.5 10 7l-2 3a16 16 0 0 0 6 6l3-2 3.5 3.5-2 3c-.6.9-1.7 1.3-2.7 1A20.5 20.5 0 0 1 2.5 8.2c-.3-1 .1-2.1 1-2.7z"/>',
    location: '<path d="M12 21s6-5.4 6-11a6 6 0 1 0-12 0c0 5.6 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/>',
    globe: '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.3 2.4 3.5 5.4 3.5 9S14.3 18.6 12 21M12 3C9.7 5.4 8.5 8.4 8.5 12s1.2 6.6 3.5 9"/>',
    code: '<path d="m8 8-4 4 4 4M16 8l4 4-4 4M14 4l-4 16"/>',
    monitor: '<rect x="3" y="4" width="18" height="13" rx="1"/><path d="M8 21h8M12 17v4"/>',
    star: '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',
    briefcase:
        '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>',
    graduation: '<path d="m3 9 9-5 9 5-9 5-9-5Z"/><path d="M7 12.5V17c3 2 7 2 10 0v-4.5M21 9v6"/>',
    award: '<circle cx="12" cy="9" r="5"/><path d="m9 13-2 8 5-3 5 3-2-8"/>',
    linkedin:
        '<path d="M6.2 8.2H2.7V21h3.5V8.2ZM4.45 2A2.05 2.05 0 1 0 4.5 6.1 2.05 2.05 0 0 0 4.45 2ZM21 13.7c0-3.85-2.05-5.64-4.8-5.64-2.2 0-3.2 1.22-3.76 2.08V8.2H8.95V21h3.49v-6.34c0-1.67.32-3.3 2.4-3.3 2.04 0 2.07 1.9 2.07 3.4V21H21v-7.3Z"/>',
};

const markup = computed(() => PATHS[props.name] ?? PATHS.user);
const isFilled = computed(() => props.filled || props.name === 'linkedin');
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        :fill="isFilled ? 'currentColor' : 'none'"
        :stroke="isFilled ? 'none' : 'currentColor'"
        stroke-width="1.8"
        aria-hidden="true"
        v-html="markup"
    />
</template>
