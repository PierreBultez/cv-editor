<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { forgetCv, listCvs, type StoredCv } from '@/lib/storage';

/**
 * Liste entierement cliente : le serveur ne sait pas quels CV appartiennent au
 * visiteur, cette information n'existe que dans son localStorage.
 */
const entries = ref<StoredCv[]>([]);

onMounted(() => {
    entries.value = listCvs();
});

function forget(publicId: string): void {
    forgetCv(publicId);
    entries.value = listCvs();
}
</script>

<template>
    <Head title="Mes CV" />

    <div class="min-h-screen">
        <header class="border-b border-default">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                <UButton to="/" variant="ghost" color="neutral" icon="i-lucide-arrow-left">Accueil</UButton>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-6 py-12">
            <h1 class="text-2xl font-bold">Mes CV</h1>
            <p class="mt-2 text-muted">Les CV que vous avez créés depuis ce navigateur.</p>

            <UAlert
                v-if="!entries.length"
                class="mt-8"
                color="neutral"
                variant="subtle"
                icon="i-lucide-folder-open"
                title="Aucun CV enregistré ici"
                description="Créez-en un depuis l'accueil : il apparaîtra dans cette liste."
            />

            <ul v-else class="mt-8 space-y-3">
                <li
                    v-for="entry in entries"
                    :key="entry.publicId"
                    class="flex items-center justify-between gap-4 rounded-lg border border-default p-4"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium">{{ entry.title }}</p>
                        <p class="text-sm text-muted">
                            Modifié le {{ new Date(entry.updatedAt).toLocaleDateString('fr-FR') }}
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <UButton :to="`/cv/${entry.publicId}/edit`" size="sm" icon="i-lucide-pencil">Éditer</UButton>
                        <UButton
                            size="sm"
                            variant="ghost"
                            color="neutral"
                            icon="i-lucide-x"
                            title="Retirer de cette liste (le CV n'est pas supprimé)"
                            @click="forget(entry.publicId)"
                        />
                    </div>
                </li>
            </ul>
        </main>
    </div>
</template>
