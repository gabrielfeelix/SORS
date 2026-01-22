<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { requestJson } from '@/lib/kitamoApi';
import type { Goal } from '@/types/kitamo';
import MobileShell from '@/Layouts/MobileShell.vue';
import DesktopShell from '@/Layouts/DesktopShell.vue';
import { useIsMobile } from '@/composables/useIsMobile';
import { formatMoneyInputCentsShift, moneyInputToNumber } from '@/lib/moneyInput';
import { preventNonDigitKeydown } from '@/lib/inputGuards';

const isMobile = useIsMobile();
const Shell = computed(() => (isMobile.value ? MobileShell : DesktopShell));
const shellProps = computed(() =>
    isMobile.value ? { showNav: false } : { title: 'Nova meta', showSearch: false, showNewAction: false },
);

type IconKey = 'home' | 'car' | 'plane' | 'cap' | 'bag' | 'laptop';

const name = ref('');
const icon = ref<IconKey>('home');
const target = ref('');
const due = ref('Dezembro 2026');

const onTargetInput = (event: Event) => {
    const targetEl = event.target as HTMLInputElement;
    target.value = formatMoneyInputCentsShift(targetEl.value);
};

const targetNumber = computed(() => moneyInputToNumber(target.value));
const monthly = computed(() => {
    const dueIso = parseDueDate(due.value);
    if (!dueIso) return 0;
    if (!targetNumber.value) return 0;

    const [y, m] = dueIso.split('-').map((p) => Number(p));
    const dueDate = new Date(y, (m ?? 1) - 1, 1);
    const now = new Date();
    const months = (dueDate.getFullYear() - now.getFullYear()) * 12 + (dueDate.getMonth() - now.getMonth()) + 1;
    const monthsLeft = Math.max(1, months);

    const remaining = Math.max(0, targetNumber.value);
    return Math.ceil(remaining / monthsLeft);
});

const mapIcon = (key: IconKey) => {
    if (key === 'plane') return 'plane';
    if (key === 'car') return 'car';
    return 'home';
};

const parseDueDate = (label: string) => {
    const value = label.trim().toLowerCase();
    if (!value) return null;
    const monthMap: Record<string, number> = {
        jan: 1, janeiro: 1,
        fev: 2, fevereiro: 2,
        mar: 3, março: 3, marco: 3,
        abr: 4, abril: 4,
        mai: 5, maio: 5,
        jun: 6, junho: 6,
        jul: 7, julho: 7,
        ago: 8, agosto: 8,
        set: 9, setembro: 9,
        out: 10, outubro: 10,
        nov: 11, novembro: 11,
        dez: 12, dezembro: 12,
    };
    const parts = value.split(/\s+/);
    const year = parts.find((part) => part.match(/^\d{4}$/));
    const monthKey = parts.find((part) => monthMap[part]);
    if (!year || !monthKey) return null;
    const month = monthMap[monthKey];
    return `${year}-${String(month).padStart(2, '0')}-01`;
};

const submit = async () => {
    const dueDate = parseDueDate(due.value);
    const response = await requestJson<{ goal: Goal }>(route('goals.store'), {
        method: 'POST',
        body: JSON.stringify({
            title: name.value.trim() || 'Nova meta',
            target_amount: targetNumber.value,
            due_date: dueDate,
            icon: mapIcon(icon.value),
        }),
    });
    router.visit(route('goals.show', { goalId: response.goal.id }));
};
</script>

<template>
    <Head title="Nova meta" />

    <component :is="Shell" v-bind="shellProps">
        <header v-if="isMobile" class="relative flex items-center justify-center pt-2">
            <Link
                :href="route('goals.index')"
                class="absolute left-0 flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200/60"
                aria-label="Voltar"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </Link>
            <div class="text-xl font-semibold tracking-tight text-slate-900">Nova meta</div>
        </header>

        <div class="mt-8 space-y-5 pb-[calc(6rem+env(safe-area-inset-bottom))] md:mx-auto md:max-w-md md:rounded-3xl md:bg-white md:p-6 md:pb-6 md:shadow-sm md:ring-1 md:ring-slate-200/60">
            <div>
                <div class="mb-2 text-sm font-semibold text-slate-700">Nome da meta</div>
                <input
                    v-model="name"
                    type="text"
                    placeholder="Ex: Casa própria..."
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 placeholder:text-slate-300 focus:border-[#14B8A6] focus:outline-none focus:ring-0"
                />
            </div>

            <div>
                <div class="mb-2 text-sm font-semibold text-slate-700">Ícone</div>
                <div class="flex flex-wrap gap-3">
                    <button
                        v-for="k in (['home','car','plane','cap','bag','laptop'] as IconKey[])"
                        :key="k"
                        type="button"
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 ring-1 ring-slate-200"
                        :class="icon === k ? 'ring-2 ring-[#14B8A6] bg-emerald-50 text-[#14B8A6]' : 'text-slate-500'"
                        @click="icon = k"
                        :aria-label="k"
                    >
                        <svg v-if="k === 'home'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 10.5L12 3l9 7.5" />
                            <path d="M5 10v10h14V10" />
                        </svg>
                        <svg v-else-if="k === 'car'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 16l1-5 1-3h10l1 3 1 5" />
                            <path d="M7 16h10" />
                            <circle cx="8" cy="17" r="1.5" />
                            <circle cx="16" cy="17" r="1.5" />
                        </svg>
                        <svg v-else-if="k === 'plane'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M2 16l20-8-20-8 6 8-6 8Z" />
                            <path d="M6 16v6l4-4" />
                        </svg>
                        <svg v-else-if="k === 'cap'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10 12 5 2 10l10 5 10-5Z" />
                            <path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5" />
                        </svg>
                        <svg v-else-if="k === 'bag'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 8h12l-1 13H7L6 8Z" />
                            <path d="M9 8V6a3 3 0 0 1 6 0v2" />
                        </svg>
                        <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="12" rx="2" />
                            <path d="M8 20h8" />
                            <path d="M12 16v4" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <div class="mb-2 text-sm font-semibold text-slate-700">Valor objetivo</div>
                <input
                    :value="target"
                    @input="onTargetInput"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    type="text"
                    placeholder="0,00"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 focus:border-[#14B8A6] focus:outline-none focus:ring-0"
                    @keydown="preventNonDigitKeydown"
                />
            </div>

            <div>
                <div class="mb-2 text-sm font-semibold text-slate-700">Prazo</div>
                <input
                    v-model="due"
                    type="text"
                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 focus:border-[#14B8A6] focus:outline-none focus:ring-0"
                />
            </div>

            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm font-semibold text-blue-700">
                <div class="flex items-start gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 10v6" />
                            <path d="M12 8h.01" />
                        </svg>
                    </span>
                    <div>R$ {{ monthly }},00/mês para atingir a meta no prazo.</div>
                </div>
            </div>
        </div>

        <div class="fixed inset-x-0 bottom-0 bg-white px-5 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-3 shadow-[0_-18px_40px_-32px_rgba(15,23,42,0.45)] md:static md:bg-transparent md:px-0 md:pb-0 md:pt-6 md:shadow-none">
            <div class="mx-auto w-full max-w-md md:mx-auto">
                <button
                    type="button"
                    class="h-[52px] w-full rounded-2xl bg-[#14B8A6] text-base font-bold text-white shadow-[0_2px_8px_rgba(20,184,166,0.25)]"
                    @click="submit"
                >
                    Criar meta
                </button>
            </div>
        </div>
    </component>
</template>
