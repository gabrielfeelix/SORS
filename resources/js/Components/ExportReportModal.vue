<script setup lang="ts">
import { computed, ref, watch } from 'vue';

type Format = 'pdf' | 'excel' | 'csv';

const props = defineProps<{
    open: boolean;
    defaultMonthKey?: string;
}>();

const emit = defineEmits<{
    (event: 'close'): void;
    (event: 'exported', payload: { channel: 'email' | 'download'; format: Format }): void;
}>();

const close = () => emit('close');

const format = ref<Format>('pdf');

const includeSummary = ref(true);
const includeCharts = ref(true);
const includeTransactions = ref(true);

const buildMonthKey = (date: Date) => `${date.getFullYear()}-${date.getMonth()}`;
const monthKey = ref(buildMonthKey(new Date()));
const monthPickerOpen = ref(false);

const months = computed(() => {
    const now = new Date();
    const items: Array<{ key: string; label: string; date: Date }> = [];
    for (let i = -120; i <= 120; i += 1) {
        const d = new Date(now.getFullYear(), now.getMonth() + i, 1);
        const label = new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(d);
        items.push({ key: `${d.getFullYear()}-${d.getMonth()}`, label: `${label.charAt(0).toUpperCase() + label.slice(1)}`, date: d });
    }
    return items;
});

const monthLabel = computed(() => months.value.find((m) => m.key === monthKey.value)?.label ?? '');

const toISODate = (date: Date) => {
    const yyyy = String(date.getFullYear());
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};

const rangeForMonthKey = (key: string) => {
    const [yyyyRaw, mmRaw] = String(key).split('-');
    const yyyy = Number(yyyyRaw);
    const mm0 = Number(mmRaw);
    if (!Number.isFinite(yyyy) || !Number.isFinite(mm0)) {
        const now = new Date();
        const start = new Date(now.getFullYear(), now.getMonth(), 1);
        const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
        return { startISO: toISODate(start), endISO: toISODate(end) };
    }
    const start = new Date(yyyy, mm0, 1);
    const end = new Date(yyyy, mm0 + 1, 0);
    return { startISO: toISODate(start), endISO: toISODate(end) };
};

const fileName = computed(() => {
    const safeMonth = (monthLabel.value || 'Relatorio').replace(/\s+/g, '_');
    const ext = format.value === 'excel' ? 'xlsx' : format.value;
    return `Relatório_${safeMonth}.${ext}`;
});

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;
        monthKey.value = props.defaultMonthKey && months.value.some((m) => m.key === props.defaultMonthKey) ? props.defaultMonthKey : buildMonthKey(new Date());
        format.value = 'pdf';
        includeSummary.value = true;
        includeCharts.value = true;
        includeTransactions.value = true;
        monthPickerOpen.value = false;
    },
);

const formatCardClass = (value: Format) =>
    format.value === value ? 'border-[#14B8A6] bg-[#E6FFFB]' : 'border-slate-200 bg-white';

const selectFormat = (value: Format) => (format.value = value);

const exportBusy = ref(false);
const getCookie = (name: string) => {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length < 2) return '';
    return parts.pop()!.split(';').shift() ?? '';
};

const xsrfToken = () => {
    const raw = getCookie('XSRF-TOKEN');
    if (!raw) return '';
    try {
        return decodeURIComponent(raw);
    } catch {
        return raw;
    }
};

