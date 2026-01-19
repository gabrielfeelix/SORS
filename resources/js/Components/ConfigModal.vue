<script setup lang="ts">
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PickerSheet from '@/Components/PickerSheet.vue';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const menuItems = computed(() => [
    {
        label: 'Categorias',
        href: route('settings'),
        icon: 'tag',
        tone: 'teal' as const,
    },
    {
        label: 'Tags',
        href: route('settings'),
        icon: 'tag2',
        tone: 'blue' as const,
    },
    {
        label: 'Metas',
        href: route('settings'),
        icon: 'target',
        tone: 'red' as const,
    },
    {
        label: 'Contas',
        href: route('settings'),
        icon: 'wallet',
        tone: 'emerald' as const,
    },
    {
        label: 'Cartão de Crédito',
        href: route('settings'),
        icon: 'card',
        tone: 'purple' as const,
    },
]);

const toneClass = (tone: 'teal' | 'blue' | 'red' | 'emerald' | 'purple') => {
    const toneMap = {
        teal: 'bg-teal-100 text-teal-600',
        blue: 'bg-blue-100 text-blue-600',
        red: 'bg-red-100 text-red-600',
        emerald: 'bg-emerald-100 text-emerald-600',
        purple: 'bg-purple-100 text-purple-600',
    };
    return toneMap[tone];
};

const handleSelect = (href: string) => {
    emit('close');
    router.visit(href);
};
</script>

<template>
    <PickerSheet :open="open" title="Menu Rápido" @close="emit('close')">
        <div class="space-y-1 pb-2">
            <button
                v-for="item in menuItems"
                :key="item.label"
                type="button"
                class="flex w-full items-center gap-4 rounded-2xl px-4 py-4 transition hover:bg-slate-50"
                @click="handleSelect(item.href)"
            >
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl" :class="toneClass(item.tone)">
                    <svg v-if="item.icon === 'tag'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7.5 3A4.5 4.5 0 0 0 3 7.5v9A4.5 4.5 0 0 0 7.5 21h9a4.5 4.5 0 0 0 4.5-4.5v-9A4.5 4.5 0 0 0 16.5 3h-9Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'tag2'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'target'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="12" cy="12" r="10" />
                        <circle cx="12" cy="12" r="6" fill="white" />
                        <circle cx="12" cy="12" r="2" />
                    </svg>
                    <svg v-else-if="item.icon === 'wallet'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M18 6H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2Zm0 10h-4v-2h4v2Z" />
                    </svg>
                    <svg v-else-if="item.icon === 'card'" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="2" y="4" width="20" height="16" rx="2" />
                        <path d="M2 9h20" fill="currentColor" />
                    </svg>
                </span>
                <span class="flex-1 text-left text-sm font-semibold text-slate-900">{{ item.label }}</span>
                <svg class="h-5 w-5 flex-shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6" />
                </svg>
            </button>
        </div>
    </PickerSheet>
</template>
