<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import AuthShell from '@/Layouts/AuthShell.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const step = ref<'email' | 'code'>(props.status ? 'code' : 'email');

const codeDigits = ref(['', '', '', '']);
const codeRefs = ref<Array<HTMLInputElement | null>>([]);
const focusedIndex = ref<number | null>(null);
const codeComplete = computed(() => codeDigits.value.every((digit) => digit.length > 0));

const resendCountdown = ref(30);
const resendActive = ref(false);
const resending = ref(false);
let intervalId: number | null = null;

const toast = ref<{ title: string; message: string; tone: 'info' | 'success' } | null>(null);
let toastTimer: number | null = null;

const showToast = (title: string, message: string, tone: 'info' | 'success' = 'info') => {
    toast.value = { title, message, tone };
    if (toastTimer) window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => {
        toast.value = null;
    }, 2000);
};

const startCountdown = (seconds = 30) => {
    resendCountdown.value = seconds;
    resendActive.value = true;

    if (intervalId) window.clearInterval(intervalId);
    intervalId = window.setInterval(() => {
        if (resendCountdown.value <= 1) {
            resendCountdown.value = 0;
            resendActive.value = false;
            if (intervalId) window.clearInterval(intervalId);
            intervalId = null;
            return;
        }
        resendCountdown.value -= 1;
    }, 1000);
};

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 'code';
            startCountdown();
        },
    });
};

const resendCode = () => {
    if (resendActive.value) {
        showToast('Calma, respira!', `Espera mais ${resendCountdown.value} segundos pra pedir outro.`, 'info');
        return;
    }

    if (!form.email) {
        showToast('Ops!', 'Preenche teu e-mail pra reenviar.', 'info');
        return;
    }

    resending.value = true;
    form.post(route('password.email'), {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 'code';
            showToast('Enviado!', 'Codigo novo chegou no teu e-mail.', 'success');
            startCountdown();
        },
        onFinish: () => {
            resending.value = false;
        },
    });
};

const setCodeRef = (el: HTMLInputElement | null, idx: number) => {
    codeRefs.value[idx] = el;
};

const onCodeInput = (event: Event, idx: number) => {
    const target = event.target as HTMLInputElement;
    const value = target.value.replace(/\D/g, '');
    if (!value) {
        codeDigits.value[idx] = '';
        return;
    }
    const digit = value[value.length - 1];
    codeDigits.value[idx] = digit;
    if (idx < codeDigits.value.length - 1) {
        codeRefs.value[idx + 1]?.focus();
    }
};

const onCodeKeydown = (event: KeyboardEvent, idx: number) => {
    if (event.key === 'Backspace' && !codeDigits.value[idx] && idx > 0) {
        codeRefs.value[idx - 1]?.focus();
    }
};

const onCodePaste = (event: ClipboardEvent) => {
    const data = event.clipboardData?.getData('text').replace(/\D/g, '') ?? '';
    if (!data) return;
    event.preventDefault();
    const digits = data.slice(0, 4).split('');
    codeDigits.value = ['','','',''].map((_, idx) => digits[idx] ?? '');
    const lastIndex = Math.min(digits.length, 4) - 1;
    if (lastIndex >= 0) {
        codeRefs.value[lastIndex]?.focus();
    }
};

const iconTone = computed(() =>
    step.value === 'code' ? 'bg-amber-50 text-amber-500' : 'bg-emerald-50 text-emerald-500',
);

watch(
    () => props.status,
    (value) => {
        if (value) {
            step.value = 'code';
            startCountdown();
        }
    },
);

onMounted(() => {
    if (props.status) {
        startCountdown();
    }
});

