<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import type { CvRecord } from '@/lib/types';
import A4Frame from '@/components/preview/A4Frame.vue';
import CvPreview from '@/components/preview/CvPreview.vue';

const props = defineProps<{ cv: CvRecord }>();

const title = props.cv.content.identity.fullName.trim() || 'CV';

/** `window` n'est pas exposé aux templates Vue, d'où ce relais. */
function print(): void {
    window.print();
}
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[#e9edf3] py-[12mm]">
        <div class="no-print mx-auto mb-6 flex max-w-[210mm] justify-end gap-2 px-4">
            <UButton icon="i-lucide-printer" color="neutral" @click="print"> Imprimer / PDF </UButton>
        </div>

        <div class="mx-auto max-w-[210mm] px-4">
            <A4Frame>
                <CvPreview :cv="cv" />
            </A4Frame>
        </div>
    </div>
</template>
