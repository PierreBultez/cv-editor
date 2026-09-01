<script setup lang="ts">
import type { CvSection, DiplomaItem, ExperienceItem, LanguageItem, SkillItem } from '@/lib/types';
import SkillList from '@/components/cv/SkillList.vue';
import LanguageList from '@/components/cv/LanguageList.vue';
import BulletList from '@/components/cv/BulletList.vue';
import ExperienceList from '@/components/cv/ExperienceList.vue';
import DiplomaList from '@/components/cv/DiplomaList.vue';

/**
 * Aiguillage unique entre un type de section et son rendu. Les deux templates
 * s'en servent, ce qui garantit qu'ajouter un type de section n'oblige pas a
 * modifier chaque mise en page.
 */
defineProps<{ section: CvSection }>();
</script>

<template>
    <SkillList v-if="section.type === 'skills'" :items="section.items as SkillItem[]" />
    <LanguageList v-else-if="section.type === 'languages'" :items="section.items as LanguageItem[]" />
    <BulletList
        v-else-if="section.type === 'tools' || section.type === 'interests'"
        :items="section.items as string[]"
    />
    <ExperienceList v-else-if="section.type === 'experiences'" :items="section.items as ExperienceItem[]" />
    <DiplomaList v-else :items="section.items as DiplomaItem[]" />
</template>
