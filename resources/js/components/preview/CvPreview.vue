<script setup lang="ts">
import { computed } from 'vue';
import type { CvRecord } from '@/lib/types';
import { themeVariables } from '@/lib/palette';
import ClassicSidebar from '@/components/templates/ClassicSidebar.vue';
import CompactHeader from '@/components/templates/CompactHeader.vue';

/**
 * Point unique ou le theme devient du CSS.
 *
 * Les variables sont posees en style inline sur un conteneur englobant : elles
 * cascadent dans tout le template, aucun rebuild Tailwind n'est necessaire, et
 * changer une couleur ou une police se voit au prochain rendu de Vue.
 */
const props = defineProps<{ cv: CvRecord }>();

const TEMPLATES = {
    classic: ClassicSidebar,
    compact: CompactHeader,
} as const;

const component = computed(() => TEMPLATES[props.cv.template as keyof typeof TEMPLATES] ?? ClassicSidebar);
const variables = computed(() => themeVariables(props.cv.theme, props.cv.fonts));
</script>

<template>
    <div :style="variables">
        <component :is="component" :cv="cv" />
    </div>
</template>
