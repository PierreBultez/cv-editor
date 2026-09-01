<script setup lang="ts">
import type { CvSection, DiplomaItem, ExperienceItem, LanguageItem, SkillItem } from '@/lib/types';
import { blankItem } from '@/stores/useCvStore';
import { dotsForLevel, levelOptions } from '@/lib/languages';

const props = defineProps<{ section: CvSection; disabled?: boolean }>();

function add(): void {
    props.section.items.push(blankItem(props.section.type));
}

function remove(index: number): void {
    props.section.items.splice(index, 1);
}

/**
 * Boutons plutot que glisser-deposer : les items n'ont pas d'identifiant
 * stable, seul leur index les distingue. Un glisser-deposer sur des cles
 * d'index deplace le focus de saisie de facon imprevisible.
 */
function move(index: number, delta: number): void {
    const target = index + delta;

    if (target < 0 || target >= props.section.items.length) {
        return;
    }

    const items = props.section.items;
    [items[index], items[target]] = [items[target], items[index]];
}

function asExperience(item: unknown): ExperienceItem {
    return item as ExperienceItem;
}

function asDiploma(item: unknown): DiplomaItem {
    return item as DiplomaItem;
}

function asSkill(item: unknown): SkillItem {
    return item as SkillItem;
}

function asLanguage(item: unknown): LanguageItem {
    return item as LanguageItem;
}

/**
 * `USlider` renvoie un tableau a un element des qu'on le manipule — il est bati
 * sur un curseur multi-poignees. Un `v-model` direct remplacerait donc le
 * niveau (un entier) par `[55]`, que le serveur refuse : la sauvegarde
 * automatique echouait des la premiere modification d'un curseur.
 *
 * On normalise ici plutot que de faire confiance a la forme rendue par la
 * bibliotheque, et l'arrondi protege au passage d'une valeur flottante.
 */
/**
 * Un niveau CECR porte deja l'information de maitrise : les pastilles en
 * decoulent, plutot que d'etre reglees a part.
 */
function applyLanguageLevel(item: LanguageItem, mention: string): void {
    item.mention = mention;
    item.level = dotsForLevel(mention) || item.level;
}

function toLevel(value: unknown): number {
    const raw = Array.isArray(value) ? value[0] : value;
    const parsed = Math.round(Number(raw));

    return Number.isFinite(parsed) ? parsed : 0;
}

const ADD_LABELS: Record<string, string> = {
    experiences: 'Ajouter une expérience',
    education: 'Ajouter une formation',
    certifications: 'Ajouter une certification',
    skills: 'Ajouter une compétence',
    languages: 'Ajouter une langue',
    tools: 'Ajouter un outil',
    interests: 'Ajouter un centre d’intérêt',
};
</script>

