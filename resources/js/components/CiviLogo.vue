<script setup lang="ts">
/**
 * Logotype Civi.
 *
 * Le fichier source fait 1448 px de large : deux tailles reduites sont servies
 * via `srcset`, l'en-tete n'en affichant qu'une centaine. Les dimensions sont
 * declarees pour reserver la place et eviter que la page ne saute au
 * chargement.
 */
withDefaults(defineProps<{ height?: string }>(), { height: '1.75rem' });
</script>

<template>
    <img
        src="/logo-civi-320.png"
        srcset="/logo-civi-320.png 320w, /logo-civi-640.png 640w"
        sizes="(max-width: 640px) 120px, 160px"
        width="320"
        height="240"
        alt="Civi"
        class="w-auto"
        :style="{ height }"
    />
</template>
