<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Met la feuille A4 (210 mm de large, taille fixe) a l'echelle de la place
 * disponible, et montre ou tombent les coupes de page.
 *
 * La feuille garde ses dimensions reelles en millimetres — c'est ce qui permet
 * a l'apercu d'etre exactement ce qui sort a l'impression. Seule une
 * transformation CSS la reduit visuellement, et cette transformation est
 * neutralisee dans la feuille de style d'impression.
 */
const props = withDefaults(
    defineProps<{
        zoom?: number;
        /** Reperes de page et verdict : utiles a l'auteur, hors de la page publique. */
        showGuides?: boolean;
    }>(),
    { zoom: 1, showGuides: false },
);

const PAGE_HEIGHT_MM = 297;
const PAGE_WIDTH_MM = 210;

const outer = ref<HTMLElement | null>(null);
const page = ref<HTMLElement | null>(null);
const scale = ref(1);
const scaledHeight = ref(0);
const contentMm = ref(0);

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

    // La largeur rendue vaut exactement 210 mm : elle sert de regle pour
    // convertir la hauteur mesuree en millimetres, sans supposer de DPI.
    contentMm.value = (page.value.offsetHeight * PAGE_WIDTH_MM) / natural;
}

const pageCount = computed(() => Math.max(1, Math.ceil(contentMm.value / PAGE_HEIGHT_MM - 0.001)));

/** Millimetres occupes sur la derniere page. */
const lastPageMm = computed(() => Math.round(contentMm.value - (pageCount.value - 1) * PAGE_HEIGHT_MM));

/**
 * Un debordement de quelques millimetres est le pire cas : une page
 * supplementaire presque vide, que l'auteur n'a aucune raison de vouloir.
 */
const severity = computed<'ok' | 'warning'>(() =>
    pageCount.value > 1 && lastPageMm.value < 60 ? 'warning' : 'ok',
);

const verdict = computed(() => {
    // La feuille porte un `min-height` de 297 mm : un CV court la remplit donc
    // en apparence, et l'espace restant n'est pas mesurable a partir de sa
    // hauteur. Mieux vaut ne rien affirmer que d'annoncer « 0 mm libres ».
    if (pageCount.value === 1) {
        return 'Tient sur une page';
    }

    if (severity.value === 'warning') {
        return `${pageCount.value} pages — la dernière ne porte que ${lastPageMm.value} mm de contenu`;
    }

    return `${pageCount.value} pages — ${lastPageMm.value} mm sur la dernière`;
});

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

defineExpose({ refresh, pageCount, contentMm });
</script>

<template>
    <div>
        <div
            v-if="showGuides"
            class="no-print mb-2 flex items-center gap-2 text-xs"
            :class="severity === 'warning' ? 'text-warning' : 'text-muted'"
        >
            <UIcon
                :name="severity === 'warning' ? 'i-lucide-triangle-alert' : 'i-lucide-file-text'"
                class="size-4 shrink-0"
            />
            <span>{{ verdict }}</span>
        </div>

        <div ref="outer" class="cv-scale-outer w-full overflow-hidden" :style="{ height: `${scaledHeight}px` }">
            <div
                ref="page"
                class="cv-scale relative w-max origin-top-left shadow-[0_20px_55px_rgba(15,23,42,0.16)]"
                :style="{ transform: `scale(${scale})` }"
            >
                <slot />

                <!--
                    Reperes places dans le repere non mis a l'echelle : `297mm`
                    designe donc la vraie coupe, quel que soit le zoom applique.
                -->
                <template v-if="showGuides">
                    <div
                        v-for="n in pageCount - 1"
                        :key="n"
                        class="no-print pointer-events-none absolute inset-x-0 z-20"
                        :style="{ top: `${n * PAGE_HEIGHT_MM}mm` }"
                    >
                        <div class="border-t-2 border-dashed border-red-500/60" />
                        <span
                            class="absolute right-0 top-0 rounded-b bg-red-500/85 px-2 py-0.5 text-[11px] font-medium text-white"
                        >
                            Page {{ n + 1 }}
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