const exportReport = async (channel: 'download' | 'email') => {
    if (channel === 'email') {
        window.alert('Em breve: envio por email.');
        return;
    }

    exportBusy.value = true;
    try {
        const { startISO, endISO } = rangeForMonthKey(monthKey.value);
        const xsrf = xsrfToken();
        const response = await fetch(route('api.relatorios.exportar'), {
            method: 'POST',
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/octet-stream',
                'X-Requested-With': 'XMLHttpRequest',
                ...(xsrf ? { 'X-CSRF-TOKEN': xsrf, 'X-XSRF-TOKEN': xsrf } : {}),
            },
            body: JSON.stringify({
                formato: format.value,
                periodo_inicio: startISO,
                periodo_fim: endISO,
                incluir_resumo: includeSummary.value,
                incluir_graficos: includeCharts.value,
                incluir_transacoes: includeTransactions.value,
            }),
        });
        if (!response.ok) {
            throw new Error('Falha ao exportar relatório');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        const disposition = response.headers.get('content-disposition') ?? '';
        const filenameMatch = disposition.match(/filename=\"?([^\";]+)\"?/i);
        a.href = url;
        a.download = filenameMatch?.[1] ?? fileName.value;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        emit('exported', { channel: 'download', format: format.value });
        close();
    } catch {
        window.alert('Não foi possível exportar o relatório.');
    } finally {
        exportBusy.value = false;
    }
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-[85] bg-white">
        <header class="flex items-center justify-between px-5 pb-4 pt-[calc(1rem+env(safe-area-inset-top))]">
            <div class="text-xl font-semibold tracking-tight text-slate-900">Exportar relatório</div>
            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600" aria-label="Fechar" @click="close">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18" />
                    <path d="M6 6l12 12" />
                </svg>
            </button>
        </header>

        <main class="mx-auto w-full max-w-md px-5 pb-[calc(7rem+env(safe-area-inset-bottom))]">
            <div class="mt-2 space-y-6">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Período do relatório</div>
                    <button
                        type="button"
                        class="mt-3 flex h-12 w-full items-center justify-between rounded-2xl bg-white px-4 shadow-sm ring-1 ring-slate-200/60"
                        @click="monthPickerOpen = true"
                    >
                        <div class="text-sm font-semibold text-slate-900">{{ monthLabel }}</div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>
                </div>

                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Formato</div>

                    <div class="mt-3 space-y-3">
                        <button type="button" class="flex w-full items-center justify-between rounded-2xl border p-4 text-left" :class="formatCardClass('pdf')" @click="selectFormat('pdf')">
                            <div class="flex items-center gap-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-red-500 ring-1 ring-slate-200/60">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <path d="M14 2v6h6" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">PDF</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">Relatório visual com gráficos</div>
                                </div>
                            </div>
                            <span class="flex h-5 w-5 items-center justify-center rounded-full border" :class="format === 'pdf' ? 'border-[#14B8A6] bg-[#14B8A6]' : 'border-slate-200 bg-white'">
                                <span v-if="format === 'pdf'" class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            </span>
                        </button>

                        <button type="button" class="flex w-full items-center justify-between rounded-2xl border p-4 text-left" :class="formatCardClass('excel')" @click="selectFormat('excel')">
                            <div class="flex items-center gap-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-emerald-600 ring-1 ring-slate-200/60">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <path d="M14 2v6h6" />
                                        <path d="M8 13l4 4" />
                                        <path d="M12 13l-4 4" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Excel</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">Planilha editável (.xlsx)</div>
                                </div>
                            </div>
                            <span class="flex h-5 w-5 items-center justify-center rounded-full border" :class="format === 'excel' ? 'border-[#14B8A6] bg-[#14B8A6]' : 'border-slate-200 bg-white'">
                                <span v-if="format === 'excel'" class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            </span>
                        </button>

                        <button type="button" class="flex w-full items-center justify-between rounded-2xl border p-4 text-left" :class="formatCardClass('csv')" @click="selectFormat('csv')">
                            <div class="flex items-center gap-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-600 ring-1 ring-slate-200/60">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <path d="M14 2v6h6" />
                                        <path d="M8 13h8" />
                                        <path d="M8 17h6" />
                                    </svg>
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">CSV</div>
                                    <div class="mt-1 text-xs font-semibold text-slate-400">Lista simples de transações</div>
                                </div>
                            </div>
                            <span class="flex h-5 w-5 items-center justify-center rounded-full border" :class="format === 'csv' ? 'border-[#14B8A6] bg-[#14B8A6]' : 'border-slate-200 bg-white'">
                                <span v-if="format === 'csv'" class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            </span>
                        </button>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-400">Incluir no relatório</div>
                    <div class="mt-3 space-y-3">
                        <label class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm ring-1 ring-slate-200/60">
                            <input v-model="includeSummary" type="checkbox" class="h-5 w-5 rounded-md border-slate-300 text-blue-600" />
                            <div class="text-sm font-semibold text-slate-700">Resumo financeiro</div>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm ring-1 ring-slate-200/60">
                            <input v-model="includeCharts" type="checkbox" class="h-5 w-5 rounded-md border-slate-300 text-blue-600" />
                            <div class="text-sm font-semibold text-slate-700">Gráficos</div>
                        </label>
                        <label class="flex items-center gap-3 rounded-2xl bg-white px-4 py-4 shadow-sm ring-1 ring-slate-200/60">
                            <input v-model="includeTransactions" type="checkbox" class="h-5 w-5 rounded-md border-slate-300 text-blue-600" />
                            <div class="text-sm font-semibold text-slate-700">Lista de transações</div>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/60">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 ring-1 ring-slate-200/60">
                            <div class="text-[10px] font-bold uppercase">{{ format === 'excel' ? 'XLSX' : format }}</div>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-semibold text-slate-900">{{ fileName }}</div>
                            <div class="mt-1 text-xs font-semibold text-slate-400">~2 MB</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="fixed inset-x-0 bottom-0 bg-white px-5 pb-[calc(1rem+env(safe-area-inset-bottom))] pt-3 shadow-[0_-18px_40px_-32px_rgba(15,23,42,0.45)]">
            <div class="mx-auto flex w-full max-w-md gap-3">
                <button
                    type="button"
                    class="flex h-[52px] flex-1 items-center justify-center gap-2 rounded-2xl border border-[#14B8A6] bg-white text-sm font-semibold text-[#14B8A6]"
                    :disabled="exportBusy"
                    :class="exportBusy ? 'opacity-60' : ''"
                    @click="exportReport('email')"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16v12H4V6Z" />
                        <path d="m4 7 8 6 8-6" />
                    </svg>
                    E-mail
                </button>
                <button
                    type="button"
                    class="flex h-[52px] flex-1 items-center justify-center gap-2 rounded-2xl bg-[#14B8A6] text-sm font-semibold text-white shadow-[0_2px_8px_rgba(20,184,166,0.25)]"
                    :disabled="exportBusy"
                    :class="exportBusy ? 'opacity-60' : ''"
                    @click="exportReport('download')"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3v12" />
                        <path d="M8 11l4 4 4-4" />
                        <path d="M4 21h16" />
                    </svg>
                    {{ exportBusy ? 'Gerando…' : 'Baixar' }}
                </button>
            </div>
        </footer>

        <div v-if="monthPickerOpen" class="fixed inset-0 z-[90]">
            <button class="absolute inset-0 bg-black/50 backdrop-blur-sm" type="button" @click="monthPickerOpen = false" aria-label="Fechar"></button>
            <div class="absolute inset-x-0 bottom-0 max-h-[70vh] w-full overflow-hidden rounded-t-[24px] bg-white shadow-[0_-4px_20px_rgba(0,0,0,0.25)]">
                <div class="flex items-center justify-between px-5 pb-3 pt-4">
                    <div class="text-sm font-bold text-slate-900">Escolha o mês</div>
                    <button type="button" class="text-sm font-semibold text-[#14B8A6]" @click="monthPickerOpen = false">Fechar</button>
                </div>
                <div class="max-h-[calc(70vh-56px)] overflow-y-auto px-3 pb-4">
                    <button
                        v-for="m in months"
                        :key="m.key"
                        type="button"
                        class="flex w-full items-center justify-between rounded-2xl px-4 py-3 text-left text-sm font-semibold"
                        :class="m.key === monthKey ? 'bg-[#E6FFFB] text-[#14B8A6]' : 'text-slate-700 hover:bg-slate-50'"
                        @click="() => { monthKey = m.key; monthPickerOpen = false; }"
                    >
                        <span>{{ m.label }}</span>
                        <svg v-if="m.key === monthKey" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <path d="M20 6 9 17l-5-5" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
