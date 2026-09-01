<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppPreview from '@/components/landing/AppPreview.vue';
import BrandIcon from '@/components/landing/BrandIcon.vue';

/**
 * Page d'accueil, portée de la maquette Claude Design « Civi Landing ».
 *
 * Deux écarts assumés par rapport à la maquette :
 *  - les grilles de cartes y sont figées à 3 et 2 colonnes ; elles passent ici
 *    en une colonne sur mobile, sans quoi la page serait inutilisable sur
 *    téléphone ;
 *  - les animations sont coupées si le système demande à les réduire.
 */
const page = usePage();
const creating = ref(false);

/** Identifiant du CV de démonstration, aligné sur DemoCvSeeder::PUBLIC_ID. */
const DEMO_ID = '01K0DEMXCV0000000000000000';

function createCv(): void {
    creating.value = true;
    router.post('/cv', {}, { onFinish: () => (creating.value = false) });
}

const STEPS = [
    {
        n: '1',
        title: 'Vous remplissez',
        text: "Expériences, formations, compétences, langues. Les sections se réordonnent, celles qui ne servent pas se désactivent.",
        tint: 'bg-civi-100 text-civi-600',
    },
    {
        n: '2',
        title: 'Civi met en page',
        text: "Deux couleurs, deux polices, votre photo. La feuille A4 se recompose à chaque frappe — ce que vous voyez est ce qui s'imprime.",
        tint: 'bg-sky-brand-100 text-sky-brand-700',
    },
    {
        n: '3',
        title: 'Vous postulez',
        text: 'PDF en un clic, ou lien public à coller dans un mail. Vous revenez le modifier quand vous voulez.',
        tint: 'bg-sun-100 text-sun-800',
    },
];

const BENEFITS = [
    {
        icon: '/brand/icon-doc.png',
        title: 'Un vrai A4, pas une approximation',
        text: "L'aperçu fait 210 × 297 mm en vraies dimensions. Aucune surprise entre l'écran et le papier.",
    },
    {
        icon: '/brand/icon-cotillon.png',
        title: 'Deux couleurs, deux polices',
        text: "Assez pour avoir l'air unique, trop peu pour rater son coup. La palette se calcule toute seule pour rester lisible à l'impression.",
    },
    {
        icon: '/brand/icon-eclair.png',
        title: 'Un lien à partager',
        text: "Chaque CV a son adresse privée. Vous l'envoyez, elle s'ouvre partout, elle reste à jour.",
    },
    {
        icon: '/brand/icon-bouclier.png',
        title: 'Pas indexé par défaut',
        text: "Votre CV n'apparaît pas dans Google, sauf si vous cochez la case. Et il s'efface tout seul après 12 mois d'oubli.",
    },
];

const PROMISES = [
    {
        accent: 'text-sun-400',
        title: 'Zéro inscription',
        text: "On clique, on écrit. C'est tout.",
    },
    {
        accent: 'text-sky-brand-400',
        title: 'Suppression définitive en un bouton',
        text: 'Vos données partent quand vous le décidez.',
    },
    {
        accent: 'text-sun-400',
        title: 'Purge automatique après 12 mois',
        text: "Un CV oublié ne traîne pas éternellement sur un serveur.",
    },
];

const FAQ = [
    {
        q: "C'est gratuit… et le piège, il est où ?",
        a: "Nulle part. Pas de version « Pro », pas de filigrane sur le PDF, pas de revente de données. Civi est un outil, pas un entonnoir.",
    },
    {
        q: 'Comment je récupère mon PDF ?',
        a: "Bouton « Imprimer / PDF », puis « Enregistrer au format PDF » dans votre navigateur. Papier A4, marges « Aucune », graphiques d'arrière-plan activés : Civi vous le rappelle au bon moment.",
    },
    {
        q: "Je change d'ordinateur, je perds tout ?",
        a: "Le CV reste consultable par son lien public. Le droit de modification, lui, est stocké dans le navigateur qui l'a créé — conservez le lien d'édition pour le retrouver.",
    },
    {
        q: 'Mon CV va se retrouver sur Google ?',
        a: "Non. Les pages publiques sont en noindex par défaut ; l'indexation est une case à cocher dans les réglages, jamais l'inverse.",
    },
    {
        q: 'Combien de CV puis-je créer ?',
        a: "Autant que d'offres qui vous font envie. Ils s'alignent tous dans « Mes CV », depuis ce navigateur.",
    },
];

const MARQUEE = ['SIMPLE', 'RAPIDE', 'GRATUIT', 'SANS COMPTE', 'SANS PUB', 'SANS FILIGRANE'];
</script>

