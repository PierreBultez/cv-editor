<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import type { CvRecord } from '@/lib/types';
import { useCvStore } from '@/stores/useCvStore';
import A4Frame from '@/components/preview/A4Frame.vue';
import CvPreview from '@/components/preview/CvPreview.vue';
import IdentityForm from '@/components/editor/IdentityForm.vue';
import SectionsEditor from '@/components/editor/SectionsEditor.vue';
import ThemePicker from '@/components/editor/ThemePicker.vue';
import FontPicker from '@/components/editor/FontPicker.vue';
import PhotoUploader from '@/components/editor/PhotoUploader.vue';
import SaveIndicator from '@/components/editor/SaveIndicator.vue';

const props = defineProps<{
    cv: CvRecord;
    issuedToken: string | null;
    supportsAvif: boolean;
}>();

const store = useCvStore();
store.initialise(props.cv, props.issuedToken);

const readonly = computed(() => !store.canEdit);
const publicUrl = computed(() => `${window.location.origin}/cv/${props.cv.public_id}`);

const tab = ref('contenu');
const mobileTab = ref('editer');
const confirmDelete = ref(false);
const copied = ref(false);
const photoError = ref<string | null>(null);

const TEMPLATE_OPTIONS = [
    {
        label: 'Classique',
        value: 'classic',
        description: 'Colonne latérale colorée à gauche, contenu principal à droite.',
    },
    {
        label: 'Compacte',
        value: 'compact',
        description: "Bandeau d'identité pleine largeur, puis deux colonnes.",
    },
];

function printCv(): void {
    window.print();
}

