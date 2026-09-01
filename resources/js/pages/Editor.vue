<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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

// Un lien de modification a deja ete absorbe au demarrage de l'application
// (voir absorbRecoveryLink) : le jeton est donc dans le trousseau local, et
// `initialise` l'y retrouve comme pour n'importe quelle visite ulterieure.
const store = useCvStore();
store.initialise(props.cv, props.issuedToken);

const readonly = computed(() => !store.canEdit);
const publicUrl = computed(() => `${window.location.origin}/cv/${props.cv.public_id}`);

/**
 * Sans comptes, ce lien est le seul moyen de revenir modifier ce CV : il n'est
 * rattache a aucune adresse e-mail et le serveur n'en conserve que le hachage.
 */
const editUrl = computed(() =>
    store.token ? `${window.location.origin}/cv/${props.cv.public_id}/edit#t=${store.token}` : null,
);

const tab = ref('contenu');
const mobileTab = ref('editer');
const confirmDelete = ref(false);
const copied = ref(false);
const copiedEdit = ref(false);
/** Bandeau affiche au retour immediat de la creation, tant qu'il n'est pas ecarte. */
const showKeepLink = ref(props.issuedToken !== null);
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

/**
 * Les onglets restent accessibles quel que soit le defilement : sans cela,
 * arrive en bas d'une longue liste d'experiences, on ne pouvait plus atteindre
 * « Apparence » ni « Reglages » sans remonter toute la page.
 *
 * La hauteur de la barre d'outils est mesuree plutot que figee : elle s'enroule
 * sur les ecrans etroits, et un decalage constant collerait les onglets sous
 * une barre plus haute qu'eux.
 */
const toolbar = ref<HTMLElement | null>(null);
const toolbarHeight = ref(0);

let toolbarObserver: ResizeObserver | null = null;

/**
 * Sous 640 px, les trois onglets ne tiennent avec leur icone qu'au prix d'un
 * libelle coupe en « Apparen... ». Le libelle porte l'information, l'icone ne
 * fait que l'illustrer : c'est donc elle qui saute.
 *
 * Une requete media plutot qu'une classe utilitaire, parce que le choix porte
 * sur des *donnees* passees a `UTabs`, pas sur du style.
 */
const compact = ref(false);
let compactQuery: MediaQueryList | null = null;

function syncCompact(event: MediaQueryListEvent | MediaQueryList): void {
    compact.value = event.matches;
}

const formTabs = computed(() => [
    { label: 'Contenu', value: 'contenu', icon: compact.value ? undefined : 'i-lucide-list' },
    { label: 'Apparence', value: 'apparence', icon: compact.value ? undefined : 'i-lucide-palette' },
    { label: 'Réglages', value: 'reglages', icon: compact.value ? undefined : 'i-lucide-settings' },
]);

onMounted(() => {
    toolbarObserver = new ResizeObserver(() => {
        toolbarHeight.value = toolbar.value?.offsetHeight ?? 0;
    });

    if (toolbar.value) {
        toolbarObserver.observe(toolbar.value);
        toolbarHeight.value = toolbar.value.offsetHeight;
    }

    compactQuery = window.matchMedia('(max-width: 639px)');
    syncCompact(compactQuery);
    compactQuery.addEventListener('change', syncCompact);
});

onBeforeUnmount(() => {
    toolbarObserver?.disconnect();
    toolbarObserver = null;

    compactQuery?.removeEventListener('change', syncCompact);
    compactQuery = null;
});

function printCv(): void {
    window.print();
}

async function copyLink(): Promise<void> {
    await navigator.clipboard.writeText(publicUrl.value);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
}

async function copyEditLink(): Promise<void> {
    if (!editUrl.value) {
        return;
    }

    await navigator.clipboard.writeText(editUrl.value);
    copiedEdit.value = true;
    setTimeout(() => (copiedEdit.value = false), 2000);
}

/**
 * Raccourci Internet : double-cliquer le fichier rouvre l'editeur avec le
 * jeton. C'est le filet pour qui vide les donnees de son navigateur ou change
 * de machine.
 */
