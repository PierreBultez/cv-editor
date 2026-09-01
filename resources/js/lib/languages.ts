/**
 * Niveaux de langue du Cadre européen commun de référence (CECR), échelle
 * globale, plus la langue maternelle qui n'en fait pas partie mais figure sur
 * tous les CV.
 *
 * Le libellé complet est stocké tel quel dans `mention` : c'est une chaîne
 * libre côté serveur, ce qui garde compatibles les CV rédigés avant
 * l'introduction de cette liste. Le nombre de pastilles est déduit du niveau
 * plutôt que saisi séparément — deux réglages pour la même information
 * finissaient par se contredire.
 *
 * @see https://www.coe.int/fr/web/common-european-framework-reference-languages/table-1-cefr-3.3-common-reference-levels-global-scale
 */

export interface LanguageLevel {
    /** Libellé stocké et affiché sur le CV. */
    label: string;
    /** Pastilles pleines, sur cinq. */
    dots: number;
    /** Groupe affiché dans le sélecteur. */
    group: string;
}

export const LANGUAGE_LEVELS: LanguageLevel[] = [
    { label: 'Langue maternelle', dots: 5, group: 'Langue maternelle' },
    { label: 'A1 — Introductif ou de découverte', dots: 1, group: 'Utilisateur élémentaire' },
    { label: 'A2 — Intermédiaire ou usuel', dots: 2, group: 'Utilisateur élémentaire' },
    { label: 'B1 — Niveau seuil', dots: 3, group: 'Utilisateur indépendant' },
    { label: 'B2 — Avancé ou indépendant', dots: 4, group: 'Utilisateur indépendant' },
    { label: 'C1 — Autonome', dots: 5, group: 'Utilisateur expérimenté' },
    { label: 'C2 — Maîtrise', dots: 5, group: 'Utilisateur expérimenté' },
];

export function dotsForLevel(label: string): number {
    return LANGUAGE_LEVELS.find((level) => level.label === label)?.dots ?? 0;
}

/**
 * Options du sélecteur. Une mention rédigée à la main avant l'existence de
 * cette liste y est réinjectée, sans quoi le champ apparaîtrait vide et la
 * valeur serait perdue au premier enregistrement.
 */
export function levelOptions(current: string): Array<{ label: string; value: string }> {
    const options = LANGUAGE_LEVELS.map((level) => ({ label: level.label, value: level.label }));

    if (current && !options.some((option) => option.value === current)) {
        options.unshift({ label: `${current} (saisie personnalisée)`, value: current });
    }

    return options;
}
