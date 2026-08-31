<script setup lang="ts">
import { computed } from 'vue';
import type { CvTheme } from '@/lib/types';
import { buildScale } from '@/lib/palette';

const theme = defineModel<CvTheme>({ required: true });

/** Duos prets a l'emploi, pour ne pas partir d'une page blanche. */
const PRESETS: Array<{ label: string; primary: string; accent: string }> = [
    { label: 'Bleu classique', primary: '#174d94', accent: '#245ea9' },
    { label: 'Ardoise', primary: '#334155', accent: '#0f766e' },
    { label: 'Bordeaux', primary: '#9f1239', accent: '#be123c' },
    { label: 'Forêt', primary: '#166534', accent: '#15803d' },
    { label: 'Prune', primary: '#6d28d9', accent: '#7c3aed' },
    { label: 'Cuivre', primary: '#9a3412', accent: '#c2410c' },
];

const STOPS = [50, 100, 200, 500, 600, 700, 900];

const primaryScale = computed(() => buildScale(theme.value.primary));
const accentScale = computed(() => buildScale(theme.value.accent));

function isHex(value: string): boolean {
    return /^#[0-9a-fA-F]{6}$/.test(value);
}

function setHex(key: 'primary' | 'accent', value: string): void {
    if (isHex(value)) {
        theme.value = { ...theme.value, [key]: value.toLowerCase() };
    }
}

function applyPreset(preset: { primary: string; accent: string }): void {
    theme.value = { primary: preset.primary, accent: preset.accent };
}
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-wrap gap-2">
            <UButton
                v-for="preset in PRESETS"
                :key="preset.label"
                size="xs"
                variant="outline"
                color="neutral"
                @click="applyPreset(preset)"
            >
                <span class="mr-1.5 inline-flex">
                    <span class="size-3 rounded-l-full" :style="{ background: preset.primary }" />
                    <span class="size-3 rounded-r-full" :style="{ background: preset.accent }" />
                </span>
                {{ preset.label }}
            </UButton>
        </div>

        <div v-for="key in (['primary', 'accent'] as const)" :key="key" class="space-y-2">
            <UFormField
                :label="key === 'primary' ? 'Couleur principale' : 'Couleur secondaire'"
                :description="
                    key === 'primary'
                        ? 'Titres, intitulés et fond de la colonne latérale.'
                        : 'Barres de compétences, pastilles de langue et puces de liste.'
                "
            >
                <div class="flex items-center gap-2">
                    <input
                        type="color"
                        class="h-9 w-12 cursor-pointer rounded border border-default bg-transparent"
                        :value="theme[key]"
                        @input="setHex(key, ($event.target as HTMLInputElement).value)"
                    />
                    <UInput
                        :model-value="theme[key]"
                        class="flex-1 font-mono"
                        placeholder="#174d94"
                        @update:model-value="setHex(key, String($event))"
                    />
                </div>
            </UFormField>

            <!--
                Les nuances sont affichees telles qu'elles seront rendues : la
                luminosite de chaque palier est imposee pour garantir le contraste
                a l'impression, une couleur tres claire ressort donc plus foncee
                que celle choisie. Mieux vaut le montrer que le subir.
            -->
            <div class="flex overflow-hidden rounded-md">
                <span
                    v-for="stop in STOPS"
                    :key="stop"
                    class="h-6 flex-1"
                    :style="{ background: (key === 'primary' ? primaryScale : accentScale)[stop] }"
                    :title="`${stop} — ${(key === 'primary' ? primaryScale : accentScale)[stop]}`"
                />
            </div>
        </div>
    </div>
</template>