<template>
    <div class="space-y-3">
        <div
            v-for="(item, index) in section.items"
            :key="index"
            class="rounded-lg border border-default p-3"
        >
            <div class="mb-2 flex items-center justify-between gap-2">
                <span class="text-xs font-medium text-muted">{{ index + 1 }}</span>
                <div class="flex gap-1">
                    <UButton
                        size="xs"
                        variant="ghost"
                        color="neutral"
                        icon="i-lucide-chevron-up"
                        :disabled="disabled || index === 0"
                        @click="move(index, -1)"
                    />
                    <UButton
                        size="xs"
                        variant="ghost"
                        color="neutral"
                        icon="i-lucide-chevron-down"
                        :disabled="disabled || index === section.items.length - 1"
                        @click="move(index, 1)"
                    />
                    <UButton
                        size="xs"
                        variant="ghost"
                        color="error"
                        icon="i-lucide-trash-2"
                        :disabled="disabled"
                        @click="remove(index)"
                    />
                </div>
            </div>

            <!-- Expériences -->
            <div v-if="section.type === 'experiences'" class="space-y-2">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <UFormField label="Période" size="sm">
                        <UInput v-model="asExperience(item).period" :disabled="disabled" placeholder="2020 — 2022" />
                    </UFormField>
                    <UFormField label="Poste" size="sm">
                        <UInput v-model="asExperience(item).role" :disabled="disabled" />
                    </UFormField>
                    <UFormField label="Entreprise" size="sm">
                        <UInput v-model="asExperience(item).company" :disabled="disabled" />
                    </UFormField>
                    <UFormField label="Lieu" size="sm">
                        <UInput v-model="asExperience(item).location" :disabled="disabled" placeholder="Lyon (69)" />
                    </UFormField>
                </div>

                <UFormField label="Missions" size="sm">
                    <div class="space-y-1.5">
                        <div
                            v-for="(_, bulletIndex) in asExperience(item).bullets"
                            :key="bulletIndex"
                            class="flex gap-1.5"
                        >
                            <UTextarea
                                v-model="asExperience(item).bullets[bulletIndex]"
                                class="flex-1"
                                :rows="2"
                                autoresize
                                :disabled="disabled"
                            />
                            <UButton
                                size="xs"
                                variant="ghost"
                                color="neutral"
                                icon="i-lucide-x"
                                :disabled="disabled"
                                @click="asExperience(item).bullets.splice(bulletIndex, 1)"
                            />
                        </div>
                        <UButton
                            size="xs"
                            variant="soft"
                            color="neutral"
                            icon="i-lucide-plus"
                            :disabled="disabled"
                            @click="asExperience(item).bullets.push('')"
                        >
                            Ajouter une ligne
                        </UButton>
                    </div>
                </UFormField>
            </div>

            <!-- Formations et certifications -->
            <div v-else-if="section.type === 'education' || section.type === 'certifications'" class="space-y-2">
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <UFormField label="Période" size="sm">
                        <UInput v-model="asDiploma(item).period" :disabled="disabled" placeholder="2025" />
                    </UFormField>
                    <UFormField label="Intitulé" size="sm">
                        <UInput v-model="asDiploma(item).degree" :disabled="disabled" />
                    </UFormField>
                    <UFormField label="Établissement" size="sm">
                        <UInput v-model="asDiploma(item).school" :disabled="disabled" />
                    </UFormField>
                    <UFormField label="Lieu" size="sm">
                        <UInput v-model="asDiploma(item).location" :disabled="disabled" />
                    </UFormField>
                </div>
                <UFormField label="Précision" size="sm">
                    <UInput v-model="asDiploma(item).detail" :disabled="disabled" />
                </UFormField>
            </div>

            <!-- Compétences -->
            <div v-else-if="section.type === 'skills'" class="space-y-2">
                <UInput v-model="asSkill(item).label" :disabled="disabled" placeholder="Backend : PHP, Laravel…" />
                <div class="flex items-center gap-3">
                    <USlider
                        class="min-w-0 flex-1"
                        :model-value="asSkill(item).level"
                        :min="0"
                        :max="100"
                        :step="5"
                        :disabled="disabled"
                        @update:model-value="asSkill(item).level = toLevel($event)"
                    />
                    <span class="w-10 shrink-0 text-right text-xs tabular-nums text-muted">
                        {{ asSkill(item).level }}%
                    </span>
                </div>
            </div>

            <!--
                Langues : le niveau CECR pilote a la fois la mention affichée et
                le nombre de pastilles. Deux réglages distincts pour la même
                information finissaient par se contredire.
            -->
            <div v-else-if="section.type === 'languages'" class="space-y-2">
                <UFormField label="Langue" size="sm">
                    <UInput v-model="asLanguage(item).label" :disabled="disabled" placeholder="Anglais" />
                </UFormField>

                <UFormField label="Niveau" size="sm" hint="Échelle du CECR">
                    <div class="flex items-center gap-3">
                        <USelect
                            :model-value="asLanguage(item).mention"
                            :items="levelOptions(asLanguage(item).mention)"
                            placeholder="Choisir un niveau"
                            class="min-w-0 flex-1"
                            :disabled="disabled"
                            @update:model-value="applyLanguageLevel(asLanguage(item), String($event))"
                        />
                        <span class="flex shrink-0 gap-1">
                            <i
                                v-for="dot in 5"
                                :key="dot"
                                class="size-2 rounded-full"
                                :class="dot <= asLanguage(item).level ? 'bg-primary' : 'bg-accented'"
                            />
                        </span>
                    </div>
                </UFormField>
            </div>

            <!-- Outils et centres d'intérêt -->
            <UInput v-else v-model="section.items[index]" :disabled="disabled" />
        </div>

        <UButton
            size="sm"
            variant="soft"
            icon="i-lucide-plus"
            :disabled="disabled"
            @click="add"
        >
            {{ ADD_LABELS[section.type] ?? 'Ajouter' }}
        </UButton>
    </div>
</template>
