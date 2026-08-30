<script setup lang="ts">
import { ref } from 'vue';
import { Cropper, type CropperResult } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import type { CvPhoto } from '@/lib/types';
import CvPhotoFrame from '@/components/cv/CvPhotoFrame.vue';

const props = defineProps<{ photo: CvPhoto | null; disabled?: boolean; supportsAvif: boolean }>();

const emit = defineEmits<{ upload: [Blob]; remove: [] }>();

const fileInput = ref<HTMLInputElement | null>(null);
const cropperSource = ref<string | null>(null);
const cropper = ref<InstanceType<typeof Cropper> | null>(null);
const busy = ref(false);
const error = ref<string | null>(null);

function pick(): void {
    error.value = null;
    fileInput.value?.click();
}

function onFile(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        error.value = "Ce fichier n'est pas une image.";
        return;
    }

    // L'aperçu du recadrage travaille sur une URL locale : rien n'est envoyé
    // au serveur tant que l'utilisateur n'a pas validé son cadrage.
    cropperSource.value = URL.createObjectURL(file);
    (event.target as HTMLInputElement).value = '';
}

function closeCropper(): void {
    if (cropperSource.value) {
        URL.revokeObjectURL(cropperSource.value);
    }

    cropperSource.value = null;
}

/**
 * Le carré est produit ici, à 1024 px maximum, plutôt que d'envoyer l'original :
 * cela réduit fortement la charge réseau et laisse le cadrage à l'utilisateur
 * au lieu d'un recadrage centré arbitraire côté serveur.
 */
async function confirmCrop(): Promise<void> {
    const result = cropper.value?.getResult() as CropperResult | undefined;

    if (!result?.canvas) {
        return;
    }

    busy.value = true;
    error.value = null;

    try {
        const blob = await new Promise<Blob | null>((resolve) =>
            result.canvas!.toBlob(resolve, 'image/jpeg', 0.92),
        );

        if (!blob) {
            throw new Error("Le recadrage n'a pas pu être exporté.");
        }

        emit('upload', blob);
        closeCropper();
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Erreur inconnue';
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center gap-4">
            <div
                v-if="photo"
                class="size-20 shrink-0 overflow-hidden rounded-full bg-elevated"
            >
                <CvPhotoFrame :photo="photo" alt="Photo du CV" size="5rem" />
            </div>
            <div
                v-else
                class="grid size-20 shrink-0 place-items-center rounded-full border border-dashed border-default text-muted"
            >
                <UIcon name="i-lucide-user" class="size-7" />
            </div>

            <div class="flex flex-col gap-2">
                <UButton size="sm" icon="i-lucide-upload" :disabled="disabled" @click="pick">
                    {{ photo ? 'Remplacer' : 'Ajouter une photo' }}
                </UButton>
                <UButton
                    v-if="photo"
                    size="sm"
                    variant="ghost"
                    color="error"
                    icon="i-lucide-trash-2"
                    :disabled="disabled"
                    @click="emit('remove')"
                >
                    Retirer
                </UButton>
            </div>
        </div>

        <p class="text-xs text-muted">
            Redimensionnée en 512 px et convertie en
            <span v-if="supportsAvif">AVIF, WebP et JPEG</span>
            <span v-else>WebP et JPEG</span>.
        </p>

        <UAlert v-if="error" color="error" variant="subtle" :description="error" />

        <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="onFile" />

        <UModal :open="cropperSource !== null" title="Cadrer la photo" @update:open="closeCropper">
            <template #body>
                <Cropper
                    v-if="cropperSource"
                    ref="cropper"
                    class="h-80 bg-elevated"
                    :src="cropperSource"
                    :stencil-props="{ aspectRatio: 1 }"
                    :resize-image="{ adjustStencil: false }"
                    :canvas="{ maxWidth: 1024, maxHeight: 1024 }"
                />
            </template>

            <template #footer>
                <div class="flex w-full justify-end gap-2">
                    <UButton variant="ghost" color="neutral" @click="closeCropper">Annuler</UButton>
                    <UButton :loading="busy" @click="confirmCrop">Valider le cadrage</UButton>
                </div>
            </template>
        </UModal>
    </div>
</template>
