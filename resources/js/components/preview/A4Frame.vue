<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Met la feuille A4 (210 mm de large, taille fixe) a l'echelle de la place
 * disponible.
 *
 * La feuille garde ses dimensions reelles en millimetres — c'est ce qui permet
 * a l'apercu d'etre exactement ce qui sort a l'impression. Seule une
 * transformation CSS la reduit visuellement, et cette transformation est
 * neutralisee dans la feuille de style d'impression.
 */
const props = withDefaults(defineProps<{ zoom?: number }>(), { zoom: 1 });

const outer = ref<HTMLElement | null>(null);
const page = ref<HTMLElement | null>(null);
const scale = ref(1);
const scaledHeight = ref(0);

let observer: ResizeObserver | null = null;

function refresh(): void {
    if (!outer.value || !page.value) {
        return;
    }

    const available = outer.value.clientWidth;
    const natural = page.value.offsetWidth;

    if (!available || !natural) {
        return;
    }

    // On ne grandit jamais au-dela de la taille reelle : un CV affiche plus
    // grand que l'A4 donnerait une fausse idee des corps de texte.
    scale.value = Math.min(1, available / natural) * props.zoom;
    scaledHeight.value = page.value.offsetHeight * scale.value;
}

onMounted(() => {
    // Deux cibles : la largeur disponible change avec la fenetre, la hauteur de
    // la feuille change des que l'utilisateur ajoute du contenu.
    observer = new ResizeObserver(refresh);

    if (outer.value) observer.observe(outer.value);
    if (page.value) observer.observe(page.value);

    refresh();
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});

defineExpose({ refresh });
</script>

<template>
    <div ref="outer" class="cv-scale-outer w-full overflow-hidden" :style="{ height: `${scaledHeight}px` }">
        <div
            ref="page"
            class="cv-scale w-max origin-top-left shadow-[0_20px_55px_rgba(15,23,42,0.16)]"
            :style="{ transform: `scale(${scale})` }"
        >
            <slot />
        </div>
    </div>
</template>
