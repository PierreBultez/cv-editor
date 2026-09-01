<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import CiviLogo from '@/components/CiviLogo.vue';

const creating = ref(false);
const page = usePage();

function createCv(): void {
    creating.value = true;
    router.post('/cv', {}, { onFinish: () => (creating.value = false) });
}
</script>

<template>
    <Head title="Créer un CV" />

    <div class="min-h-screen">
        <header class="border-b border-default">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <CiviLogo />
                <UButton to="/mes-cv" variant="ghost" color="neutral" icon="i-lucide-folder">Mes CV</UButton>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-16">
            <UAlert
                v-if="page.props.flash?.status"
                class="mb-8"
                color="success"
                variant="subtle"
                :description="String(page.props.flash.status)"
            />

            <h1 class="max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl">
                Faites votre CV,<br class="hidden sm:inline" />
                pas votre mise en page.
            </h1>

            <p class="mt-5 max-w-2xl text-lg text-muted">
                Remplissez vos expériences, choisissez deux couleurs et deux polices, ajoutez votre photo.
                L'aperçu au format A4 se met à jour à chaque frappe.
            </p>

            <div class="mt-10 flex flex-wrap items-center gap-3">
                <UButton size="xl" icon="i-lucide-plus" :loading="creating" @click="createCv">
                    Créer mon CV
                </UButton>
                <UButton size="xl" variant="subtle" color="neutral" to="/cv/01K0DEMXCV0000000000000000">
                    Voir un exemple
                </UButton>
            </div>

            <UAlert
                class="mt-12 max-w-2xl"
                color="neutral"
                variant="subtle"
                icon="i-lucide-info"
                title="Sans compte, et sans oubli"
                description="Aucune inscription : votre CV est accessible par un lien secret conservé dans ce navigateur. Effacer les données du site vous en ferait perdre l'accès en écriture. Les CV inactifs pendant 12 mois sont supprimés automatiquement."
            />
        </main>
    </div>
</template>
