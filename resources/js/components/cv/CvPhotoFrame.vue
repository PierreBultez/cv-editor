<script setup lang="ts">
import type { CvPhoto } from '@/lib/types';

/**
 * Rend les variantes generees par `App\Services\PhotoProcessor`.
 *
 * AVIF n'est present que si l'hote sait l'encoder ; le JPEG est le seul format
 * garanti, d'ou son role de `src` de repli.
 */
withDefaults(defineProps<{ photo: CvPhoto | null; alt: string; size?: string }>(), {
    size: '6rem',
});
</script>

<template>
    <picture v-if="photo">
        <source v-if="photo.avif" :srcset="photo.avif" type="image/avif" />
        <source v-if="photo.webp" :srcset="photo.webp" type="image/webp" />
        <img
            :src="photo.jpg"
            :alt="alt"
            :width="photo.width"
            :height="photo.height"
            :style="{ width: size, height: size }"
            class="rounded-full border-4 border-white object-cover shadow-sm"
        />
    </picture>
</template>
