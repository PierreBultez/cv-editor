<script setup lang="ts">
import type { LanguageItem } from '@/lib/types';

defineProps<{ items: LanguageItem[] }>();
</script>

<template>
    <div class="mt-3 space-y-2.5 text-[8px] leading-tight text-slate-700">
        <div v-for="(language, index) in items" :key="index" class="flex items-center justify-between gap-2">
            <div>
                <strong class="font-semibold text-slate-900">{{ language.label }}</strong>
                <div v-if="language.mention">{{ language.mention }}</div>
            </div>
            <div class="flex gap-1">
                <i
                    v-for="dot in 5"
                    :key="dot"
                    class="cv-dot"
                    :class="{ 'cv-dot--on': dot <= language.level }"
                />
            </div>
        </div>
    </div>
</template>
