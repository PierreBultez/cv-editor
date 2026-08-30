<script setup lang="ts">
import { computed } from 'vue';
import type { ColumnName, CvRecord } from '@/lib/types';
import { CONTACT_ICONS, sectionIcon } from '@/lib/sections';
import CvIcon from '@/components/cv/CvIcon.vue';
import CvPhotoFrame from '@/components/cv/CvPhotoFrame.vue';
import CvSectionBody from '@/components/cv/CvSectionBody.vue';

/**
 * Mise en page « Classique » : portage du CV statique d'origine
 * (`legacy/index.html`), a l'identique en dimensions et en hierarchie
 * typographique, mais pilote par les donnees et par les variables `--cv-*`.
 */
const props = defineProps<{ cv: CvRecord }>();

/** Une section vide n'est pas rendue : elle existe dans l'editeur, pas sur la feuille. */
function sectionsIn(column: ColumnName) {
    return props.cv.content.sections.filter(
        (section) => section.column === column && section.enabled && section.items.length > 0,
    );
}

const sidebarSections = computed(() => sectionsIn('sidebar'));
const mainSections = computed(() => sectionsIn('main'));
const contacts = computed(() => props.cv.content.contact.filter((entry) => entry.value.trim() !== ''));
const identity = computed(() => props.cv.content.identity);
</script>

<template>
    <div class="cv-page grid grid-cols-[61mm_1fr]">
        <!-- ================= Colonne latérale ================= -->
        <aside
            class="px-[8mm] py-[10mm]"
            :style="{ background: `linear-gradient(to bottom, var(--cv-primary-50), #f9fbfd)` }"
        >
            <div v-if="cv.photo" class="mb-7 flex justify-center">
                <CvPhotoFrame :photo="cv.photo" :alt="identity.fullName" size="6rem" />
            </div>

            <section v-if="contacts.length" class="mb-6">
                <h2 class="cv-sidebar-title">
                    <span class="cv-icon-box"><CvIcon name="user" class="size-4" /></span>
                    Coordonnées
                </h2>

                <div class="mt-3 space-y-2 text-[8.5px] leading-[1.35] text-slate-700">
                    <p v-for="(entry, index) in contacts" :key="index" class="flex items-center gap-2">
                        <CvIcon
                            :name="CONTACT_ICONS[entry.type] ?? 'user'"
                            class="size-3.5 shrink-0"
                            :style="{ color: 'var(--cv-primary-600)' }"
                        />
                        <span>{{ entry.value }}</span>
                    </p>
                </div>
            </section>

            <section v-for="section in sidebarSections" :key="section.id" class="mb-6 last:mb-0">
                <h2 class="cv-sidebar-title">
                    <span class="cv-icon-box"><CvIcon :name="sectionIcon(section.type)" class="size-4" /></span>
                    {{ section.title }}
                </h2>
                <CvSectionBody :section="section" />
            </section>
        </aside>

        <!-- ================= Colonne principale ================= -->
        <section class="relative px-[9mm] py-[9mm]">
            <!-- Décor discret, purement graphique -->
            <div
                class="pointer-events-none absolute right-[8mm] top-[8mm] grid grid-cols-6 gap-[4px] opacity-50"
                aria-hidden="true"
            >
                <span
                    v-for="dot in 18"
                    :key="dot"
                    class="size-[2px] rounded-full"
                    :style="{ background: 'var(--cv-primary-500)' }"
                />
            </div>

            <header class="mb-5 pr-16">
                <h1
                    class="text-[28px] font-black uppercase leading-none tracking-[0.02em]"
                    :style="{ color: 'var(--cv-primary-900)' }"
                >
                    {{ identity.fullName }}
                </h1>
                <p
                    v-if="identity.jobTitle"
                    class="mt-2 text-[10px] font-bold uppercase tracking-[0.045em]"
                    :style="{ color: 'var(--cv-primary-600)' }"
                >
                    {{ identity.jobTitle }}
                </p>
                <p
                    v-if="identity.techLine"
                    class="mt-1 text-[10px] font-bold uppercase italic tracking-[0.045em]"
                    :style="{ color: 'var(--cv-primary-600)' }"
                >
                    {{ identity.techLine }}
                </p>
                <p v-if="identity.tagline" class="mt-2 max-w-[490px] text-[8.5px] leading-[1.55] text-slate-700">
                    {{ identity.tagline }}
                </p>
            </header>

            <section v-if="cv.content.profile.trim()" class="mb-4">
                <h2 class="cv-section-title">
                    <span class="cv-icon-box"><CvIcon name="user" class="size-4" /></span>
                    Profil
                </h2>
                <p class="mt-2 text-[8px] leading-[1.55] text-slate-700">{{ cv.content.profile }}</p>
            </section>

            <section
                v-for="section in mainSections"
                :key="section.id"
                class="mb-4 last:mb-0"
                :class="{ 'break-inside-avoid': section.type !== 'experiences' }"
            >
                <h2 class="cv-section-title">
                    <span class="cv-icon-box"><CvIcon :name="sectionIcon(section.type)" class="size-4" /></span>
                    {{ section.title }}
                </h2>
                <CvSectionBody :section="section" />
            </section>
        </section>
    </div>
</template>
