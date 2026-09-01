import type { SectionType } from '@/lib/types';

/** Icone associee a chaque type de section dans les titres du CV. */
const ICONS: Record<SectionType, string> = {
    experiences: 'briefcase',
    education: 'graduation',
    certifications: 'award',
    skills: 'code',
    languages: 'globe',
    tools: 'monitor',
    interests: 'star',
};

export function sectionIcon(type: SectionType): string {
    return ICONS[type] ?? 'user';
}

/**
 * Icones Lucide utilisees par l'interface de l'editeur (Nuxt UI).
 * Distinctes de `sectionIcon`, qui designe les traces SVG inlines du CV lui-meme.
 */
const LUCIDE_ICONS: Record<SectionType, string> = {
    experiences: 'i-lucide-briefcase',
    education: 'i-lucide-graduation-cap',
    certifications: 'i-lucide-award',
    skills: 'i-lucide-code',
    languages: 'i-lucide-globe',
    tools: 'i-lucide-monitor',
    interests: 'i-lucide-star',
};

export function sectionLucideIcon(type: SectionType): string {
    return LUCIDE_ICONS[type] ?? 'i-lucide-layers';
}

/** Libelles employes dans l'editeur pour proposer l'ajout d'une section. */
export const SECTION_LABELS: Record<SectionType, string> = {
    experiences: 'Expériences professionnelles',
    education: 'Formation',
    certifications: 'Certifications',
    skills: 'Compétences',
    languages: 'Langues',
    tools: 'Logiciels / Outils',
    interests: "Centres d'intérêt",
};

/**
 * Icone des coordonnees, par type de contact.
 *
 * Ce sont des pictogrammes geometriques et non les marques des plateformes :
 * sur la feuille, ces icones font 3,5 px de haut. A cette taille un logo
 * detaille devient une tache, tandis qu'une forme simple reste lisible — a
 * l'ecran comme a l'impression. LinkedIn conserve sa marque, deja en place et
 * suffisamment simple pour tenir.
 */
export const CONTACT_ICONS: Record<string, string> = {
    email: 'mail',
    phone: 'phone',
    location: 'location',
    website: 'globe',
    linkedin: 'linkedin',
    github: 'code-branch',
    gitlab: 'code-branch',
    malt: 'briefcase',
    linktree: 'link',
    behance: 'palette',
    dribbble: 'palette',
    mastodon: 'megaphone',
};

export const CONTACT_LABELS: Record<string, string> = {
    email: 'E-mail',
    phone: 'Téléphone',
    location: 'Localisation',
    website: 'Site web',
    linkedin: 'LinkedIn',
    github: 'GitHub',
    gitlab: 'GitLab',
    malt: 'Malt',
    linktree: 'Linktree',
    behance: 'Behance',
    dribbble: 'Dribbble',
    mastodon: 'Mastodon',
};