<template>
    <Head title="Faites votre CV, pas votre mise en page" />

    <!--
        `overflow-x-clip` et non `hidden` : `hidden` ferait de ce conteneur le
        parent de défilement de l'en-tête, qui perdrait alors son `sticky`
        faute de quoi s'accrocher. `clip` rogne le débordement des halos du
        hero sans créer de conteneur de défilement.
    -->
    <div class="overflow-x-clip bg-ecru text-anthracite">
        <!-- ================= En-tête ================= -->
        <header
            class="sticky top-0 z-50 border-b border-anthracite/7 bg-ecru/85 backdrop-blur-[14px]"
        >
            <div class="mx-auto flex max-w-[1180px] items-center gap-7 px-7 py-3.5">
                <a href="#top" class="flex shrink-0 items-center">
                    <img src="/logo-civi-320.png" srcset="/logo-civi-320.png 320w, /logo-civi-640.png 640w"
                         sizes="150px" width="320" height="240" alt="Civi" class="h-[34px] w-auto" />
                </a>

                <nav class="ml-3.5 hidden items-center gap-6 text-[15px] font-medium md:flex">
                    <a href="#comment" class="text-[#4a4c58] hover:text-civi-500">Comment ça marche</a>
                    <a href="#atouts" class="text-[#4a4c58] hover:text-civi-500">Ce que ça fait</a>
                    <a href="#faq" class="text-[#4a4c58] hover:text-civi-500">Questions</a>
                </nav>

                <div class="ml-auto flex items-center gap-2.5">
                    <a
                        href="/mes-cv"
                        class="rounded-[11px] px-4 py-2.5 text-[15px] font-semibold text-anthracite hover:bg-civi-500/8 hover:text-civi-500"
                    >
                        Mes CV
                    </a>
                    <button
                        type="button"
                        class="rounded-xl bg-civi-500 px-5 py-2.5 text-[15px] font-bold text-white shadow-[0_8px_20px_-8px_rgba(108,60,255,0.9)] transition hover:bg-[#5528e6] disabled:opacity-70"
                        :disabled="creating"
                        @click="createCv"
                    >
                        Créer mon CV
                    </button>
                </div>
            </div>
        </header>

        <!-- ================= Hero ================= -->
        <section id="top" class="relative mx-auto max-w-[1180px] px-7 pb-[90px] pt-[72px]">
            <div
                class="pointer-events-none absolute -left-[220px] -top-[140px] size-[460px] rounded-full bg-sky-brand-400 opacity-[0.16] blur-[10px]"
                aria-hidden="true"
            />
            <div
                class="pointer-events-none absolute -right-[260px] top-[180px] size-[420px] rounded-full bg-sun-400 opacity-[0.22] blur-[10px]"
                aria-hidden="true"
            />

            <div class="relative grid items-center gap-14 lg:grid-cols-2">
                <div>
                    <div
                        class="inline-flex items-center gap-2.5 rounded-full border border-anthracite/8 bg-white py-1.5 pl-2.5 pr-3.5 text-[13.5px] font-semibold shadow-[0_6px_18px_-12px_rgba(30,31,38,0.5)]"
                    >
                        <BrandIcon src="/brand/icon-eclair.png" class="size-6 object-contain" />
                        Gratuit · sans compte · sans pub
                    </div>

                    <h1
                        class="mt-[22px] text-balance font-display text-[44px] font-extrabold leading-[1.02] tracking-[-0.028em] sm:text-[56px] lg:text-[64px]"
                    >
                        Faites votre CV,<br />pas votre
                        <span class="relative inline-block whitespace-nowrap text-civi-500">
                            <span
                                class="absolute inset-x-0 bottom-1 h-3 rounded-full bg-sky-brand-400 opacity-45"
                                aria-hidden="true"
                            />
                            <span class="relative">mise en page.</span>
                        </span>
                    </h1>

                    <p class="mt-[26px] max-w-[520px] text-pretty text-[19.5px] leading-[1.55] text-[#4a4c58]">
                        Civi met votre CV en page pendant que vous le remplissez. L'aperçu A4 bouge à chaque
                        frappe, deux couleurs et deux polices suffisent, et le PDF sort à la fin. Pas de compte,
                        pas d'abonnement, pas de filigrane.
                    </p>

                    <div class="mt-[34px] flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2.5 rounded-[14px] bg-civi-500 px-7 py-4 text-[17px] font-bold text-white shadow-[0_16px_34px_-14px_rgba(108,60,255,1)] transition hover:-translate-y-px hover:bg-[#5528e6] disabled:opacity-70"
                            :disabled="creating"
                            @click="createCv"
                        >
                            Créer mon CV <span class="text-[19px] leading-none">→</span>
                        </button>
                        <a
                            :href="`/cv/${DEMO_ID}`"
                            class="inline-flex items-center gap-2.5 rounded-[14px] border border-anthracite/12 bg-white px-6 py-4 text-[17px] font-bold text-anthracite transition hover:border-civi-500 hover:text-civi-500"
                        >
                            Voir un exemple
                        </a>
                    </div>

                    <div
                        class="mt-[26px] flex flex-wrap items-center gap-[18px] text-[14.5px] font-medium text-[#6b6d7a]"
                    >
                        <span
                            v-for="claim in ['Aucune inscription', 'PDF prêt à envoyer', '4 minutes chrono']"
                            :key="claim"
                            class="inline-flex items-center gap-[7px]"
                        >
                            <span class="font-extrabold text-civi-500">✓</span> {{ claim }}
                        </span>
                    </div>
                </div>

                <AppPreview />
            </div>
        </section>

        <!-- ================= Bandeau défilant ================= -->
        <div class="overflow-hidden bg-anthracite py-[17px]" aria-hidden="true">
            <div class="civi-marquee flex w-max">
                <div
                    v-for="copy in 2"
                    :key="copy"
                    class="flex items-center gap-[34px] pr-[34px] font-display text-[19px] font-bold tracking-[0.02em] text-white"
                >
                    <template v-for="(word, index) in MARQUEE" :key="word">
                        <span>{{ word }}</span>
                        <span :class="index % 2 === 0 ? 'text-sun-400' : 'text-sky-brand-400'">★</span>
                    </template>
                </div>
            </div>
        </div>

        <!-- ================= Comment ça marche ================= -->
        <section id="comment" class="mx-auto max-w-[1180px] px-7 pb-5 pt-24">
            <div class="flex flex-wrap items-end justify-between gap-10">
                <div>
                    <div class="text-[13px] font-extrabold uppercase tracking-[0.14em] text-civi-500">
                        Comment ça marche
                    </div>
                    <h2
                        class="mt-3.5 max-w-[640px] font-display text-[32px] font-extrabold leading-[1.08] tracking-[-0.025em] sm:text-[44px]"
                    >
                        Trois étapes, zéro tutoriel.
                    </h2>
                </div>
                <p class="m-0 max-w-[340px] text-[16.5px] leading-[1.55] text-[#6b6d7a]">
                    Pas de gabarit à choisir dans une grille de quarante, pas de compte à créer avant de voir quoi
                    que ce soit.
                </p>
            </div>

            <div class="mt-11 grid gap-[22px] sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="step in STEPS"
                    :key="step.n"
                    class="rounded-[20px] border border-anthracite/8 bg-white px-7 pb-8 pt-[30px]"
                >
                    <div
                        class="inline-flex size-11 items-center justify-center rounded-[13px] font-display text-[19px] font-extrabold"
                        :class="step.tint"
                    >
                        {{ step.n }}
                    </div>
                    <h3 class="mb-2.5 mt-5 font-display text-[21px] font-bold tracking-[-0.015em]">
                        {{ step.title }}
                    </h3>
                    <p class="m-0 text-base leading-[1.55] text-[#5a5c68]">{{ step.text }}</p>
                </div>
            </div>
        </section>

        <!-- ================= Ce que ça fait ================= -->
        <section id="atouts" class="mx-auto max-w-[1180px] px-7 pb-5 pt-[88px]">
            <h2
                class="m-0 max-w-[600px] font-display text-[32px] font-extrabold leading-[1.08] tracking-[-0.025em] sm:text-[44px]"
            >
                Ce que les autres facturent 9 €/mois.
            </h2>

            <div class="mt-11 grid gap-[22px] md:grid-cols-2">
                <div
                    v-for="benefit in BENEFITS"
                    :key="benefit.title"
                    class="flex items-start gap-5 rounded-[20px] border border-anthracite/8 bg-white p-[30px]"
                >
                    <BrandIcon :src="benefit.icon" class="-my-1.5 size-[62px] shrink-0 object-contain" />
                    <div>
                        <h3 class="mb-2 mt-0 font-display text-xl font-bold tracking-[-0.015em]">
                            {{ benefit.title }}
                        </h3>
                        <p class="m-0 text-base leading-[1.55] text-[#5a5c68]">{{ benefit.text }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= Sans compte ================= -->
        <section class="mx-auto max-w-[1180px] px-7 pb-5 pt-[88px]">
            <div class="relative overflow-hidden rounded-[26px] bg-anthracite px-8 py-14 text-white sm:px-13">
                <div
                    class="absolute -right-[90px] -top-[90px] size-[320px] rounded-full bg-civi-500 opacity-40 blur-[4px]"
                    aria-hidden="true"
                />
                <div class="relative grid items-center gap-14 lg:grid-cols-2">
                    <div>
                        <div class="text-[13px] font-extrabold uppercase tracking-[0.14em] text-sun-400">
                            Sans compte, vraiment
                        </div>
                        <h2
                            class="mb-[18px] mt-4 font-display text-[30px] font-extrabold leading-[1.1] tracking-[-0.025em] sm:text-[40px]"
                        >
                            Aucun mot de passe à oublier.
                        </h2>
                        <p class="m-0 max-w-[440px] text-[17px] leading-[1.6] text-white/70">
                            Votre CV vit derrière un lien secret gardé par votre navigateur. Pas d'email, pas de
                            newsletter, rien à désinscrire. En contrepartie, une règle honnête : effacer les
                            données du site vous fait perdre le droit de modification. Gardez le lien quelque part.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3.5">
                        <div
                            v-for="promise in PROMISES"
                            :key="promise.title"
                            class="flex items-start gap-3.5 rounded-2xl border border-white/10 bg-white/6 px-5 py-[18px]"
                        >
                            <span class="text-lg leading-[1.3]" :class="promise.accent">✓</span>
                            <div>
                                <div class="mb-1 font-display text-base font-semibold">{{ promise.title }}</div>
                                <div class="text-[14.5px] leading-[1.5] text-white/60">{{ promise.text }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= FAQ ================= -->
        <section id="faq" class="mx-auto max-w-[900px] px-7 pb-5 pt-[88px]">
            <h2
                class="mb-[34px] mt-0 text-center font-display text-[30px] font-extrabold leading-[1.1] tracking-[-0.025em] sm:text-[40px]"
            >
                Les questions qu'on nous pose
            </h2>

            <div class="flex flex-col gap-3">
                <div
                    v-for="item in FAQ"
                    :key="item.q"
                    class="rounded-2xl border border-anthracite/8 bg-white px-[26px] py-[22px]"
                >
                    <div class="mb-[7px] font-display text-[17.5px] font-semibold">{{ item.q }}</div>
                    <p class="m-0 text-base leading-[1.55] text-[#5a5c68]">{{ item.a }}</p>
                </div>
            </div>
        </section>

        <!-- ================= Appel final ================= -->
        <section class="mx-auto max-w-[1180px] px-7 pb-[100px] pt-[88px]">
            <div class="relative overflow-hidden rounded-[26px] bg-civi-500 px-10 py-[70px] text-center">
                <div
                    class="absolute -bottom-[110px] -left-[70px] size-[280px] rounded-full bg-sky-brand-400 opacity-50"
                    aria-hidden="true"
                />
                <div
                    class="absolute -right-[60px] -top-[100px] size-[240px] rounded-full bg-sun-400"
                    aria-hidden="true"
                />

                <div class="relative">
                    <BrandIcon src="/brand/icon-gratuit.png" class="mx-auto mb-[18px] size-[82px] object-contain" />
                    <h2
                        class="m-0 text-balance font-display text-[34px] font-extrabold leading-[1.06] tracking-[-0.03em] text-white sm:text-[48px]"
                    >
                        Votre prochain CV est<br />à quatre minutes d'ici.
                    </h2>
                    <p class="mx-auto mt-5 max-w-[480px] text-lg leading-[1.55] text-white/80">
                        Pas d'email à donner pour commencer. On vous demandera juste votre prénom — et encore,
                        c'est pour le mettre en haut de la page.
                    </p>
                    <button
                        type="button"
                        class="mt-[34px] inline-flex items-center gap-2.5 rounded-[14px] bg-white px-8 py-[18px] font-display text-lg font-bold text-civi-500 shadow-[0_18px_34px_-18px_rgba(0,0,0,0.7)] transition hover:bg-sun-400 hover:text-anthracite disabled:opacity-70"
                        :disabled="creating"
                        @click="createCv"
                    >
                        Créer mon CV gratuitement <span class="text-xl leading-none">→</span>
                    </button>
                </div>
            </div>
        </section>

        <!-- ================= Pied de page ================= -->
        <footer class="border-t border-anthracite/8 bg-white">
            <div class="mx-auto flex max-w-[1180px] flex-wrap items-center gap-6 px-7 py-[38px]">
                <img src="/icon-192.png" alt="Civi" class="size-8 rounded-[9px]" />
                <span class="text-[15px] text-[#6b6d7a]">Le CV sans prise de tête.</span>
                <div class="ml-auto flex flex-wrap gap-6 text-[15px] font-medium">
                    <a :href="`/cv/${DEMO_ID}`" class="text-[#4a4c58] hover:text-civi-500">Exemple de CV</a>
                    <a href="/mes-cv" class="text-[#4a4c58] hover:text-civi-500">Mes CV</a>
                    <a href="#faq" class="text-[#4a4c58] hover:text-civi-500">Données personnelles</a>
                </div>
            </div>
        </footer>

        <UAlert
            v-if="page.props.flash?.status"
            class="fixed bottom-4 left-1/2 z-50 w-[min(28rem,calc(100vw-2rem))] -translate-x-1/2"
            color="success"
            variant="subtle"
            :description="String(page.props.flash.status)"
        />
    </div>
</template>
