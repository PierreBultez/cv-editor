<script setup lang="ts">
import { ref } from 'vue';

/**
 * Illustration de marque, servie depuis public/brand/.
 *
 * Ces images sont décoratives : le texte qu'elles accompagnent se suffit à
 * lui-même. Si le fichier manque, l'icône s'efface au lieu d'afficher le
 * pictogramme d'image cassée du navigateur — une page amputée d'une décoration
 * reste correcte, une page constellée d'images rompues ne l'est pas.
 */
defineProps<{ src: string; class?: string }>();

const failed = ref(false);
</script>

<template>
    <img
        v-if="!failed"
        :src="src"
        alt=""
        aria-hidden="true"
        :class="$props.class"
        @error="failed = true"
    />
</template>
