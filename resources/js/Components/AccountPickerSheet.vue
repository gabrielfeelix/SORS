<script setup lang="ts">
import { computed } from 'vue';
import PickerSheet from '@/Components/PickerSheet.vue';
import AccountIcon from '@/Components/AccountIcon.vue';

export type AccountOption = {
    key: string;
    label: string;
    subtitle?: string;
    balance?: number;
    limit?: number;
    used?: number;
    available?: number;
    tone?: 'purple' | 'amber' | 'emerald' | 'slate';
    customColor?: string;
    icon?: string;
    type?: 'bank' | 'wallet' | 'credit_card';
};

const props = defineProps<{
    open: boolean;
    options: AccountOption[];
    title?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', key: string): void;
}>();

const toneClass = (tone?: AccountOption['tone']) => {
    if (tone === 'purple') return 'bg-purple-100 text-purple-600';
    if (tone === 'amber') return 'bg-amber-100 text-amber-600';
    if (tone === 'emerald') return 'bg-emerald-100 text-emerald-600';
    return 'bg-slate-200 text-slate-600';
};

const formatBRL = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);

const isCreditCard = (opt: AccountOption) => opt.type === 'credit_card';

const saldoLabel = (opt: AccountOption) => {
    if (isCreditCard(opt)) {
        const available =
            opt.available ??
            (opt.limit != null ? Number(opt.limit) - Number(opt.used ?? 0) : null);
        if (available == null) return '';
        return `Disponível: ${formatBRL(Math.max(0, available))}`;
    }
    if (opt.balance == null) return '';
    return `Saldo: ${formatBRL(opt.balance)}`;
};

const bankAndWalletOptions = computed(() => props.options.filter((o) => o.type !== 'credit_card'));
const creditCardOptions = computed(() => props.options.filter((o) => o.type === 'credit_card'));
</script>

<template>
    <PickerSheet :open="open" :title="props.title ?? 'Pagar com'" @close="emit('close')">
        <div class="space-y-5 pb-2">
            <div v-if="bankAndWalletOptions.length">
                <div class="px-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">CONTAS E CARTEIRAS</div>
                <div class="mt-2 space-y-3">
                    <button
                        v-for="opt in bankAndWalletOptions"
                        :key="opt.key"
                        type="button"
                        class="flex w-full items-center gap-4 rounded-2xl bg-slate-50 px-4 py-4 text-left ring-1 ring-slate-200/70"
                        @click="emit('select', opt.key)"
                    >
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-full text-lg font-semibold"
                            :class="opt.customColor ? '' : toneClass(opt.tone)"
                            :style="opt.customColor ? { backgroundColor: opt.customColor, color: 'white' } : {}"
                        >
                            <AccountIcon :type="opt.type" :icon="opt.icon" class="h-6 w-6" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-base font-semibold text-slate-900">{{ opt.label }}</div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold">
                                <span v-if="opt.subtitle" class="truncate text-slate-400">{{ opt.subtitle }}</span>
                                <span v-if="opt.subtitle && saldoLabel(opt)" class="text-slate-300">•</span>
                                <span v-if="saldoLabel(opt)" class="truncate text-slate-500">{{ saldoLabel(opt) }}</span>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="creditCardOptions.length">
                <div class="px-2 text-[10px] font-bold uppercase tracking-wide text-slate-400">CARTÕES DE CRÉDITO</div>
                <div class="mt-2 space-y-3">
                    <button
                        v-for="opt in creditCardOptions"
                        :key="opt.key"
                        type="button"
                        class="flex w-full items-center gap-4 rounded-2xl bg-slate-50 px-4 py-4 text-left ring-1 ring-slate-200/70"
                        @click="emit('select', opt.key)"
                    >
                        <span
                            class="flex h-12 w-12 items-center justify-center rounded-full text-lg font-semibold"
                            :class="opt.customColor ? '' : toneClass(opt.tone)"
                            :style="opt.customColor ? { backgroundColor: opt.customColor, color: 'white' } : {}"
                        >
                            <AccountIcon :type="opt.type" :icon="opt.icon" class="h-6 w-6" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-base font-semibold text-slate-900">{{ opt.label }}</div>
                            <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold">
                                <span v-if="opt.subtitle" class="truncate text-slate-400">{{ opt.subtitle }}</span>
                                <span v-if="opt.subtitle && saldoLabel(opt)" class="text-slate-300">•</span>
                                <span v-if="saldoLabel(opt)" class="truncate text-slate-500">{{ saldoLabel(opt) }}</span>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </PickerSheet>
</template>
