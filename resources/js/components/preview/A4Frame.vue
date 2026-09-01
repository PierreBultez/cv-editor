<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
        /**
         * Loupe. Sur un telephone, la feuille entiere tient dans 45 % de sa
         * taille reelle : le corps de texte y tombe sous les 5 px et l'apercu
         * ne prouve plus rien. Le grossissement rend la feuille lisible, au
         * prix d'un defilement horizontal *dans le cadre* — jamais dans la page.
         */
        controls?: boolean;
    }>(),
    { zoom: 1, showGuides: false, controls: false },
);

const PAGE_HEIGHT_MM = 297;
const PAGE_WIDTH_MM = 210;
const ZOOM_STEPS = [1, 1.5, 2, 3];

const outer = ref<HTMLElement | null>(null);
const page = ref<HTMLElement | null>(null);
const scale = ref(1);
const scaledWidth = ref(0);
const scaledHeight = ref(0);
const contentMm = ref(0);

/** Facteur applique par-dessus l'ajustement a la largeur disponible. */
const magnify = ref(1);

/** Echelle a laquelle la feuille tient tout juste dans le cadre. */
const fitScale = ref(1);

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
    fitScale.value = Math.min(1, available / natural);
    scale.value = fitScale.value * props.zoom * magnify.value;

    // La transformation ne change pas la boite de mise en page : sans ces deux
    // dimensions, le cadre reserverait toujours les 794 px de la feuille et
    // pousserait un defilement horizontal a *toute* la page. C'est exactement
    // ce qui rendait l'editeur inutilisable sur telephone.
    scaledWidth.value = natural * scale.value;
    scaledHeight.value = page.value.offsetHeight * scale.value;

    // La largeur rendue vaut exactement 210 mm : elle sert de regle pour
    // convertir la hauteur mesuree en millimetres, sans supposer de DPI.
    contentMm.value = (page.value.offsetHeight * PAGE_WIDTH_MM) / natural;
}

watch(magnify, refresh);
watch(() => props.zoom, refresh);

/** Le grossissement n'a de sens que si la feuille est deja reduite. */
const canMagnify = computed(() => fitScale.value < 0.999);

const magnifyPercent = computed(() => Math.round(scale.value * 100));

function stepMagnify(direction: 1 | -1): void {
    const ceiling = fitScale.value > 0 ? 1 / fitScale.value : 1;
    const steps = ZOOM_STEPS.filter((step) => step <= ceiling + 0.001).concat(ceiling);
    const current = magnify.value;

    const next =
        direction === 1
            ? steps.find((step) => step > current + 0.001)
            : [...steps].reverse().find((step) => step < current - 0.001);

    magnify.value = next ?? current;
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
    <div class="min-w-0">
        <div
            v-if="showGuides || (controls && canMagnify)"
            class="no-print mb-2 flex items-center gap-2 text-xs"
        >
            <template v-if="showGuides">
                <UIcon
                    :name="severity === 'warning' ? 'i-lucide-triangle-alert' : 'i-lucide-file-text'"
                    class="size-4 shrink-0"
                    :class="severity === 'warning' ? 'text-warning' : 'text-muted'"
                />
                <span :class="severity === 'warning' ? 'text-warning' : 'text-muted'">{{ verdict }}</span>
            </template>

            <div v-if="controls && canMagnify" class="ml-auto flex shrink-0 items-center gap-1">
                <UButton
                    size="xs"
                    variant="subtle"
                    color="neutral"
                    icon="i-lucide-zoom-out"
                    aria-label="Réduire l'aperçu"
                    :disabled="magnify <= 1"
                    @click="stepMagnify(-1)"
                />
                <span class="w-10 text-center tabular-nums text-muted">{{ magnifyPercent }}%</span>
                <UButton
                    size="xs"
                    variant="subtle"
                    color="neutral"
                    icon="i-lucide-zoom-in"
                    aria-label="Agrandir l'aperçu"
                    :disabled="scale >= 0.999"
                    @click="stepMagnify(1)"
                />
            </div>
        </div>

        <!--
            Le defilement horizontal est confine ici : au-dela de l'echelle
            d'ajustement, c'est le cadre qui defile, pas la page.
        -->
        <div ref="outer" class="cv-scale-outer w-full overflow-x-auto overflow-y-hidden">
            <div
                class="cv-scale-box relative"
                :style="{ width: `${scaledWidth}px`, height: `${scaledHeight}px` }"
            >
                <div
                    ref="page"
                    class="cv-scale absolute left-0 top-0 w-max origin-top-left shadow-[0_20px_55px_rgba(15,23,42,0.16)]"
                    :style="{ transform: `scale(${scale})` }"
                >
                    <slot />

                    <!--
                        Reperes places dans le repere non mis a l'echelle :
                        `297mm` designe donc la vraie coupe, quel que soit le
                        zoom applique.
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
    </div>
</template>