onBeforeUnmount(() => {
    if (intervalId) window.clearInterval(intervalId);
    if (toastTimer) window.clearTimeout(toastTimer);
});
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

        <Head title="Esqueceu a senha" />

        <div class="flex flex-1 flex-col justify-center lg:justify-start lg:pt-8">
            <div class="relative mx-auto w-full max-w-md lg:mx-0">
                <div
                    v-if="toast"
                    class="absolute left-1/2 top-0 z-20 w-[92%] -translate-x-1/2 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-[0_18px_35px_-25px_rgba(15,23,42,0.9)] lg:left-0 lg:translate-x-0"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            :class="toast.tone === 'success' ? 'bg-emerald-500' : 'bg-emerald-600'"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 8v4" />
                                <path d="M12 16h.01" />
                                <circle cx="12" cy="12" r="9" />
                            </svg>
                        </span>
                        <div>
                            <div class="text-sm font-semibold">{{ toast.title }}</div>
                            <div class="text-xs text-slate-200">{{ toast.message }}</div>
                        </div>
                    </div>
                </div>

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
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl" :class="iconTone">
                        <svg v-if="step === 'email'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="10" cy="14" r="6" />
                            <path d="M14.5 9.5L18 6" />
                            <path d="M18 6h-4" />
                            <path d="M18 6v4" />
                        </svg>
                        <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16v12H4z" />
                            <path d="m22 6-10 7L2 6" />
                        </svg>
                    </div>

                    <h1 class="mt-4 text-2xl font-semibold text-slate-900">
                        {{ step === 'email' ? 'Esqueceu a senha?' : 'Checa teu e-mail!' }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-500">
                        <span v-if="step === 'email'">
                            <span class="lg:hidden">Relaxa, acontece nas melhores familias. Manda teu e-mail ai que a gente resolve.</span>
                            <span class="hidden lg:inline">Sem stress. Manda teu e-mail cadastrado ai.</span>
                        </span>
                        <span v-else>
                            <span class="lg:hidden">Mandamos um codigo de 4 digitos. Digita ele aqui pra gente saber que e voce mesmo.</span>
                            <span class="hidden lg:inline">Mandamos um codigo de 4 digitos pra la.</span>
                        </span>
                    </p>
                </div>

                <form v-if="step === 'email'" class="mt-8 space-y-5" @submit.prevent="submit">
                    <div>
                        <label for="email" class="text-xs font-semibold text-slate-500">
                            <span class="lg:hidden">E-mail cadastrado</span>
                            <span class="hidden lg:inline">E-mail de acesso</span>
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 placeholder:text-slate-400 focus:border-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            placeholder="ex: gabriel@email.com"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <button
                        type="submit"
                        class="group flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-[var(--kitamo-accent)] text-sm font-semibold text-white shadow-[0_20px_35px_-22px_rgba(20,184,166,0.9)]"
                        :class="{ 'opacity-70': form.processing }"
                        :disabled="form.processing"
                    >
                        Enviar codigo
                        <span class="flex items-center gap-2 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14" />
                                <path d="M13 5l6 7-6 7" />
                            </svg>
                        </span>
                    </button>
                </form>

                <div v-else class="mt-8 space-y-6">
                    <div class="flex items-center justify-center gap-3 lg:justify-start">
                        <input
                            v-for="(digit, idx) in codeDigits"
                            :key="idx"
                            :ref="(el) => setCodeRef(el as HTMLInputElement, idx)"
                            v-model="codeDigits[idx]"
                            inputmode="numeric"
                            maxlength="1"
                            class="h-14 w-14 rounded-2xl border border-slate-200 bg-slate-50 text-center text-lg font-semibold text-slate-800 focus:border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                            :class="focusedIndex === idx ? 'border-emerald-400' : ''"
                            @focus="focusedIndex = idx"
                            @blur="focusedIndex = null"
                            @input="onCodeInput($event, idx)"
                            @keydown="onCodeKeydown($event, idx)"
                            @paste="onCodePaste"
                        />
                    </div>

                    <div class="text-center text-sm text-slate-400 lg:text-left">
                        Nao chegou?
                        <button
                            type="button"
                            class="ml-1 font-semibold"
                            :class="resendActive ? 'text-slate-400' : 'text-emerald-600'"
                            @click="resendCode"
                        >
                            <span v-if="resending">Enviando...</span>
                            <span v-else>
                                Reenviar<span v-if="resendActive"> ({{ resendCountdown }}s)</span>
                            </span>
                        </button>
                    </div>

                    <button
                        type="button"
                        class="group flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-[var(--kitamo-accent)] text-sm font-semibold text-white shadow-[0_20px_35px_-22px_rgba(20,184,166,0.9)]"
                        :class="{ 'opacity-60': !codeComplete }"
                        :disabled="!codeComplete"
                    >
                        Validar codigo
                    </button>
                </div>
            </div>
        </div>
    </AuthShell>
</template>
