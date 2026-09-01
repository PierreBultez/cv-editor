/**
 * Catalogue des polices proposees dans l'editeur.
 *
 * Les alias doivent correspondre exactement a ceux declares dans
 * `vite.config.ts` (qui expose une variable `--font-{alias}`) et a la liste
 * `CvDefaults::FONTS` cote serveur, qui valide le choix.
 */

export interface FontOption {
    alias: string;
    label: string;
    category: 'sans' | 'serif';
    /** Note affichee dans le selecteur pour guider le choix. */
    hint: string;
}

export const FONTS: FontOption[] = [
    { alias: 'satoshi', label: 'Satoshi', category: 'sans', hint: 'Géométrique, neutre — le choix par défaut' },
    { alias: 'poppins', label: 'Poppins', category: 'sans', hint: 'Ronde et affirmée, la police de Civi' },
    { alias: 'inter', label: 'Inter', category: 'sans', hint: 'Très lisible en petits corps' },
    { alias: 'outfit', label: 'Outfit', category: 'sans', hint: 'Rond et contemporain' },
    { alias: 'space-grotesk', label: 'Space Grotesk', category: 'sans', hint: 'Caractère technique' },
    { alias: 'fraunces', label: 'Fraunces', category: 'serif', hint: 'Serif expressif, bon en titres' },
    { alias: 'playfair', label: 'Playfair Display', category: 'serif', hint: 'Contrasté, très éditorial' },
    { alias: 'lora', label: 'Lora', category: 'serif', hint: 'Serif sobre, confortable en texte' },
];

export const FONT_ALIASES = FONTS.map((font) => font.alias);

export function fontLabel(alias: string): string {
    return FONTS.find((font) => font.alias === alias)?.label ?? alias;
}

/**
 * Pile utilisee pour l'apercu du selecteur. Les fichiers ne sont telecharges
 * par le navigateur qu'a partir du moment ou la famille est reellement
 * appliquee, d'ou l'absence de prechargement pour la plupart d'entre elles.
 */
export function fontStack(alias: string): string {
    const option = FONTS.find((font) => font.alias === alias);
    const fallback = option?.category === 'serif' ? 'ui-serif, Georgia, serif' : 'ui-sans-serif, system-ui, sans-serif';

    return `var(--font-${alias}), ${fallback}`;
}
