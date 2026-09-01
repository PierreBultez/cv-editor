<script setup lang="ts">
import BrandIcon from '@/components/landing/BrandIcon.vue';

/**
 * Maquette de l'éditeur affichée dans le hero.
 *
 * Purement décorative : ce n'est pas l'application, seulement une évocation de
 * ce qu'on y trouve. Elle est donc masquée aux lecteurs d'écran, qui n'y
 * trouveraient qu'une suite de rectangles vides.
 */
</script>

<template>
    <div class="relative" aria-hidden="true">
        <!-- Halo coloré derrière la carte -->
        <div
            class="pointer-events-none absolute -inset-x-4 -bottom-6 -top-5 rounded-[34px]"
            style="background: linear-gradient(150deg, rgba(108, 60, 255, 0.14), rgba(77, 182, 255, 0.16))"
        />

        <div
            class="relative overflow-hidden rounded-[22px] border border-anthracite/10 bg-white shadow-[0_40px_80px_-40px_rgba(30,31,38,0.45)]"
        >
            <!-- Barre de navigateur -->
            <div class="flex items-center gap-2.5 border-b border-anthracite/7 px-4 py-3">
                <div class="flex gap-1.5">
                    <span class="block size-2.5 rounded-full bg-sun-400" />
                    <span class="block size-2.5 rounded-full bg-sky-brand-400" />
                    <span class="block size-2.5 rounded-full bg-civi-500" />
                </div>
                <div
                    class="ml-2 flex h-6 flex-1 items-center rounded-lg bg-[#F0F0F6] px-2.5 font-mono text-[11.5px] text-[#8a8c99]"
                >
                    civi.app/cv/…/edit
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3.5 bg-ecru p-4">
                <!-- Colonne formulaire -->
                <div class="flex flex-col gap-2.5">
                    <div class="font-display text-[13px] font-bold">Votre expérience</div>

                    <div class="rounded-[10px] border border-anthracite/10 bg-white px-3 py-2.5">
                        <div class="mb-1.5 text-[10px] font-semibold text-[#8a8c99]">Poste occupé</div>
                        <div class="text-[12.5px] font-semibold">
                            Chef de projet digital<span
                                class="civi-caret ml-0.5 inline-block h-[13px] w-[1.5px] -translate-y-px bg-civi-500 align-[-2px]"
                            />
                        </div>
                    </div>

                    <div class="rounded-[10px] border border-anthracite/10 bg-white px-3 py-2.5">
                        <div class="mb-1.5 text-[10px] font-semibold text-[#8a8c99]">Entreprise</div>
                        <div class="text-[12.5px] font-semibold">Agence Nova</div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-[10px] border border-anthracite/10 bg-white px-3 py-2.5">
                            <div class="mb-1.5 text-[10px] font-semibold text-[#8a8c99]">Début</div>
                            <div class="text-[12.5px] font-semibold">09/2021</div>
                        </div>
                        <div class="rounded-[10px] border border-anthracite/10 bg-white px-3 py-2.5">
                            <div class="mb-1.5 text-[10px] font-semibold text-[#8a8c99]">Fin</div>
                            <div class="text-[12.5px] font-semibold text-civi-500">Aujourd'hui</div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-2 rounded-[10px] border border-sun-400/50 bg-[#FFF6DE] px-3 py-2.5 text-[11.5px] font-semibold text-[#7a5b00]"
                    >
                        <BrandIcon src="/brand/icon-eclair.png" class="-my-1 size-[18px] object-contain" />
                        Sauvegarde automatique
                    </div>
                </div>

                <!-- Colonne aperçu du CV -->
                <div
                    class="flex flex-col overflow-hidden rounded-[10px] border border-anthracite/10 bg-white"
                >
                    <div class="grid flex-1 grid-cols-[34%_1fr]">
                        <div class="flex flex-col gap-2.5 bg-civi-500 px-2.5 py-3">
                            <div class="aspect-square w-full rounded-full bg-white/30" />
                            <div class="h-[5px] w-4/5 rounded-full bg-white/85" />
                            <div class="h-1 w-3/5 rounded-full bg-white/45" />
                            <div class="my-0.5 h-px bg-white/30" />
                            <div class="h-1 rounded-full bg-white/40" />
                            <div class="h-1 w-[85%] rounded-full bg-white/40" />
                            <div class="h-1 w-[70%] rounded-full bg-white/40" />
                            <div class="my-0.5 h-px bg-white/30" />
                            <div class="h-1 w-[90%] rounded-full bg-white/40" />
                            <div class="h-1 w-[55%] rounded-full bg-white/40" />
                        </div>

                        <div class="flex flex-col gap-2 px-3.5 pb-13 pt-3.5">
                            <div class="font-display text-[13px] font-bold tracking-tight">Marie Dupont</div>
                            <div class="text-[9.5px] font-semibold uppercase tracking-[0.06em] text-civi-500">
                                Chef de projet digital
                            </div>
                            <div class="mt-0.5 h-px bg-[#E6E7F0]" />

                            <div class="text-[8px] font-extrabold uppercase tracking-[0.09em] text-civi-500">
                                Expérience
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <div class="h-1 w-[92%] rounded-full bg-[#E6E7F0]" />
                                <div class="h-1 w-[78%] rounded-full bg-[#EFEFF5]" />
                                <div class="h-1 w-[85%] rounded-full bg-[#EFEFF5]" />
                            </div>

                            <div class="mt-1 text-[8px] font-extrabold uppercase tracking-[0.09em] text-civi-500">
                                Formation
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <div class="h-1 w-[88%] rounded-full bg-[#E6E7F0]" />
                                <div class="h-1 w-[64%] rounded-full bg-[#EFEFF5]" />
                            </div>

                            <div class="mt-1 text-[8px] font-extrabold uppercase tracking-[0.09em] text-civi-500">
                                Compétences
                            </div>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="skill in ['Gestion de projet', 'SEO/SEA', 'Notion']"
                                    :key="skill"
                                    class="rounded-full bg-[#F1EDFF] px-1.5 py-[3px] text-[7.5px] font-bold text-[#5528e6]"
                                >
                                    {{ skill }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="civi-float absolute -bottom-8 -right-5 rounded-[14px] bg-civi-500 px-[18px] py-3.5 text-center font-display text-sm font-bold leading-tight text-white shadow-[0_18px_34px_-16px_rgba(108,60,255,1)]"
        >
            Aperçu<br />en temps réel
        </div>
    </div>
</template>
