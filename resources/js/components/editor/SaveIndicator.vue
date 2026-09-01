<script setup lang="ts">
import { computed } from 'vue';
import type { SaveState } from '@/lib/types';

const props = defineProps<{ state: SaveState; error: string | null }>();

const display = computed(() => {
    switch (props.state) {
        case 'pending':
        case 'saving':
            return { label: 'Enregistrement…', color: 'neutral' as const, icon: 'i-lucide-loader-circle' };
        case 'saved':
            return { label: 'Enregistré', color: 'success' as const, icon: 'i-lucide-check' };
        case 'error':
            return { label: 'Échec', color: 'error' as const, icon: 'i-lucide-triangle-alert' };
        case 'readonly':
            return { label: 'Lecture seule', color: 'warning' as const, icon: 'i-lucide-lock' };
        default:
            return { label: 'À jour', color: 'neutral' as const, icon: 'i-lucide-cloud' };
    }
});
</script>

<template>
    <UTooltip :text="error ?? undefined" :disabled="!error">
        <UBadge :color="display.color" variant="subtle" :icon="display.icon">
            {{ display.label }}
        </UBadge>
    </UTooltip>
</template>
