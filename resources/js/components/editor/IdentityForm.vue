<script setup lang="ts">
import type { CvContent, ContactType } from '@/lib/types';
import { CONTACT_LABELS } from '@/lib/sections';

const props = defineProps<{ content: CvContent; disabled?: boolean }>();

/** Doit rester aligné sur CvDefaults::CONTACT_TYPES, qui valide côté serveur. */
const TYPES: ContactType[] = [
    'email',
    'phone',
    'location',
    'website',
    'linkedin',
    'github',
    'gitlab',
    'malt',
    'linktree',
    'behance',
    'dribbble',
    'mastodon',
];

const TYPE_OPTIONS = TYPES.map((type) => ({ label: CONTACT_LABELS[type], value: type }));

const PLACEHOLDERS: Partial<Record<ContactType, string>> = {
    email: 'prenom.nom@example.com',
    phone: '06 12 34 56 78',
    location: '69003 Lyon, France',
    website: 'mon-portfolio.fr',
    linkedin: 'linkedin.com/in/prenom-nom',
    github: 'github.com/pseudo',
    gitlab: 'gitlab.com/pseudo',
    malt: 'malt.fr/profile/pseudo',
    linktree: 'linktr.ee/pseudo',
    behance: 'behance.net/pseudo',
    dribbble: 'dribbble.com/pseudo',
    mastodon: '@pseudo@mastodon.social',
};

function addContact(): void {
    props.content.contact.push({ type: 'website', value: '' });
}

function removeContact(index: number): void {
    props.content.contact.splice(index, 1);
}
</script>

<template>
    <div class="space-y-4">
        <UFormField label="Nom complet">
            <UInput v-model="content.identity.fullName" :disabled="disabled" placeholder="Prénom Nom" />
        </UFormField>

        <UFormField label="Intitulé du poste">
            <UInput
                v-model="content.identity.jobTitle"
                :disabled="disabled"
                placeholder="Chargée de communication"
            />
        </UFormField>

        <!--
            Anciennement « Technologies » : le champ ne concerne pas que les
            métiers techniques. La clé JSON reste `techLine` pour ne pas casser
            les CV déjà enregistrés — c'est un détail d'implémentation.
        -->
        <UFormField label="Sous-titre" hint="Ligne secondaire, sous l'intitulé">
            <UInput
                v-model="content.identity.techLine"
                :disabled="disabled"
                placeholder="Événementiel • Réseaux sociaux • Rédaction"
            />
        </UFormField>

        <UFormField label="Accroche" hint="Deux lignes maximum">
            <UTextarea v-model="content.identity.tagline" :rows="3" autoresize :disabled="disabled" />
        </UFormField>

        <UFormField label="Profil" hint="Paragraphe de présentation">
            <UTextarea v-model="content.profile" :rows="5" autoresize :disabled="disabled" />
        </UFormField>

        <UFormField label="Coordonnées">
            <div class="space-y-2">
                <div v-for="(contact, index) in content.contact" :key="index" class="flex gap-2">
                    <USelect
                        v-model="contact.type"
                        :items="TYPE_OPTIONS"
                        class="w-36 shrink-0"
                        :disabled="disabled"
                    />
                    <UInput
                        v-model="contact.value"
                        class="min-w-0 flex-1"
                        :placeholder="PLACEHOLDERS[contact.type]"
                        :disabled="disabled"
                    />
                    <UButton
                        size="xs"
                        variant="ghost"
                        color="error"
                        icon="i-lucide-x"
                        :disabled="disabled"
                        @click="removeContact(index)"
                    />
                </div>

                <UButton
                    size="xs"
                    variant="soft"
                    color="neutral"
                    icon="i-lucide-plus"
                    :disabled="disabled"
                    @click="addContact"
                >
                    Ajouter une coordonnée
                </UButton>
            </div>
        </UFormField>
    </div>
</template>
