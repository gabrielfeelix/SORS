<script setup lang="ts">
import { ref } from 'vue';
import AuthShell from '@/Layouts/AuthShell.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirm = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthShell>
        <template #aside>
            <div class="relative flex h-full w-full flex-col justify-between overflow-hidden bg-[linear-gradient(160deg,#1ab7a6,#0b7f78)] px-10 py-10 text-white">
                <div class="pointer-events-none absolute -right-24 -top-20 h-80 w-80 rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-40 -right-16 h-[520px] w-[520px] rounded-full bg-white/10"></div>
                <div class="pointer-events-none absolute -bottom-52 -left-28 h-[520px] w-[520px] rounded-full bg-white/10"></div>

                <div class="relative z-10 space-y-10">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-[var(--kitamo-accent)] text-lg font-bold">
                            K
                        </div>
                        <div class="text-lg font-semibold tracking-wide">Kitamo</div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-4xl font-semibold leading-tight">Deu branco? Acontece.</h2>
                        <p class="max-w-sm text-sm text-white/85">
                            Relaxa, a gente recupera isso num instante. Seus dados continuam blindados e seguros com a gente.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 mt-10 max-w-md rounded-3xl border border-white/15 bg-white/10 p-6 backdrop-blur">
                    <div class="flex items-center gap-3 text-white">
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-white/15">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </span>
                        <div class="text-sm font-semibold">Dica de Mestre</div>
                    </div>
                    <p class="mt-3 text-sm text-white/80">
                        Evite usar a mesma senha em varios sites. O Kitamo recomenda senhas unicas para proteger seu patrimonio.
                    </p>
                </div>
            </div>
        </template>

        <Head title="Nova senha" />

        <div class="flex flex-1 flex-col justify-center lg:justify-start lg:pt-8">
            <div class="mx-auto w-full max-w-md lg:mx-0">
                <Link
                    :href="route('login')"
                    class="hidden items-center gap-2 text-sm font-semibold text-slate-500 lg:inline-flex"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Voltar pro Login
                </Link>
                <Link
                    :href="route('login')"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-400 shadow-sm lg:hidden"
                    aria-label="Voltar"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </Link>

                <div class="mt-6 text-center lg:text-left">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-purple-500">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="11" width="14" height="9" rx="2" />
                            <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                        </svg>
                    </div>

                    <h1 class="mt-4 text-2xl font-semibold text-slate-900">Agora capricha.</h1>
                    <p class="mt-2 text-sm text-slate-500">
                        <span class="lg:hidden">Cria uma senha nova (e segura) pra gente esquecer aquela velha.</span>
                        <span class="hidden lg:inline">Cria uma senha nova (e forte) pra gente esquecer a velha.</span>
                    </p>
                </div>

                <form class="mt-8 space-y-5" @submit.prevent="submit">
                    <input type="hidden" v-model="form.email" />

                    <div>
                        <label for="password" class="text-xs font-semibold text-slate-500">Nova senha</label>
                        <div class="relative mt-2">
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:border-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                placeholder="Minimo 6 caracteres"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"
                                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                                @click="showPassword = !showPassword"
                            >
                                <svg v-if="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3l18 18" />
                                    <path d="M10.5 10.5a2.5 2.5 0 0 0 3 3" />
                                    <path d="M6.3 6.3C4.3 7.7 2.9 9.7 2 12c0 0 3.5 7 10 7 2.1 0 4-.5 5.6-1.3" />
                                    <path d="M9.9 5.1C10.6 5 11.3 5 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.3 3.4" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="text-xs font-semibold text-slate-500">Confirma a senha</label>
                        <div class="relative mt-2">
                            <input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirm ? 'text' : 'password'"
                                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-12 text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:border-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                                placeholder="Repete ela aqui"
                                required
                                autocomplete="new-password"
                            />
                            <button
                                type="button"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"
                                :aria-label="showConfirm ? 'Ocultar senha' : 'Mostrar senha'"
                                @click="showConfirm = !showConfirm"
                            >
                                <svg v-if="showConfirm" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                                <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 3l18 18" />
                                    <path d="M10.5 10.5a2.5 2.5 0 0 0 3 3" />
                                    <path d="M6.3 6.3C4.3 7.7 2.9 9.7 2 12c0 0 3.5 7 10 7 2.1 0 4-.5 5.6-1.3" />
                                    <path d="M9.9 5.1C10.6 5 11.3 5 12 5c6.5 0 10 7 10 7a17 17 0 0 1-2.3 3.4" />
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1" :message="form.errors.password_confirmation" />
                    </div>

                    <button
                        type="submit"
                        class="group flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-[var(--kitamo-accent)] text-sm font-semibold text-white shadow-[0_20px_35px_-22px_rgba(20,184,166,0.9)]"
                        :class="{ 'opacity-70': form.processing }"
                        :disabled="form.processing"
                    >
                        Salvar nova senha
                    </button>
                </form>
            </div>
        </div>
    </AuthShell>
</template>
