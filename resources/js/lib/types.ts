/**
 * Miroir cote client du schema valide par `App\Http\Requests\UpdateCvRequest`.
 * Toute evolution doit rester alignee sur `App\Support\CvDefaults`.
 */

/** Doit rester aligné sur `CvDefaults::CONTACT_TYPES`, qui valide côté serveur. */
export type ContactType =
    | 'email'
    | 'phone'
    | 'location'
    | 'website'
    | 'linkedin'
    | 'github'
    | 'gitlab'
    | 'malt'
    | 'linktree'
    | 'behance'
    | 'dribbble'
    | 'mastodon';

export type SectionType =
    | 'experiences'
    | 'education'
    | 'certifications'
    | 'skills'
    | 'languages'
    | 'tools'
    | 'interests';

export type ColumnName = 'sidebar' | 'main';

export interface ExperienceItem {
    period: string;
    role: string;
    company: string;
    location: string;
    bullets: string[];
}

/** Formation et certification partagent la meme forme. */
export interface DiplomaItem {
    period: string;
    degree: string;
    school: string;
    location: string;
    detail: string;
}

export interface SkillItem {
    label: string;
    /** 0 à 100, rendu en barre de progression. */
    level: number;
}

export interface LanguageItem {
    label: string;
    mention: string;
    /** 0 à 5, rendu en pastilles. */
    level: number;
}

export type SectionItem = ExperienceItem | DiplomaItem | SkillItem | LanguageItem | string;

export interface CvSection {
    id: string;
    type: SectionType;
    title: string;
    column: ColumnName;
    enabled: boolean;
    items: SectionItem[];
}

export interface CvIdentity {
    fullName: string;
    jobTitle: string;
    techLine: string;
    tagline: string;
}

export interface CvContact {
    type: ContactType;
    value: string;
}

export interface CvContent {
    schema_version: number;
    identity: CvIdentity;
    profile: string;
    contact: CvContact[];
    sections: CvSection[];
}

export interface CvTheme {
    primary: string;
    accent: string;
}

export interface CvFonts {
    title: string;
    body: string;
}

/** URL absolues renvoyees par le serveur, jamais des chemins de stockage. */
export interface CvPhoto {
    jpg: string;
    thumb: string;
    webp?: string;
    avif?: string;
    width: number;
    height: number;
}

export interface CvRecord {
    public_id: string;
    template: string;
    theme: CvTheme;
    fonts: CvFonts;
    content: CvContent;
    photo: CvPhoto | null;
    is_public: boolean;
    allow_indexing: boolean;
    updated_at: string | null;
}

export type SaveState = 'idle' | 'pending' | 'saving' | 'saved' | 'error' | 'readonly';
