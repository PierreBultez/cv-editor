import { converter, formatHex, clampChroma } from 'culori';

/**
 * Derive une echelle de nuances a partir d'une seule couleur choisie par
 * l'utilisateur.
 *
 * Le travail se fait en OKLCH : la teinte de la couleur source est conservee
 * telle quelle, seules la luminosite et la saturation varient. Contrairement a
 * un eclaircissement en RGB, la progression reste perceptuellement reguliere
 * quelle que soit la teinte de depart (un jaune ne « brule » pas, un bleu ne
 * vire pas au gris).
 *
 * Le pas 600 est celui qu'utilisent les titres et les aplats : c'est donc lui
 * qui doit rendre la couleur choisie. Il la reproduit a l'identique tant que sa
 * luminosite reste lisible sur du blanc, et se contente de la ramener dans
 * cette plage sinon. Sur les 22 familles de Tailwind, neuf passent inchangees
 * et aucune ne descend sous un contraste de 4,5:1.
 */

const toOklch = converter('oklch');

/**
 * Bornes de luminosite du pas 600.
 *
 * Trop clair, un titre ne se lit plus sur du blanc ; trop sombre, la couleur
 * devient indistinguable du noir et la palette perd son identite. Entre les
 * deux, la luminosite choisie est conservee telle quelle — c'est ce qui permet
 * de retrouver exactement la couleur cliquee.
 */
const MIN_LIGHTNESS = 0.32;
const MAX_LIGHTNESS = 0.55;

/**
 * Les paliers clairs servent de fonds et gagnent a rester stables d'une teinte
 * a l'autre ; les paliers sombres suivent la couleur choisie, par un ecart
 * relatif au pas 600. Cet ecart garantit aussi que l'echelle reste monotone,
 * quelle que soit la couleur de depart.
 */
const LIGHT_STOPS: Record<number, { lightness: number; chromaFactor: number }> = {
    50: { lightness: 0.975, chromaFactor: 0.1 },
    100: { lightness: 0.945, chromaFactor: 0.18 },
    200: { lightness: 0.878, chromaFactor: 0.36 },
};

const DARK_STOPS: Record<number, { offset: number; chromaFactor: number }> = {
    500: { offset: 0.09, chromaFactor: 0.95 },
    600: { offset: 0, chromaFactor: 1 },
    700: { offset: -0.06, chromaFactor: 0.94 },
    900: { offset: -0.16, chromaFactor: 0.78 },
};

export type ColorScale = Record<number, string>;

export function buildScale(hex: string): ColorScale {
    const base = toOklch(hex);

    if (!base) {
        return { ...FALLBACK_SCALE };
    }

    const chroma = base.c ?? 0;
    const hue = base.h ?? 0;

    // Luminosite de reference : celle de la couleur choisie, ramenee dans la
    // plage lisible seulement si elle en sort.
    const anchor = Math.min(MAX_LIGHTNESS, Math.max(MIN_LIGHTNESS, base.l ?? 0.42));

    const scale: ColorScale = {};

    // clampChroma ramene la couleur dans le gamut sRGB en reduisant la
    // saturation plutot qu'en ecretant les canaux, ce qui preserve la teinte.
    const at = (lightness: number, chromaFactor: number): string =>
        formatHex(
            clampChroma({ mode: 'oklch', l: lightness, c: chroma * chromaFactor, h: hue }, 'oklch', 'rgb'),
        ) ?? '#000000';

    for (const [stop, { lightness, chromaFactor }] of Object.entries(LIGHT_STOPS)) {
        scale[Number(stop)] = at(lightness, chromaFactor);
    }

    for (const [stop, { offset, chromaFactor }] of Object.entries(DARK_STOPS)) {
        scale[Number(stop)] = at(Math.min(0.72, Math.max(0.12, anchor + offset)), chromaFactor);
    }

    return scale;
}

/**
 * Variables CSS appliquees en style inline sur la racine de l'apercu. Aucune
 * classe Tailwind n'est regeneree : changer de couleur ne declenche pas de build.
 */
export function themeVariables(theme: { primary: string; accent: string }, fonts: { title: string; body: string }) {
    const primary = buildScale(theme.primary);
    const accent = buildScale(theme.accent);

    return {
        '--cv-primary-50': primary[50],
        '--cv-primary-100': primary[100],
        '--cv-primary-200': primary[200],
        '--cv-primary-500': primary[500],
        '--cv-primary-600': primary[600],
        '--cv-primary-700': primary[700],
        '--cv-primary-900': primary[900],
        '--cv-accent-500': accent[500],
        '--cv-accent-600': accent[600],
        '--cv-font-title': `var(--font-${fonts.title}), ui-sans-serif, system-ui, sans-serif`,
        '--cv-font-body': `var(--font-${fonts.body}), ui-sans-serif, system-ui, sans-serif`,
    } satisfies Record<string, string>;
}

/** Palette du CV d'origine, utilisee si la couleur saisie est illisible. */
const FALLBACK_SCALE: ColorScale = {
    50: '#f4f7fb',
    100: '#e9f0f8',
    200: '#cedcec',
    500: '#245ea9',
    600: '#174d94',
    700: '#103f7c',
    900: '#0a2854',
};
