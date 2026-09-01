<script setup lang="ts">
import { computed, ref } from 'vue';
import type { CvTheme } from '@/lib/types';
import { buildScale } from '@/lib/palette';
import { COLOR_FAMILIES, COLOR_PAIRS } from '@/lib/palette-families';

const theme = defineModel<CvTheme>({ required: true });

/** Couleur en cours de réglage dans la grille des familles. */
const target = ref<'primary' | 'accent'>('primary');

const STOPS = [50, 100, 200, 500, 600, 700, 900];

const scales = computed(() => ({
    primary: buildScale(theme.value.primary),
    accent: buildScale(theme.value.accent),
}));

function isHex(value: string): boolean {
    return /^#[0-9a-fA-F]{6}$/.test(value);
}

function setHex(key: 'primary' | 'accent', value: string): void {
    if (isHex(value)) {
        theme.value = { ...theme.value, [key]: value.toLowerCase() };
    }
}

function applyPair(pair: { primary: string; accent: string }): void {
    theme.value = { primary: pair.primary, accent: pair.accent };
}

function isSelected(hex: string): boolean {
    return theme.value[target.value].toLowerCase() === hex.toLowerCase();
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <p class="mb-2 text-sm font-medium">Duos prêts à l'emploi</p>
            <div class="flex flex-wrap gap-2">
                <UButton
                    v-for="pair in COLOR_PAIRS"
                    :key="pair.label"
                    size="xs"
                    variant="outline"
                    color="neutral"
                    @click="applyPair(pair)"
                >
                    <span class="mr-1.5 inline-flex">
                        <span class="size-3 rounded-l-full" :style="{ background: pair.primary }" />
                        <span class="size-3 rounded-r-full" :style="{ background: pair.accent }" />
                    </span>
                    {{ pair.label }}
                </UButton>
            </div>
        </div>

        <!--
            La grille agit sur une seule des deux couleurs à la fois : sans ce
            choix explicite, cliquer une teinte laisserait deviner laquelle des
            deux vient de changer.
        -->
        <div>
            <UTabs
                v-model="target"
                size="xs"
                :items="[
                    { label: 'Couleur principale', value: 'primary' },
                    { label: 'Couleur secondaire', value: 'accent' },
                ]"
            />

            <p class="mt-2 text-xs text-muted">
                {{
                    target === 'primary'
                        ? 'Titres, intitulés et fond de la colonne latérale.'
                        : 'Barres de compétences, pastilles de langue et puces de liste.'
                }}
            </p>

            <div class="mt-3 grid grid-cols-8 gap-1.5 sm:grid-cols-11">
                <button
                    v-for="family in COLOR_FAMILIES"
                    :key="family.key"
                    type="button"
                    :title="family.label"
                    :aria-label="family.label"
                    :aria-pressed="isSelected(family.hex)"
                    class="aspect-square rounded-md ring-offset-2 ring-offset-default transition"
                    :class="isSelected(family.hex) ? 'ring-2 ring-inverted' : 'hover:scale-110'"
                    :style="{ background: family.hex }"
                    @click="setHex(target, family.hex)"
                />
            </div>
        </div>

        <div v-for="key in (['primary', 'accent'] as const)" :key="key" class="space-y-2">
            <UFormField
                :label="key === 'primary' ? 'Couleur principale' : 'Couleur secondaire'"
                hint="Ou une valeur libre"
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
                        class="min-w-0 flex-1 font-mono"
                        placeholder="#155dfc"
                        @update:model-value="setHex(key, String($event))"
                    />
                </div>
            </UFormField>

            <!--
                Les nuances sont affichées telles qu'elles seront rendues. Une
                couleur trop claire pour rester lisible sur du blanc est
                légèrement assombrie : autant le montrer que le subir.
            -->
            <div class="flex overflow-hidden rounded-md">
                <span
                    v-for="stop in STOPS"
                    :key="stop"
                    class="h-6 flex-1"
                    :style="{ background: scales[key][stop] }"
                    :title="`${stop} — ${scales[key][stop]}`"
                />
            </div>
        </div>
    </div>
</template>
