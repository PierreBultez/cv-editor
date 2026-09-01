<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { CvRecord } from '@/lib/types';
import A4Frame from '@/components/preview/A4Frame.vue';
import CvPreview from '@/components/preview/CvPreview.vue';
import CiviLogo from '@/components/CiviLogo.vue';

const props = defineProps<{ cv: CvRecord }>();

const title = props.cv.content.identity.fullName.trim() || 'CV';

/** `window` n'est pas exposé aux templates Vue, d'où ce relais. */
function print(): void {
    window.print();
}
</script>

<template>
    <Head :title="title" />

    <!--
        `print-canvas` / `print-target` neutralisent a l'impression le fond, les
        marges et la largeur de cet habillage : sans eux, les 12 mm de padding
        vertical et les 2 rem horizontaux decalent la feuille et la font
        deborder sur une seconde page.
    -->
    <div class="print-canvas min-h-screen bg-[#e9edf3] py-6 sm:py-[12mm]">
        <!--
            Une page publique se visite souvent depuis un lien direct : sans
            porte de sortie, il ne reste que le bouton « précédent » du
            navigateur, qui ne mène nulle part quand on arrive de l'extérieur.
        -->
        <div class="no-print mx-auto mb-6 flex max-w-[calc(210mm+2rem)] flex-wrap items-center gap-2 px-4">
            <UButton to="/" variant="ghost" color="neutral" class="gap-2">
                <UIcon name="i-lucide-arrow-left" class="size-4" />
                <CiviLogo height="1.5rem" />
            </UButton>

            <div class="ml-auto flex shrink-0 gap-2">
                <UButton to="/" icon="i-lucide-plus" variant="subtle" aria-label="Créer mon CV">
                    <span class="hidden sm:inline">Créer mon CV</span>
                </UButton>
                <UButton
                    icon="i-lucide-printer"
                    color="neutral"
                    aria-label="Imprimer ou exporter en PDF"
                    @click="print"
                >
                    <span class="hidden sm:inline">Imprimer / PDF</span>
                    <span class="sm:hidden">PDF</span>
                </UButton>
            </div>
        </div>

        <!--
            La largeur maximale inclut le padding : en `border-box`, un
            `max-w-[210mm]` avec `px-4` ne laisserait que 210 mm - 2 rem a la
            feuille, qui serait donc affichee legerement reduite.
        -->
        <div class="print-target mx-auto max-w-[calc(210mm+2rem)] px-4">
            <!--
                La loupe n'a d'effet que si la feuille est deja reduite : sur un
                telephone, elle est le seul moyen de lire un CV recu par lien.
            -->
            <A4Frame controls>
                <CvPreview :cv="cv" />
            </A4Frame>
        </div>
    </div>
</template>
