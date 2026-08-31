<script setup lang="ts">
/** Sections « Logiciels / Outils » et « Centres d'intérêt » : de simples listes. */
defineProps<{ items: string[] }>();
</script>

<template>
    <ul class="mt-3 space-y-1 text-[8px] leading-[1.55] text-slate-700">
        <li v-for="(entry, index) in items" :key="index" class="flex gap-1.5">
            <span class="cv-bullet">•</span>
            <span>{{ entry }}</span>
        </li>
    </ul>
</template>
