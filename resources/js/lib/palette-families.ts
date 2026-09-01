/**
 * Familles de couleurs de Tailwind CSS v4.
 *
 * Les valeurs sont celles du pas 600, extraites de `tailwindcss/colors` de la
 * version installee puis converties en hexadecimal — Tailwind v4 les publie en
 * OKLCH, alors que le selecteur natif et les pastilles attendent du hex.
 *
 * Elles servent de graine a `buildScale`, qui en derive les sept nuances du CV.
 * Le pas 600 est choisi parce que c'est celui que la derivation reproduit a
 * l'identique : la couleur choisie est donc exactement celle qui s'affiche sur
 * les titres.
 */

export interface ColorFamily {
    key: string;
    label: string;
    hex: string;
}

export const COLOR_FAMILIES: ColorFamily[] = [
    { key: 'red', label: 'Rouge', hex: '#e7000b' },
    { key: 'orange', label: 'Orange', hex: '#f54900' },
    { key: 'amber', label: 'Ambre', hex: '#e17100' },
    { key: 'yellow', label: 'Jaune', hex: '#d08700' },
    { key: 'lime', label: 'Citron', hex: '#5ea500' },
    { key: 'green', label: 'Vert', hex: '#00a63e' },
    { key: 'emerald', label: 'Émeraude', hex: '#009966' },
    { key: 'teal', label: 'Sarcelle', hex: '#009689' },
    { key: 'cyan', label: 'Cyan', hex: '#0092b8' },
    { key: 'sky', label: 'Ciel', hex: '#0084d1' },
    { key: 'blue', label: 'Bleu', hex: '#155dfc' },
    { key: 'indigo', label: 'Indigo', hex: '#4f39f6' },
    { key: 'violet', label: 'Violet', hex: '#7f22fe' },
    { key: 'purple', label: 'Pourpre', hex: '#9810fa' },
    { key: 'fuchsia', label: 'Fuchsia', hex: '#c800de' },
    { key: 'pink', label: 'Rose', hex: '#e60076' },
    { key: 'rose', label: 'Rose ancien', hex: '#ec003f' },
    { key: 'slate', label: 'Ardoise', hex: '#45556c' },
    { key: 'gray', label: 'Gris', hex: '#4a5565' },
    { key: 'zinc', label: 'Zinc', hex: '#52525c' },
    { key: 'neutral', label: 'Neutre', hex: '#525252' },
    { key: 'stone', label: 'Pierre', hex: '#57534d' },
];

/** Duos prêts à l'emploi, pour ne pas partir d'une grille de 22 cases. */
export const COLOR_PAIRS: Array<{ label: string; primary: string; accent: string }> = [
    { label: 'Bleu classique', primary: '#155dfc', accent: '#0084d1' },
    { label: 'Ardoise', primary: '#45556c', accent: '#009689' },
    { label: 'Bordeaux', primary: '#ec003f', accent: '#e7000b' },
    { label: 'Forêt', primary: '#00a63e', accent: '#5ea500' },
    { label: 'Prune', primary: '#7f22fe', accent: '#c800de' },
    { label: 'Cuivre', primary: '#f54900', accent: '#e17100' },
];
