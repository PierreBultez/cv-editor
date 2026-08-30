<script setup lang="ts">
import type { CvContent, ContactType } from '@/lib/types';
import { CONTACT_LABELS } from '@/lib/sections';

const props = defineProps<{ content: CvContent; disabled?: boolean }>();

const TYPES: ContactType[] = ['email', 'phone', 'location', 'linkedin', 'website'];

const TYPE_OPTIONS = TYPES.map((type) => ({ label: CONTACT_LABELS[type], value: type }));

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
            <UInput v-model="content.identity.fullName" :disabled="disabled" placeholder="Pierre Bultez" />
        </UFormField>

        <UFormField label="Intitulé du poste">
            <UInput
                v-model="content.identity.jobTitle"
                :disabled="disabled"
                placeholder="Développeur Fullstack"
            />
        </UFormField>

        <UFormField label="Technologies" hint="Ligne secondaire, sous l'intitulé">
            <UInput
                v-model="content.identity.techLine"
                :disabled="disabled"
                placeholder="Laravel • VueJS • MySQL"
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
                    <UInput v-model="contact.value" class="flex-1" :disabled="disabled" />
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