function downloadEditLink(): void {
    if (!editUrl.value) {
        return;
    }

    const nom = store.doc?.content.identity.fullName.trim() || 'CV';
    const blob = new Blob([`[InternetShortcut]\r\nURL=${editUrl.value}\r\n`], {
        type: 'application/internet-shortcut',
    });

    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = `Modifier mon CV — ${nom}.url`;
    a.click();
    URL.revokeObjectURL(a.href);
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

    <div v-if="store.doc" class="print-canvas min-h-screen">
        <!-- ================= Barre d'outils ================= -->
        <header
            ref="toolbar"
            class="no-print sticky top-0 z-20 border-b border-default bg-default/95 backdrop-blur"
        >
            <!--
                Une seule rangee, y compris a 320 px : les libelles s'effacent
                au profit des icones plutot que de faire passer la barre sur
                deux lignes, dont la hauteur mangerait l'ecran d'un telephone.
            -->
            <div class="flex items-center gap-1 px-3 py-2 sm:gap-2 sm:px-4 sm:py-3">
                <UButton
                    to="/"
                    variant="ghost"
                    color="neutral"
                    icon="i-lucide-arrow-left"
                    size="sm"
                    aria-label="Accueil"
                >
                    <span class="hidden sm:inline">Accueil</span>
                </UButton>

                <SaveIndicator :state="store.saveState" :error="store.lastError" />

                <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-2">
                    <UButton
                        size="sm"
                        variant="subtle"
                        color="neutral"
                        :icon="copied ? 'i-lucide-check' : 'i-lucide-link'"
                        :aria-label="copied ? 'Lien public copié' : 'Copier le lien public'"
                        @click="copyLink"
                    >
                        <span class="hidden sm:inline">{{ copied ? 'Copié' : 'Lien public' }}</span>
                    </UButton>
                    <UButton size="sm" icon="i-lucide-printer" aria-label="Imprimer ou exporter en PDF" @click="printCv">
                        <span class="hidden sm:inline">Imprimer / PDF</span>
                        <span class="sm:hidden">PDF</span>
                    </UButton>
                </div>
            </div>
        </header>

        <!--
            Les bandeaux vivent dans un conteneur a marge interne : en marge
            *externe*, la largeur pleine de `UAlert` s'ajoutait aux 2 x 16 px et
            debordait la fenetre, a toutes les tailles d'ecran.
        -->
        <div v-if="readonly || (showKeepLink && editUrl)" class="no-print space-y-4 px-4 pt-4">
            <UAlert
                v-if="readonly"
                color="warning"
                variant="subtle"
                icon="i-lucide-lock"
                title="Lecture seule"
                description="Ce navigateur ne détient pas le lien de modification de ce CV. Vous pouvez le consulter, mais pas le modifier. Si vous aviez conservé votre lien de modification, ouvrez-le : il rétablit l'accès."
            />

            <!--
                Sans comptes, ce lien est le seul chemin de retour vers ce CV. Le
                dire au moment ou il est encore temps evite la seule perte
                irreversible que le modele autorise.
            -->
            <UAlert
                v-if="showKeepLink && editUrl"
                color="primary"
                variant="subtle"
                icon="i-lucide-key-round"
                title="Conservez votre lien de modification"
                :close="true"
                @update:open="showKeepLink = false"
            >
                <template #description>
                    <p class="mb-3">
                        Il n'y a ni compte ni mot de passe : ce lien secret est le seul moyen de revenir modifier ce
                        CV. Il est enregistré dans ce navigateur, mais vider les données du site ou changer d'appareil
                        vous en ferait perdre l'accès — définitivement.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <UButton
                            size="sm"
                            :icon="copiedEdit ? 'i-lucide-check' : 'i-lucide-copy'"
                            @click="copyEditLink"
                        >
                            {{ copiedEdit ? 'Copié' : 'Copier le lien' }}
                        </UButton>
                        <UButton size="sm" variant="subtle" icon="i-lucide-download" @click="downloadEditLink">
                            Enregistrer le raccourci
                        </UButton>
                    </div>
                </template>
            </UAlert>
        </div>

        <!--
            ================= Deux volets =================

            `minmax(0, ...)` sur la colonne unique du telephone n'est pas
            cosmetique : un element de grille vaut `min-width: auto`, donc au
            moins la largeur minimale de son contenu. La feuille A4 mesurant
            210 mm — 794 px — la colonne se calait sur 794 px et *toute* la page
            defilait horizontalement, formulaire compris.
        -->
        <div class="print-canvas grid grid-cols-[minmax(0,1fr)] gap-6 p-4 pb-28 lg:grid-cols-[minmax(0,420px)_minmax(0,1fr)] lg:pb-4">
            <!-- Formulaire -->
            <div class="no-print min-w-0" :class="{ 'hidden lg:block': mobileTab === 'apercu' }">
                <div
                    class="sticky z-10 -mx-1 bg-default/95 px-1 py-2 backdrop-blur"
                    :style="{ top: `${toolbarHeight}px` }"
                >
                    <!--
                        `size="sm"` n'est pas cosmetique : a la taille par
                        defaut, « Apparence » et « Reglages » se faisaient
                        tronquer sur un ecran de 320 px, icone comprise.
                    -->
                    <UTabs v-model="tab" size="sm" :items="formTabs" />
                </div>

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

                                <UFormField label="Lien public" description="À transmettre à un recruteur.">
                                    <UInput :model-value="publicUrl" readonly class="font-mono text-xs" />
                                </UFormField>
                            </div>
                        </UCard>

                        <UCard v-if="editUrl">
                            <template #header>
                                <span class="font-semibold">Lien de modification</span>
                            </template>

                            <p class="mb-3 text-sm text-muted">
                                Secret. Quiconque l'ouvre peut modifier ce CV. Gardez-le pour vous, mais gardez-le :
                                c'est le seul moyen de revenir ici depuis un autre navigateur.
                            </p>

                            <UInput :model-value="editUrl" readonly class="mb-3 font-mono text-xs" />

                            <div class="flex flex-wrap gap-2">
                                <UButton
                                    size="sm"
                                    :icon="copiedEdit ? 'i-lucide-check' : 'i-lucide-copy'"
                                    @click="copyEditLink"
                                >
                                    {{ copiedEdit ? 'Copié' : 'Copier' }}
                                </UButton>
                                <UButton size="sm" variant="subtle" icon="i-lucide-download" @click="downloadEditLink">
                                    Enregistrer le raccourci
                                </UButton>
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

            <!--
                Aperçu. Sur telephone les deux volets s'excluent : empiles, il
                fallait derouler tout le formulaire pour apercevoir la feuille.
                Le `hidden` est repris a l'impression par `.print-target`, sans
                quoi imprimer depuis un mobile sortirait une page blanche.
            -->
            <div
                class="print-target min-w-0 lg:sticky lg:top-20 lg:self-start"
                :class="{ 'hidden lg:block': mobileTab === 'editer' }"
            >
                <A4Frame show-guides controls>
                    <CvPreview :cv="store.doc" />
                </A4Frame>
            </div>
        </div>

        <!--
            Bascule mobile. `content: false` empeche `UTabs` de monter le
            panneau vide qui accompagne d'ordinaire les onglets : ici les deux
            volets sont ailleurs dans la page, l'onglet ne fait que les
            designer. Le retrait bas suit l'encoche des telephones recents.

            Pas de `z-index` : le voile des modales de Nuxt UI vaut `auto`, et
            la moindre cote positive faisait flotter cette pastille en pleine
            lumiere par-dessus le recadreur de photo. En `fixed` sans cote, elle
            passe au-dessus du contenu et sous le voile.
        -->
        <div
            class="no-print fixed left-1/2 -translate-x-1/2 lg:hidden"
            :style="{ bottom: 'calc(1rem + env(safe-area-inset-bottom, 0px))' }"
        >
            <UTabs
                v-model="mobileTab"
                size="sm"
                :content="false"
                :items="[
                    { label: 'Éditer', value: 'editer', icon: 'i-lucide-pencil' },
                    { label: 'Aperçu', value: 'apercu', icon: 'i-lucide-eye' },
                ]"
                class="rounded-full bg-default shadow-lg ring ring-default"
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
