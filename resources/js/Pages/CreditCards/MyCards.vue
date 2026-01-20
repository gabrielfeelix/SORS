<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { BootstrapData } from '@/types/kitamo';
import MobileShell from '@/Layouts/MobileShell.vue';
import KitamoLayout from '@/Layouts/KitamoLayout.vue';
import { useIsMobile } from '@/composables/useIsMobile';

const isMobile = useIsMobile();
const page = usePage();

const bootstrap = computed(
    () => (page.props.bootstrap ?? { entries: [], goals: [], accounts: [], categories: [] }) as BootstrapData,
);

const activeMonth = ref(new Date());
const monthLabel = computed(() => {
    return new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' })
        .format(activeMonth.value)
        .toUpperCase();
});

const shiftMonth = (delta: number) => {
    const d = new Date(activeMonth.value);
    d.setMonth(d.getMonth() + delta);
    activeMonth.value = d;
};

const formatBRL = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2 }).format(value);

const formatPercentage = (value: number) => {
    return value.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '%';
};

const creditCards = computed(() =>
    (bootstrap.value.accounts ?? [])
        .filter((a) => a.type === 'credit_card')
        .map((a) => {
            const limite = Number(a.credit_limit ?? 0);
            const usado = Math.max(0, Number(a.current_balance ?? 0));
            const percentualUsado = limite > 0 ? (usado / limite) * 100 : 0;
            const disponivel = limite - usado;

            let status = 'PAGA';
            if (usado >= limite) status = 'ATRASADA';
            else if (usado > 0) status = 'ABERTA';

            return {
                id: a.id,
                nome: a.name,
                cor: (a as any).color ?? '#8B5CF6',
                limite,
                usado,
                disponivel: Math.max(0, disponivel),
                percentualUsado,
                status,
            };
        }),
);

const devedaConsolidada = computed(() => {
    return creditCards.value.reduce((sum, card) => sum + card.usado, 0);
});

const limiteConsolidado = computed(() => {
    return creditCards.value.reduce((sum, card) => sum + card.limite, 0);
});

const percentualUsoConsolidado = computed(() => {
    if (limiteConsolidado.value === 0) return 0;
    return (devedaConsolidada.value / limiteConsolidado.value) * 100;
});

const disponivelConsolidado = computed(() => {
    return Math.max(0, limiteConsolidado.value - devedaConsolidada.value);
});

const getStatusColor = (status: string) => {
    if (status === 'PAGA') return 'text-emerald-600';
    if (status === 'ATRASADA') return 'text-red-500';
    return 'text-amber-500';
};
</script>

<template>
    <Head title="Meus Cartões" />

    <MobileShell v-if="isMobile" :show-nav="false">
        <!-- Header -->
        <header class="flex items-center justify-between px-4 pt-2 pb-4">
            <Link
                :href="route('dashboard')"
                class="text-slate-900"
                aria-label="Voltar"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </Link>

            <div class="text-sm font-bold tracking-wider text-slate-900">MEUS CARTÕES</div>

            <Link
                :href="route('dashboard')"
                class="text-slate-900"
                aria-label="Adicionar cartão"
            >
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
            </Link>
        </header>

        <!-- Month Navigation -->
        <div class="px-4 pb-4">
            <div class="flex items-center justify-between">
                <button
                    type="button"
                    class="text-slate-400 hover:text-slate-600"
                    @click="shiftMonth(-1)"
                    aria-label="Mês anterior"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div class="text-xs font-semibold tracking-wider text-slate-900">
                    {{ monthLabel }}
                </div>
                <button
                    type="button"
                    class="text-slate-400 hover:text-slate-600"
                    @click="shiftMonth(1)"
                    aria-label="Próximo mês"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Dívida Consolidada Card -->
        <div class="px-4">
            <div class="rounded-3xl bg-[#1E293B] p-5 shadow-xl">
                <div class="flex items-start justify-between">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-[#64748B]">
                        Dívida Consolidada
                    </div>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/10"
                        aria-label="Opções"
                    >
                        <svg class="h-4 w-4 text-white/60" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="5" r="1.5" />
                            <circle cx="12" cy="12" r="1.5" />
                            <circle cx="12" cy="19" r="1.5" />
                        </svg>
                    </button>
                </div>

                <div class="mt-2 text-[32px] font-bold leading-none text-white">
                    {{ formatBRL(devedaConsolidada) }}
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <!-- Uso de Crédito -->
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-[#64748B]">
                            Uso de Crédito
                        </div>
                        <div class="mt-1 text-lg font-bold text-[#14B8A6]">
                            {{ formatPercentage(percentualUsoConsolidado) }}
                        </div>
                        <!-- Progress bar -->
                        <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-[#334155]">
                            <div
                                class="h-full bg-[#14B8A6]"
                                :style="{ width: `${Math.min(100, percentualUsoConsolidado)}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Disponível Consolidado -->
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-[#64748B]">
                            Disp. Consolidado
                        </div>
                        <div class="mt-1 text-lg font-bold text-white">
                            {{ formatBRL(disponivelConsolidado) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartões List -->
        <div class="mt-6 px-4">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-900">
                    Cartões
                </div>
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    {{ creditCards.length }} UNIDADES
                </div>
            </div>

            <!-- Cards -->
            <div v-if="creditCards.length" class="mt-4 space-y-4 pb-8">
                <Link
                    v-for="card in creditCards"
                    :key="card.id"
                    :href="route('credit-cards.show', { account: card.id })"
                    class="block overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200"
                >
                    <!-- Card Header with Icon -->
                    <div class="flex items-start gap-3 p-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl"
                            :style="{ backgroundColor: card.cor }"
                        >
                            <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <line x1="2" y1="10" x2="22" y2="10" />
                            </svg>
                        </div>

                        <div class="flex-1">
                            <div class="text-base font-bold text-slate-900">{{ card.nome }}</div>
                            <div class="mt-0.5 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                {{ card.nome.toUpperCase() }}
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-base font-bold text-slate-900">{{ formatBRL(card.usado) }}</div>
                            <div
                                class="mt-1 text-xs font-bold uppercase tracking-wide"
                                :class="getStatusColor(card.status)"
                            >
                                {{ card.status }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Stats -->
                    <div class="bg-slate-50 px-4 pb-4 pt-2">
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Limite Usado: {{ Math.round(card.percentualUsado) }}%
                        </div>

                        <!-- Progress Bar -->
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-full"
                                :class="card.percentualUsado >= 80 ? 'bg-red-500' : card.percentualUsado >= 50 ? 'bg-amber-500' : 'bg-emerald-500'"
                                :style="{ width: `${Math.min(100, card.percentualUsado)}%` }"
                            ></div>
                        </div>

                        <div class="mt-2 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-600">Disp: {{ formatBRL(card.disponivel) }}</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div
                v-else
                class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center"
            >
                <div class="text-sm font-semibold text-slate-900">Nenhum cartão cadastrado</div>
                <div class="mt-1 text-xs text-slate-500">Adicione um cartão para começar</div>
            </div>
        </div>
    </MobileShell>

    <KitamoLayout v-else title="Meus Cartões" subtitle="Gerencie seus cartões de crédito">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
            <div class="text-sm font-semibold text-slate-900">Abra no mobile para ver o layout completo.</div>
        </div>
    </KitamoLayout>
</template>
