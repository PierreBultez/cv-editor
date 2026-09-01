<script setup lang="ts">
import type { DiplomaItem } from '@/lib/types';

/** Formations et certifications : meme presentation, en deux colonnes. */
defineProps<{ items: DiplomaItem[] }>();
</script>

<template>
    <div class="mt-2 grid grid-cols-[84px_1fr] gap-y-1.5 text-[7.8px] leading-[1.35]">
        <template v-for="(entry, index) in items" :key="index">
            <span class="font-medium" :style="{ color: 'var(--cv-primary-600)' }">{{ entry.period }}</span>
            <div>
                <strong class="font-semibold text-slate-900">{{ entry.degree }}</strong>
                <span v-if="entry.school" :style="{ color: 'var(--cv-primary-600)' }">
                    — {{ entry.school }}<template v-if="entry.location"> — {{ entry.location }}</template>
                </span>
                <div v-if="entry.detail" class="text-slate-600">{{ entry.detail }}</div>
            </div>
        </template>
    </div>
</template>
