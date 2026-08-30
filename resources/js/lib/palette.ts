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
 * Le pas 600 reproduit la couleur saisie : c'est celui qu'utilisent les titres
 * et les aplats du template, donc celui que l'utilisateur croit choisir.
 */

const toOklch = converter('oklch');

/** Luminosite absolue et facteur applique a la saturation de la couleur source. */
const STOPS: Record<number, { lightness: number; chromaFactor: number }> = {
    50: { lightness: 0.975, chromaFactor: 0.1 },
    100: { lightness: 0.945, chromaFactor: 0.18 },
    200: { lightness: 0.878, chromaFactor: 0.36 },
    500: { lightness: 0.508, chromaFactor: 0.95 },
    600: { lightness: 0.42, chromaFactor: 1 },
    700: { lightness: 0.362, chromaFactor: 0.94 },
    900: { lightness: 0.262, chromaFactor: 0.78 },
};

export type ColorScale = Record<number, string>;

export function buildScale(hex: string): ColorScale {
    const base = toOklch(hex);

    if (!base) {
        return { ...FALLBACK_SCALE };
    }

    const chroma = base.c ?? 0;
    const hue = base.h ?? 0;
    const scale: ColorScale = {};

    for (const [stop, { lightness, chromaFactor }] of Object.entries(STOPS)) {
        // clampChroma ramene la couleur dans le gamut sRGB en reduisant la
        // saturation plutot qu'en ecretant les canaux, ce qui preserve la teinte.
        const color = clampChroma(
            { mode: 'oklch', l: lightness, c: chroma * chromaFactor, h: hue },
            'oklch',
            'rgb',
        );

        scale[Number(stop)] = formatHex(color) ?? '#000000';
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