async function copyLink(): Promise<void> {
    await navigator.clipboard.writeText(publicUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}

async function onPhotoUpload(blob: Blob): Promise<void> {
    photoError.value = null;

    try {
        await store.uploadPhoto(blob);
    } catch (error) {
        photoError.value = error instanceof Error ? error.message : 'Erreur inconnue';
    }
}

function destroy(): void {
    router.delete(`/cv/${props.cv.public_id}`, {
        headers: { 'X-Cv-Token': store.token ?? '' },
    });
}
</script>

<template>
    <Head title="Éditeur" />

    <div v-if="store.doc" class="min-h-screen">
        <!-- ================= Barre d'outils ================= -->
        <header class="no-print sticky top-0 z-20 border-b border-default bg-default/95 backdrop-blur">
            <div class="flex flex-wrap items-center gap-2 px-4 py-3">
                <UButton to="/" variant="ghost" color="neutral" icon="i-lucide-arrow-left" size="sm">
                    Accueil
                </UButton>

                <SaveIndicator :state="store.saveState" :error="store.lastError" />

                <div class="ml-auto flex flex-wrap items-center gap-2">
                    <UButton
                        size="sm"
                        variant="subtle"
                        color="neutral"
                        :icon="copied ? 'i-lucide-check' : 'i-lucide-link'"
                        @click="copyLink"
                    >
                        {{ copied ? 'Copié' : 'Lien public' }}
                    </UButton>
                    <UButton size="sm" icon="i-lucide-printer" @click="printCv">Imprimer / PDF</UButton>
                </div>
            </div>
        </header>

        <UAlert
            v-if="readonly"
            class="no-print m-4"
            color="warning"
            variant="subtle"
            icon="i-lucide-lock"
            title="Lecture seule"
            description="Le jeton d'édition de ce CV n'a pas été trouvé dans ce navigateur. Vous pouvez consulter le CV, mais pas le modifier."
        />

        <!-- ================= Deux volets ================= -->
        <div class="print-canvas grid gap-6 p-4 lg:grid-cols-[minmax(0,420px)_minmax(0,1fr)]">
            <!-- Formulaire -->
            <div class="no-print" :class="{ 'hidden lg:block': mobileTab === 'apercu' }">
                <UTabs
                    v-model="tab"
                    :items="[
                        { label: 'Contenu', value: 'contenu', icon: 'i-lucide-list' },
                        { label: 'Apparence', value: 'apparence', icon: 'i-lucide-palette' },
                        { label: 'Réglages', value: 'reglages', icon: 'i-lucide-settings' },
                    ]"
                />

                <div class="mt-4">
                    <div v-if="tab === 'contenu'" class="space-y-6">
                        <UCard>
                            <template #header><span class="font-semibold">Identité</span></template>
                            <IdentityForm :content="store.doc.content" :disabled="readonly" />
                        </UCard>

                        <UCard>
                            <template #header><span class="font-semibold">Sections</span></template>
                            <SectionsEditor
                                :content="store.doc.content"
                                :disabled="readonly"
                                :show-columns="store.doc.template === 'classic'"
                            />
                        </UCard>
                    </div>

                    <div v-else-if="tab === 'apparence'" class="space-y-6">
                        <UCard>
                            <template #header><span class="font-semibold">Mise en page</span></template>
                            <URadioGroup
                                v-model="store.doc.template"
                                :items="TEMPLATE_OPTIONS"
                                :disabled="readonly"
                                variant="card"
                            />
                        </UCard>

                        <UCard>
                            <template #header><span class="font-semibold">Photo</span></template>
                            <PhotoUploader
                                :photo="store.doc.photo"
                                :disabled="readonly"
                                :supports-avif="supportsAvif"
                                @upload="onPhotoUpload"
                                @remove="store.removePhoto"
                            />
                            <UAlert
                                v-if="photoError"
                                class="mt-3"
                                color="error"
                                variant="subtle"
                                :description="photoError"
                            />
                        </UCard>

                        <UCard>
                            <template #header><span class="font-semibold">Couleurs</span></template>
                            <ThemePicker v-model="store.doc.theme" />
                        </UCard>

                        <UCard>
                            <template #header><span class="font-semibold">Polices</span></template>
                            <FontPicker v-model="store.doc.fonts" />
                        </UCard>
                    </div>

                    <div v-else class="space-y-6">
                        <UCard>
                            <template #header><span class="font-semibold">Partage</span></template>
                            <div class="space-y-4">
                                <UFormField
                                    label="Page publique"
                                    description="Rend le CV consultable par toute personne disposant du lien."
                                >
                                    <USwitch v-model="store.doc.is_public" :disabled="readonly" />
                                </UFormField>

                                <UFormField
                                    label="Autoriser l'indexation"
                                    description="Par défaut, les moteurs de recherche sont priés de ne pas référencer la page."
                                >
                                    <USwitch
                                        v-model="store.doc.allow_indexing"
                                        :disabled="readonly || !store.doc.is_public"
                                    />
                                </UFormField>

                                <UInput :model-value="publicUrl" readonly class="font-mono text-xs" />
                            </div>
                        </UCard>

                        <UCard>
                            <template #header><span class="font-semibold text-error">Suppression</span></template>
                            <p class="mb-4 text-sm text-muted">
                                La suppression est immédiate et définitive : le CV, sa photo et son lien public
                                disparaissent. Les CV inactifs pendant 12 mois sont supprimés automatiquement.
                            </p>
                            <UButton
                                color="error"
                                variant="subtle"
                                icon="i-lucide-trash-2"
                                :disabled="readonly"
                                @click="confirmDelete = true"
                            >
                                Supprimer ce CV
                            </UButton>
                        </UCard>
                    </div>
                </div>
            </div>

            <!-- Aperçu -->
            <div class="print-target lg:sticky lg:top-20 lg:self-start">
                <A4Frame show-guides>
                    <CvPreview :cv="store.doc" />
                </A4Frame>
            </div>
        </div>

        <!-- Bascule mobile -->
        <div class="no-print fixed bottom-4 left-1/2 z-30 -translate-x-1/2 lg:hidden">
            <UTabs
                v-model="mobileTab"
                size="sm"
                :items="[
                    { label: 'Éditer', value: 'editer', icon: 'i-lucide-pencil' },
                    { label: 'Aperçu', value: 'apercu', icon: 'i-lucide-eye' },
                ]"
                class="rounded-full bg-default shadow-lg"
            />
        </div>

        <UModal v-model:open="confirmDelete" title="Supprimer définitivement ce CV ?">
            <template #body>
                <p class="text-sm text-muted">
                    Cette action est irréversible. Le lien public cessera de fonctionner et la photo sera effacée
                    du serveur.
                </p>
            </template>
            <template #footer>
                <div class="flex w-full justify-end gap-2">
                    <UButton variant="ghost" color="neutral" @click="confirmDelete = false">Annuler</UButton>
                    <UButton color="error" @click="destroy">Supprimer</UButton>
                </div>
            </template>
        </UModal>
    </div>
</template>
