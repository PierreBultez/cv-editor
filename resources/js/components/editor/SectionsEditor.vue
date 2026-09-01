<script setup lang="ts">
import draggable from 'vuedraggable';
import type { CvContent, CvSection, SectionType } from '@/lib/types';
import { SECTION_LABELS, sectionLucideIcon } from '@/lib/sections';
import SectionItemsEditor from '@/components/editor/SectionItemsEditor.vue';

const props = defineProps<{ content: CvContent; disabled?: boolean; showColumns: boolean }>();

const TYPES = Object.keys(SECTION_LABELS) as SectionType[];

/** Un meme type peut apparaitre plusieurs fois, d'ou un identifiant suffixe. */
function addSection(type: SectionType): void {
    const existing = props.content.sections.filter((section) => section.type === type).length;

    props.content.sections.push({
        id: existing === 0 ? type : `${type}-${existing + 1}`,
        type,
        title: SECTION_LABELS[type],
        column: ['skills', 'languages', 'tools', 'interests'].includes(type) ? 'sidebar' : 'main',
        enabled: true,
        items: [],
    });
}

function removeSection(index: number): void {
    props.content.sections.splice(index, 1);
}

function toggleColumn(section: CvSection): void {
    section.column = section.column === 'sidebar' ? 'main' : 'sidebar';
}
</script>

<template>
    <div class="space-y-4">
        <!-- Les sections ont un `id` stable : le glisser-deposer y est sûr. -->
        <draggable
            :list="content.sections"
            item-key="id"
            handle=".section-handle"
            :animation="150"
            :disabled="disabled"
            class="space-y-3"
        >
            <template #item="{ element, index }">
                <div class="rounded-lg border border-default">
                    <!--
                        Sept elements sur une rangee ne tiennent pas dans 320 px :
                        le titre s'y reduisait a deux caracteres. Le groupe
                        d'actions passe donc sous le titre quand la place manque.
                    -->
                    <div class="flex flex-wrap items-center gap-2 border-b border-default px-2 py-2 sm:px-3">
                        <!-- La poignee est cliquable au doigt : 16 px d'icone, 36 px de cible. -->
                        <span
                            class="section-handle -m-1.5 flex size-9 shrink-0 cursor-grab touch-none items-center justify-center text-muted"
                            aria-label="Déplacer la section"
                        >
                            <UIcon name="i-lucide-grip-vertical" class="size-4" />
                        </span>
                        <UIcon :name="sectionLucideIcon(element.type)" class="size-4 shrink-0 text-muted" />

                        <UInput
                            v-model="element.title"
                            variant="none"
                            class="min-w-32 flex-1 font-medium"
                            :disabled="disabled"
                        />

                        <div class="ml-auto flex shrink-0 items-center gap-1">
                            <UBadge size="sm" variant="subtle" color="neutral">
                                {{ element.items.length }}
                            </UBadge>

                            <UTooltip v-if="showColumns" :text="element.column === 'sidebar' ? 'Colonne latérale' : 'Colonne principale'">
                                <UButton
                                    size="xs"
                                    variant="ghost"
                                    color="neutral"
                                    :icon="element.column === 'sidebar' ? 'i-lucide-panel-left' : 'i-lucide-square'"
                                    :aria-label="element.column === 'sidebar' ? 'Colonne latérale' : 'Colonne principale'"
                                    :disabled="disabled"
                                    @click="toggleColumn(element)"
                                />
                            </UTooltip>

                            <UTooltip :text="element.enabled ? 'Masquer du CV' : 'Afficher dans le CV'">
                                <UButton
                                    size="xs"
                                    variant="ghost"
                                    color="neutral"
                                    :icon="element.enabled ? 'i-lucide-eye' : 'i-lucide-eye-off'"
                                    :aria-label="element.enabled ? 'Masquer du CV' : 'Afficher dans le CV'"
                                    :disabled="disabled"
                                    @click="element.enabled = !element.enabled"
                                />
                            </UTooltip>

                            <UButton
                                size="xs"
                                variant="ghost"
                                color="error"
                                icon="i-lucide-trash-2"
                                aria-label="Supprimer la section"
                                :disabled="disabled"
                                @click="removeSection(index)"
                            />
                        </div>
                    </div>

                    <div class="p-2 sm:p-3" :class="{ 'opacity-50': !element.enabled }">
                        <SectionItemsEditor :section="element" :disabled="disabled" />
                    </div>
                </div>
            </template>
        </draggable>

        <UDropdownMenu
            :items="[TYPES.map((type) => ({ label: SECTION_LABELS[type], onSelect: () => addSection(type) }))]"
        >
            <UButton variant="outline" color="neutral" icon="i-lucide-plus" :disabled="disabled" block>
                Ajouter une section
            </UButton>
        </UDropdownMenu>
    </div>
</template>
