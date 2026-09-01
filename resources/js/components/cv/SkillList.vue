<script setup lang="ts">
import type { SkillItem } from '@/lib/types';

defineProps<{ items: SkillItem[] }>();
</script>

<template>
    <div class="mt-3 space-y-2 text-[8px] text-slate-700">
        <div
            v-for="(skill, index) in items"
            :key="index"
            class="grid grid-cols-[1fr_63px] items-center gap-2"
        >
            <span>{{ skill.label }}</span>
            <div class="cv-skill-track">
                <div class="cv-skill-value" :style="{ width: `${Math.min(100, Math.max(0, skill.level))}%` }" />
            </div>
        </div>
    </div>
</template>
