<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps<{
    open: boolean;
    modelValue: string; // dd/mm/aaaa
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'update:modelValue', value: string): void;
    (e: 'select-today'): void;
}>();

const selected = ref<Date | null>(null);

const toBR = (date: Date) =>
    new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);

const brToDate = (value: string) => {
    const match = String(value ?? '').trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if (!match) return null;
    const [, dd, mm, yyyy] = match;
    const date = new Date(Number(yyyy), Number(mm) - 1, Number(dd));
    return Number.isFinite(date.getTime()) ? date : null;
};

const monthLabel = computed(() => {
    if (!selected.value) return '';
    return new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(selected.value);
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;
        selected.value = brToDate(props.modelValue) ?? new Date();
    },
);

const pick = (date: Date | null) => {
    if (!date) return;
    selected.value = date;
    emit('update:modelValue', toBR(date));
};

const selectYesterday = () => {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    selected.value = d;
    emit('update:modelValue', toBR(d));
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-[80]">
                <button class="absolute inset-0 bg-black/40 backdrop-blur-sm" type="button" @click="emit('close')" aria-label="Fechar"></button>

                <Transition
                    enter-active-class="transition duration-250 ease-out"
                    enter-from-class="translate-y-8 opacity-0"
                    enter-to-class="translate-y-0 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 opacity-100"
                    leave-to-class="translate-y-8 opacity-0"
                >
                    <div
                        v-if="open"
                        class="absolute inset-x-0 bottom-0 mx-auto w-full max-w-md rounded-t-[28px] bg-white px-5 pb-[calc(18px+env(safe-area-inset-bottom))] pt-3 shadow-[0_-18px_60px_-40px_rgba(15,23,42,0.55)]"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="mx-auto h-1.5 w-12 rounded-full bg-slate-200"></div>

                        <div class="relative mt-4 flex items-center justify-center">
                            <div class="text-lg font-semibold text-slate-900">Quando foi?</div>
                            <button
                                type="button"
                                class="absolute right-0 flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 text-slate-500"
                                aria-label="Fechar"
                                @click="emit('close')"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 6L6 18" />
                                    <path d="M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                class="h-12 rounded-2xl text-sm font-semibold shadow-sm ring-1 ring-slate-200/60"
                                :class="!props.modelValue ? 'bg-[#14B8A6] text-white ring-transparent' : 'bg-slate-50 text-slate-700'"
                                @click="emit('select-today')"
                            >
                                Hoje
                            </button>
                            <button
                                type="button"
                                class="h-12 rounded-2xl bg-slate-50 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200/60"
                                @click="selectYesterday"
                            >
                                Ontem
                            </button>
                        </div>

                        <div class="mt-6 rounded-2xl bg-white ring-1 ring-slate-200/60">
                            <div class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-slate-700">
                                <div class="capitalize">{{ monthLabel }}</div>
                            </div>

                            <VueDatePicker
                                :model-value="selected"
                                @update:model-value="pick"
                                :inline="true"
                                :enable-time-picker="false"
                                :auto-apply="true"
                                :week-start="0"
                                locale="pt-BR"
                                :hide-navigation="false"
                            />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* suaviza o visual do datepicker para combinar com o app */
:deep(.dp__menu_inner) {
    border: 0;
}
:deep(.dp__calendar_header) {
    color: #94a3b8;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    font-size: 11px;
}
:deep(.dp__active_date),
:deep(.dp__range_start),
:deep(.dp__range_end) {
    background: #14b8a6;
}
:deep(.dp__today) {
    border-color: #14b8a6;
}
</style>

