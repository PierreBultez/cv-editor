<script setup lang="ts">
import type { ExperienceItem } from '@/lib/types';

defineProps<{ items: ExperienceItem[] }>();
</script>

<template>
    <div class="mt-1">
        <article v-for="(experience, index) in items" :key="index" class="cv-experience">
            <div class="pt-[1px] text-[7.7px] font-medium" :style="{ color: 'var(--cv-primary-600)' }">
                {{ experience.period }}
            </div>

            <div class="relative flex justify-center pt-[4px]">
                <span class="cv-timeline-dot" />
                <span class="cv-timeline-line" />
            </div>

            <div>
                <h3 class="text-[8.4px] font-bold leading-tight text-slate-900">
                    {{ experience.role }}
                    <span v-if="experience.company" class="font-medium" :style="{ color: 'var(--cv-primary-600)' }">
                        — {{ experience.company }}<template v-if="experience.location"> — {{ experience.location }}</template>
                    </span>
                </h3>

                <ul
                    v-if="experience.bullets.some((bullet) => bullet.trim())"
                    class="mt-1 list-disc space-y-[1px] pl-3 text-[7.5px] leading-[1.35] text-slate-700"
                >
                    <template v-for="(bullet, bulletIndex) in experience.bullets" :key="bulletIndex">
                        <li v-if="bullet.trim()">{{ bullet }}</li>
                    </template>
                </ul>
            </div>
        </article>
    </div>
</template>
