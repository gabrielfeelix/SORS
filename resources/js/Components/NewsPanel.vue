<script setup lang="ts">
import { computed, ref, watch } from 'vue';

export type NewsItemRow = {
    id: number;
    title: string;
    category: string | null;
    type: 'new' | 'improvement' | 'fix' | 'announcement' | string;
    published_at: string | null;
    content: string | null;
    image_url: string | null;
    cta_text: string | null;
    cta_url: string | null;
};

const props = defineProps<{
    open: boolean;
    loading?: boolean;
    items: NewsItemRow[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const close = () => emit('close');

const activeFilter = ref<'all' | 'new' | 'improvement' | 'fix' | 'announcement'>('all');

watch(
    () => props.open,
    (open) => {
        if (!open) activeFilter.value = 'all';
    },
);

const filteredItems = computed(() => {
    if (activeFilter.value === 'all') return props.items;
    return props.items.filter((i) => i.type === activeFilter.value);
});

const badgeForType = (type: string) => {
    if (type === 'fix') return { label: 'Correção', cls: 'bg-red-50 text-red-700' };
    if (type === 'improvement') return { label: 'Melhoria', cls: 'bg-blue-50 text-blue-700' };
    if (type === 'announcement') return { label: 'Aviso', cls: 'bg-amber-50 text-amber-700' };
    return { label: 'Novo', cls: 'bg-emerald-50 text-emerald-700' };
};

const timeAgo = (iso: string | null) => {
    if (!iso) return '';
    const d = new Date(iso);
    const ts = d.getTime();
    if (!Number.isFinite(ts)) return '';
    const delta = Math.max(0, Date.now() - ts);
    const min = Math.floor(delta / 60000);
    if (min < 1) return 'agora';
    if (min < 60) return `há ${min} min`;
    const h = Math.floor(min / 60);
    if (h < 24) return `há ${h} h`;
    const days = Math.floor(h / 24);
    if (days < 30) return `há ${days} dia${days > 1 ? 's' : ''}`;
    const months = Math.floor(days / 30);
    return `há ${months} mês${months > 1 ? 'es' : ''}`;
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-[70]">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="close"></div>

        <div
            class="absolute right-0 top-0 flex h-full w-full max-w-[440px] flex-col bg-[#FBFBFB] shadow-2xl ring-1 ring-slate-200/60"
        >
            <div class="flex items-start justify-between gap-4 border-b border-slate-200/70 bg-white px-6 py-5">
                <div>
                    <div class="text-lg font-semibold text-slate-900">Novidades</div>
                    <div class="mt-1 text-sm font-medium text-slate-500">Changelog, patches e melhorias recentes</div>
                </div>
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-50 text-slate-600 ring-1 ring-slate-200/60"
                    aria-label="Fechar"
                    @click="close"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 6l12 12" />
                        <path d="M18 6l-12 12" />
                    </svg>
                </button>
            </div>

            <div class="bg-white px-6 py-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold ring-1 transition"
                        :class="activeFilter === 'all' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        @click="activeFilter = 'all'"
                    >
                        Tudo
                    </button>
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold ring-1 transition"
                        :class="activeFilter === 'new' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        @click="activeFilter = 'new'"
                    >
                        Novos
                    </button>
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold ring-1 transition"
                        :class="activeFilter === 'improvement' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        @click="activeFilter = 'improvement'"
                    >
                        Melhorias
                    </button>
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold ring-1 transition"
                        :class="activeFilter === 'fix' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        @click="activeFilter = 'fix'"
                    >
                        Correções
                    </button>
                    <button
                        type="button"
                        class="rounded-full px-4 py-2 text-xs font-semibold ring-1 transition"
                        :class="activeFilter === 'announcement' ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        @click="activeFilter = 'announcement'"
                    >
                        Avisos
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div v-if="loading" class="rounded-2xl bg-white p-5 text-sm font-semibold text-slate-500 ring-1 ring-slate-200/60">
                    Carregando novidades…
                </div>

                <div v-else-if="filteredItems.length === 0" class="rounded-2xl bg-white p-6 text-center ring-1 ring-slate-200/60">
                    <div class="text-sm font-semibold text-slate-900">Sem novidades</div>
                    <div class="mt-2 text-xs font-semibold text-slate-500">Quando algo novo sair, aparece aqui.</div>
                </div>

                <div v-else class="space-y-4">
                    <article v-for="item in filteredItems" :key="item.id" class="rounded-3xl bg-white p-5 ring-1 ring-slate-200/60">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span v-if="item.category" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ item.category }}</span>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeForType(item.type).cls">
                                    {{ badgeForType(item.type).label }}
                                </span>
                            </div>
                            <div class="text-xs font-semibold text-slate-400">{{ timeAgo(item.published_at) }}</div>
                        </div>

                        <div class="mt-3 text-base font-semibold text-slate-900">{{ item.title }}</div>

                        <div v-if="item.image_url" class="mt-4 overflow-hidden rounded-2xl bg-slate-100">
                            <img :src="item.image_url" alt="" class="h-44 w-full object-cover" />
                        </div>

                        <div v-if="item.content" class="mt-4 whitespace-pre-wrap text-sm font-medium text-slate-600">
                            {{ item.content }}
                        </div>

                        <div v-if="item.cta_url" class="mt-4">
                            <a
                                class="inline-flex items-center justify-center rounded-2xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-800 ring-1 ring-slate-200/60 hover:bg-slate-100"
                                :href="item.cta_url"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ item.cta_text || 'Saiba mais' }}
                            </a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>
</template>

