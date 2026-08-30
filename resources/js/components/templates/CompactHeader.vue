<script setup lang="ts">
import { computed } from 'vue';
import type { CvRecord } from '@/lib/types';
import { CONTACT_ICONS, sectionIcon } from '@/lib/sections';
import CvIcon from '@/components/cv/CvIcon.vue';
import CvPhotoFrame from '@/components/cv/CvPhotoFrame.vue';
import CvSectionBody from '@/components/cv/CvSectionBody.vue';

/**
 * Mise en page « Compacte » : bandeau d'identite pleine largeur, puis deux
 * colonnes sans barre laterale coloree.
 *
 * Le champ `column` des sections est ici ignore — c'est le rapport de place
 * qui decide : les sections courtes (compétences, langues, outils, intérêts)
 * vont a droite, les sections longues a gauche. Un CV bascule donc d'une mise
 * en page a l'autre sans perte ni ressaisie.
 */
const props = defineProps<{ cv: CvRecord }>();

const NARROW = ['skills', 'languages', 'tools', 'interests'];

const filled = computed(() => props.cv.content.sections.filter((s) => s.enabled && s.items.length > 0));
const asideSections = computed(() => filled.value.filter((s) => NARROW.includes(s.type)));
const mainSections = computed(() => filled.value.filter((s) => !NARROW.includes(s.type)));
const contacts = computed(() => props.cv.content.contact.filter((entry) => entry.value.trim() !== ''));
const identity = computed(() => props.cv.content.identity);
</script>

<template>
    <div class="cv-page flex flex-col">
        <!-- ================= Bandeau ================= -->
        <header
            class="flex items-center gap-[6mm] px-[12mm] py-[9mm]"
            :style="{ background: 'var(--cv-primary-50)', borderBottom: '2px solid var(--cv-primary-600)' }"
        >
            <CvPhotoFrame v-if="cv.photo" :photo="cv.photo" :alt="identity.fullName" size="5rem" />

            <div class="min-w-0 flex-1">
                <h1
                    class="text-[26px] font-black uppercase leading-none tracking-[0.02em]"
                    :style="{ color: 'var(--cv-primary-900)' }"
                >
                    {{ identity.fullName }}
                </h1>
                <p
                    v-if="identity.jobTitle"
                    class="mt-1.5 text-[9.5px] font-bold uppercase tracking-[0.045em]"
                    :style="{ color: 'var(--cv-primary-600)' }"
                >
                    {{ identity.jobTitle }}
                </p>
                <p v-if="identity.techLine" class="mt-1 text-[8px] leading-[1.4] text-slate-600">
                    {{ identity.techLine }}
                </p>

                <div v-if="contacts.length" class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-[7.8px] text-slate-700">
                    <span v-for="(entry, index) in contacts" :key="index" class="flex items-center gap-1.5">
                        <CvIcon
                            :name="CONTACT_ICONS[entry.type] ?? 'user'"
                            class="size-3 shrink-0"
                            :style="{ color: 'var(--cv-primary-600)' }"
                        />
                        {{ entry.value }}
                    </span>
                </div>
            </div>
        </header>

        <!-- ================= Corps ================= -->
        <div class="grid flex-1 grid-cols-[1fr_58mm] gap-[8mm] px-[12mm] py-[8mm]">
            <div>
                <section v-if="identity.tagline || cv.content.profile.trim()" class="mb-4">
                    <h2 class="cv-section-title">
                        <span class="cv-icon-box"><CvIcon name="user" class="size-4" /></span>
                        Profil
                    </h2>
                    <p v-if="identity.tagline" class="mt-2 text-[8.2px] font-medium leading-[1.5] text-slate-800">
                        {{ identity.tagline }}
                    </p>
                    <p v-if="cv.content.profile.trim()" class="mt-1.5 text-[8px] leading-[1.55] text-slate-700">
                        {{ cv.content.profile }}
                    </p>
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
            </div>

            <aside>
                <section v-for="section in asideSections" :key="section.id" class="mb-5 last:mb-0 break-inside-avoid">
                    <h2 class="cv-sidebar-title">
                        <span class="cv-icon-box"><CvIcon :name="sectionIcon(section.type)" class="size-4" /></span>
                        {{ section.title }}
                    </h2>
                    <CvSectionBody :section="section" />
                </section>
            </aside>
        </div>
    </div>
</template>
