import { computed, ref, watch } from 'vue';
import { defineStore } from 'pinia';
import type {
    CvPhoto,
    CvRecord,
    CvSection,
    SectionItem,
    SectionType,
    SaveState,
} from '@/lib/types';
import { rememberCv, tokenFor, touchCv } from '@/lib/storage';

/** Delai d'inactivite avant l'envoi au serveur. */
const AUTOSAVE_DELAY = 700;

export const useCvStore = defineStore('cv', () => {
    const doc = ref<CvRecord | null>(null);
    const token = ref<string | null>(null);
    const saveState = ref<SaveState>('idle');
    const lastError = ref<string | null>(null);

    let ready = false;
    let timer: ReturnType<typeof setTimeout> | null = null;

    /** Sans jeton, l'editeur s'affiche mais n'envoie rien : mode consultation. */
    const canEdit = computed(() => token.value !== null);

    const sidebarSections = computed(() => visibleSections('sidebar'));
    const mainSections = computed(() => visibleSections('main'));

    function visibleSections(column: 'sidebar' | 'main'): CvSection[] {
        return (doc.value?.content.sections ?? []).filter(
            (section) => section.column === column && section.enabled && section.items.length > 0,
        );
    }

    /**
     * `issuedToken` n'est present qu'au retour immediat de la creation. Les
     * visites suivantes retrouvent le jeton dans le localStorage.
     */
    function initialise(record: CvRecord, issuedToken?: string | null): void {
        ready = false;
        doc.value = record;

        if (issuedToken) {
            token.value = issuedToken;
            rememberCv(record.public_id, issuedToken, documentTitle(record));
        } else {
            token.value = tokenFor(record.public_id);
        }

        saveState.value = canEdit.value ? 'idle' : 'readonly';

        // Le drapeau est leve apres le cycle courant pour que l'affectation
        // ci-dessus ne declenche pas une sauvegarde des l'ouverture.
        queueMicrotask(() => {
            ready = true;
        });
    }

    function documentTitle(record: CvRecord): string {
        return record.content.identity.fullName.trim() || 'CV sans titre';
    }

    watch(
        doc,
        () => {
            if (!ready || !canEdit.value) {
                return;
            }

            saveState.value = 'pending';

            if (timer) {
                clearTimeout(timer);
            }

            timer = setTimeout(() => void save(), AUTOSAVE_DELAY);
        },
        { deep: true },
    );

    /**
     * Requete `fetch` et non visite Inertia : une sauvegarde automatique ne doit
     * ni rejouer le rendu de la page ni ecrire dans l'historique du navigateur.
     */
    async function save(): Promise<void> {
        if (!doc.value || !token.value) {
            return;
        }

        saveState.value = 'saving';
        lastError.value = null;

        const payload = {
            template: doc.value.template,
            theme: doc.value.theme,
            fonts: doc.value.fonts,
            content: doc.value.content,
            is_public: doc.value.is_public,
            allow_indexing: doc.value.allow_indexing,
        };

        try {
            const response = await fetch(`/cv/${doc.value.public_id}`, {
                method: 'PATCH',
                headers: jsonHeaders(token.value),
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error(await describeFailure(response));
            }

            saveState.value = 'saved';
            touchCv(doc.value.public_id, documentTitle(doc.value));
        } catch (error) {
            saveState.value = 'error';
            lastError.value = error instanceof Error ? error.message : 'Erreur inconnue';
        }
    }

    async function uploadPhoto(file: Blob): Promise<void> {
        if (!doc.value || !token.value) {
            return;
        }

        const body = new FormData();
        body.append('photo', file, 'photo.jpg');

        const response = await fetch(`/cv/${doc.value.public_id}/photo`, {
            method: 'POST',
            headers: { 'X-Cv-Token': token.value, 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' },
            body,
        });

        if (!response.ok) {
            throw new Error(await describeFailure(response));
        }

        const data = (await response.json()) as { photo: CvPhoto };
        doc.value.photo = data.photo;
    }

    async function removePhoto(): Promise<void> {
        if (!doc.value || !token.value) {
            return;
        }

        await fetch(`/cv/${doc.value.public_id}/photo`, {
            method: 'DELETE',
            headers: jsonHeaders(token.value),
        });

        doc.value.photo = null;
    }

    function addItem(section: CvSection): void {
        section.items.push(blankItem(section.type));
    }

    function removeItem(section: CvSection, index: number): void {
        section.items.splice(index, 1);
    }

    return {
        doc,
        token,
        canEdit,
        saveState,
        lastError,
        sidebarSections,
        mainSections,
        initialise,
        save,
        uploadPhoto,
        removePhoto,
        addItem,
        removeItem,
    };
});

/** Item vierge correspondant a la forme attendue par le validateur serveur. */
export function blankItem(type: SectionType): SectionItem {
    switch (type) {
        case 'experiences':
            return { period: '', role: '', company: '', location: '', bullets: [''] };
        case 'education':
        case 'certifications':
            return { period: '', degree: '', school: '', location: '', detail: '' };
        case 'skills':
            return { label: '', level: 70 };
        case 'languages':
            return { label: '', mention: '', level: 3 };
        default:
            return '';
    }
}

function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

function jsonHeaders(editToken: string): Record<string, string> {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Cv-Token': editToken,
        'X-CSRF-TOKEN': csrfToken(),
    };
}

/** Remonte les messages de validation du serveur plutot qu'un code HTTP nu. */
async function describeFailure(response: Response): Promise<string> {
    if (response.status === 403) {
        return "Jeton d'édition invalide : ce CV ne peut pas être modifié depuis ce navigateur.";
    }

    if (response.status === 429) {
        return 'Trop de requêtes, réessayez dans un instant.';
    }

    const text = await response.text().catch(() => '');
    const data = parseJsonBody(text);
    const first = data?.errors ? Object.values(data.errors)[0]?.[0] : undefined;

    return first ?? data?.message ?? `Erreur ${response.status}`;
}

/**
 * PHP peut prefixer sa reponse d'un avertissement HTML — c'est le cas quand un
 * upload echoue faute de repertoire temporaire. `response.json()` leve alors, et
 * l'on perdait le message du serveur au profit d'un code HTTP nu, illisible pour
 * l'utilisateur comme pour le diagnostic. On repart donc de la premiere accolade.
 */
function parseJsonBody(text: string): { message?: string; errors?: Record<string, string[]> } | null {
    for (const candidate of [text, text.slice(text.indexOf('{'))]) {
        try {
            return JSON.parse(candidate);
        } catch {
            // On tente la variante suivante.
        }
    }

    return null;
}
