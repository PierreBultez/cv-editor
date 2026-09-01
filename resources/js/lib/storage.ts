/**
 * Trousseau local des CV du visiteur.
 *
 * Il n'y a pas de comptes : le jeton d'edition remis a la creation d'un CV
 * n'existe qu'ici. S'il est perdu (navigation privee, donnees de site effacees,
 * autre navigateur), le CV reste consultable via son URL publique mais devient
 * definitivement non modifiable. C'est le compromis assume de l'anonymat.
 *
 * Tous les acces sont proteges : le stockage leve dans certains contextes
 * (navigation privee stricte, navigateur configure pour bloquer les donnees).
 */

const KEY = 'cv-studio:mes-cv';

export interface StoredCv {
    publicId: string;
    token: string;
    title: string;
    updatedAt: string;
}

function read(): StoredCv[] {
    try {
        const raw = window.localStorage.getItem(KEY);
        const parsed: unknown = raw ? JSON.parse(raw) : [];

        return Array.isArray(parsed) ? (parsed as StoredCv[]).filter((entry) => entry?.publicId && entry?.token) : [];
    } catch {
        return [];
    }
}

function write(entries: StoredCv[]): void {
    try {
        window.localStorage.setItem(KEY, JSON.stringify(entries));
    } catch {
        // Quota atteint ou stockage indisponible : l'edition en cours continue
        // de fonctionner, seul le rappel du CV au prochain passage est perdu.
    }
}

/**
 * Absorbe un lien de modification de la forme `/cv/{id}/edit#t={jeton}`.
 *
 * A appeler avant l'initialisation d'Inertia : le routeur reecrit l'URL a son
 * demarrage et remettrait un fragment efface plus tard. Le jeton est range dans
 * le trousseau, puis retire de la barre d'adresse pour ne pas trainer dans
 * l'historique du navigateur ni s'afficher par-dessus l'epaule.
 *
 * Le fragment n'etant jamais transmis au serveur, le jeton n'apparait ni dans
 * les journaux d'acces ni dans un en-tete Referer.
 */
export function absorbRecoveryLink(): void {
    const token = window.location.hash.match(/(?:^|[#&])t=([A-Za-z0-9]+)/)?.[1];
    const publicId = window.location.pathname.match(/^\/cv\/([A-Za-z0-9]+)\/edit$/)?.[1];

    if (!token || !publicId) {
        return;
    }

    // Le libelle sera corrige des que l'editeur connaitra le nom du CV.
    rememberCv(publicId, token, 'CV');

    window.history.replaceState(
        window.history.state,
        '',
        window.location.pathname + window.location.search,
    );
}

export function listCvs(): StoredCv[] {
    return read().sort((a, b) => (b.updatedAt ?? '').localeCompare(a.updatedAt ?? ''));
}

export function tokenFor(publicId: string): string | null {
    return read().find((entry) => entry.publicId === publicId)?.token ?? null;
}

export function rememberCv(publicId: string, token: string, title: string): void {
    const entries = read().filter((entry) => entry.publicId !== publicId);

    entries.push({ publicId, token, title, updatedAt: new Date().toISOString() });

    write(entries);
}

/** Met a jour le libelle et la date sans toucher au jeton deja stocke. */
export function touchCv(publicId: string, title: string): void {
    const entries = read();
    const entry = entries.find((item) => item.publicId === publicId);

    if (!entry) {
        return;
    }

    entry.title = title;
    entry.updatedAt = new Date().toISOString();

    write(entries);
}

export function forgetCv(publicId: string): void {
    write(read().filter((entry) => entry.publicId !== publicId));
}
