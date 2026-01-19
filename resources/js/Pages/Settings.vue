<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import MobileShell from '@/Layouts/MobileShell.vue';
import DesktopSettingsShell from '@/Layouts/DesktopSettingsShell.vue';
import { useIsMobile } from '@/composables/useIsMobile';

const isMobile = useIsMobile();
const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Gabriel Felix');
const userEmail = computed(() => page.props.auth?.user?.email ?? 'gab.feelix@gmail.com');
const userPhone = computed(() => page.props.auth?.user?.phone ?? '');

const form = useForm({
    name: userName.value,
    email: userEmail.value,
    phone: userPhone.value,
});

watch(
    () => [userName.value, userEmail.value, userPhone.value],
    () => {
        form.name = userName.value;
        form.email = userEmail.value;
        form.phone = userPhone.value;
    },
);

const resetForm = () => {
    form.name = userName.value;
    form.email = userEmail.value;
    form.phone = userPhone.value;
    form.clearErrors();
};

const submitProfile = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};

const initials = computed(() => {
    const parts = String(userName.value).trim().split(/\s+/).filter(Boolean);
    const first = parts[0]?.[0] ?? 'G';
    const last = parts.length > 1 ? parts[parts.length - 1]?.[0] : 'F';
    return `${first}${last}`.toUpperCase();
});
</script>


<template>
    <MobileShell v-if="isMobile">
        <header class="pt-2">
            <div class="text-2xl font-semibold tracking-tight text-slate-900">Configurações</div>
        </header>

        <div class="mt-5 rounded-3xl bg-white p-5 text-center shadow-sm ring-1 ring-slate-200/60">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100 text-lg font-semibold text-slate-700 shadow-sm ring-4 ring-white">
                {{ initials }}
            </div>
            <div class="mt-4 text-lg font-semibold text-slate-900">{{ userName }}</div>
            <div class="text-sm text-slate-400">{{ userEmail }}</div>
            <Link
                :href="route('profile.edit')"
                class="mt-4 inline-flex items-center justify-center rounded-full border border-teal-500 px-5 py-2 text-sm font-semibold text-teal-600"
            >
                Editar perfil
            </Link>
        </div>

        <div class="mt-8 pb-4">
            <div class="text-lg font-semibold text-slate-900">Geral</div>

            <div class="mt-4 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/60">
                <Link :href="route('settings.notifications')" class="flex w-full items-center justify-between px-5 py-4 text-left">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                                <path d="M9 17a3 3 0 0 0 6 0" />
                            </svg>
                        </span>
                        <div class="text-sm font-semibold text-slate-900">Notificações</div>
                    </div>
                    <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" />
                    </svg>
                </Link>

                <div class="border-t border-slate-100">
                    <Link :href="route('settings.security')" class="flex w-full items-center justify-between px-5 py-4 text-left">
                        <div class="flex items-center gap-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V6l-8-4-8 4v6c0 6 8 10 8 10Z" />
                                </svg>
                            </span>
                            <div class="text-sm font-semibold text-slate-900">Segurança e Privacidade</div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </Link>
                </div>

                <div class="border-t border-slate-100">
                    <Link :href="route('settings.support')" class="flex w-full items-center justify-between px-5 py-4 text-left">
                        <div class="flex items-center gap-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 16v-1" />
                                    <path d="M12 11a2 2 0 1 0-2-2" />
                                </svg>
                            </span>
                            <div class="text-sm font-semibold text-slate-900">Ajuda e Suporte</div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </Link>
                </div>

                <div class="border-t border-slate-100">
                    <Link :href="route('settings.about')" class="flex w-full items-center justify-between px-5 py-4 text-left">
                        <div class="flex items-center gap-4">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 10v6" />
                                    <path d="M12 8h.01" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Sobre o App</div>
                                <div class="text-xs font-semibold text-slate-400">Versão 1.0.0</div>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </Link>
                </div>
            </div>

            <div class="mt-6 flex justify-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm font-semibold text-red-500"
                >
                    Sair da conta
                </Link>
            </div>
        </div>

    </MobileShell>

    <div v-else-if="false">
        <div class="rounded-[28px] border border-white/70 bg-white p-8 shadow-[0_20px_50px_-40px_rgba(15,23,42,0.4)]">
            <div class="text-sm font-semibold text-slate-900">Configurações (desktop/tablet)</div>
            <div class="mt-2 text-sm text-slate-500">Vamos adaptar essa tela depois da versão mobile.</div>
        </div>
    </div>

    <DesktopSettingsShell v-else>
        <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <div class="px-10 py-9">
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 overflow-hidden rounded-2xl bg-slate-200 ring-4 ring-white shadow-sm">
                        <div class="flex h-full w-full items-center justify-center bg-slate-300 text-xl font-bold text-slate-700">
                            {{ initials }}
                        </div>
                    </div>
                    <div class="min-w-0">
                        <div class="truncate text-2xl font-semibold tracking-tight text-slate-900">{{ userName }}</div>
                        <div class="mt-1 text-sm font-semibold text-slate-400">Informações básicas de acesso à conta.</div>
                    </div>
                </div>

                <form class="mt-10 space-y-8" @submit.prevent="submitProfile">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-300">Nome completo</div>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-3 h-12 w-full rounded-2xl bg-slate-50 px-4 text-sm font-semibold text-slate-700 ring-1 ring-slate-200/60 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            />
                            <div v-if="form.errors.name" class="mt-2 text-xs font-semibold text-red-500">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <div class="text-[11px] font-bold uppercase tracking-wide text-slate-300">Telefone</div>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="mt-3 h-12 w-full rounded-2xl bg-slate-50 px-4 text-sm font-semibold text-slate-700 ring-1 ring-slate-200/60 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                            />
                        </div>
                    </div>

                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-300">E-mail de acesso</div>
                        <div class="mt-3 flex items-center gap-3 rounded-2xl bg-slate-50 px-4 ring-1 ring-slate-200/60">
                            <input
                                v-model="form.email"
                                type="email"
                                disabled
                                class="h-12 w-full cursor-not-allowed bg-transparent text-sm font-semibold text-slate-400 focus:outline-none"
                            />
                            <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="6" y="11" width="12" height="10" rx="2" />
                                <path d="M8 11V8a4 4 0 1 1 8 0v3" />
                            </svg>
                        </div>
                        <div class="mt-2 text-xs font-semibold text-slate-300">O e-mail não pode ser alterado para garantir a segurança da conta.</div>
                    </div>

                    <div class="pt-2">
                        <Link :href="route('password.request')" class="inline-flex items-center gap-2 text-sm font-semibold text-[#14B8A6]">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 14 8 16a4 4 0 0 1-6-6l2-2a4 4 0 0 1 6 6Z" />
                                <path d="M14 10l2-2a4 4 0 0 1 6 6l-2 2a4 4 0 0 1-6-6Z" />
                                <path d="M8 16l8-8" />
                            </svg>
                            Esqueceu sua senha?
                        </Link>
                    </div>
                </form>
            </div>

            <div class="flex items-center justify-between border-t border-slate-100 px-10 py-7">
                <button type="button" class="text-sm font-semibold text-slate-400 hover:text-slate-500" @click="resetForm">Cancelar</button>
                <button
                    type="button"
                    class="inline-flex h-12 items-center justify-center rounded-2xl bg-[#14B8A6] px-8 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20 disabled:opacity-60"
                    :disabled="form.processing"
                    @click="submitProfile"
                >
                    Guardar Alterações
                </button>
            </div>
        </div>

    </DesktopSettingsShell>
</template>
