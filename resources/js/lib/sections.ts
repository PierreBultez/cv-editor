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

/** Icone des coordonnees, par type de contact. */
export const CONTACT_ICONS: Record<string, string> = {
    email: 'mail',
    phone: 'phone',
    location: 'location',
    linkedin: 'linkedin',
    website: 'globe',
};

export const CONTACT_LABELS: Record<string, string> = {
    email: 'E-mail',
    phone: 'Téléphone',
    location: 'Localisation',
    linkedin: 'LinkedIn',
    website: 'Site web',
};
