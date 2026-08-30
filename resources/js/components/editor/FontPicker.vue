<script setup lang="ts">
import type { CvFonts } from '@/lib/types';
import { FONTS, fontStack } from '@/lib/fonts';

const fonts = defineModel<CvFonts>({ required: true });

function select(key: 'title' | 'body', alias: string): void {
    fonts.value = { ...fonts.value, [key]: alias };
}
</script>

<template>
    <div class="space-y-6">
        <div v-for="key in (['title', 'body'] as const)" :key="key">
            <p class="mb-2 text-sm font-medium">
                {{ key === 'title' ? 'Police des titres' : 'Police du texte' }}
            </p>

            <div class="grid grid-cols-2 gap-2">
                <!--
                    Chaque option est rendue dans sa propre police : c'est le seul
                    moyen honnête de choisir. Le fichier n'est telecharge par le
                    navigateur qu'a cet instant, d'ou l'absence de prechargement.
                -->
                <button
                    v-for="font in FONTS"
                    :key="font.alias"
                    type="button"
                    class="rounded-lg border px-3 py-2 text-left transition"
                    :class="
                        fonts[key] === font.alias
                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                            : 'border-default hover:bg-elevated'
                    "
                    @click="select(key, font.alias)"
                >
                    <span class="block text-base leading-tight" :style="{ fontFamily: fontStack(font.alias) }">
                        {{ font.label }}
                    </span>
                    <span class="mt-0.5 block text-xs text-muted">{{ font.hint }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
