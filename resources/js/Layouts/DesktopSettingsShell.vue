<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DesktopShell from '@/Layouts/DesktopShell.vue';

const props = withDefaults(
    defineProps<{
        subtitle?: string;
    }>(),
    {
        subtitle: '11 de Janeiro, 2026',
    },
);

type SectionKey = 'profile' | 'categories' | 'notifications' | 'appearance' | 'security';

const currentSection = computed<SectionKey>(() => {
    if (route().current('settings.notifications')) return 'notifications';
    if (route().current('settings.categories')) return 'categories';
    if (route().current('settings.appearance')) return 'appearance';
    if (route().current('settings.security')) return 'security';
    if (route().current('settings.backup')) return 'security';
    return 'profile';
});

const menu = computed(() => [
    { key: 'profile' as const, label: 'Meu Perfil', href: route('settings'), icon: 'user' as const },
    { key: 'categories' as const, label: 'Categorias & Orçamentos', href: route('settings.categories'), icon: 'tag' as const },
    { key: 'notifications' as const, label: 'Notificações', href: route('settings.notifications'), icon: 'bell' as const },
    { key: 'appearance' as const, label: 'Aparência & Moeda', href: route('settings.appearance'), icon: 'palette' as const },
    { key: 'security' as const, label: 'Segurança & Sessões', href: route('settings.security'), icon: 'shield' as const },
]);
</script>

<template>
    <DesktopShell title="Configurações" :subtitle="subtitle" :show-search="false" :show-new-action="false">
        <template #actions>
            <div class="flex items-center gap-3">
                <div class="inline-flex h-11 items-center gap-2 rounded-xl bg-slate-50 px-4 text-sm font-semibold text-slate-600 ring-1 ring-slate-200/60">
                    <svg class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M10 12a2 2 0 1 0 4 0 2 2 0 0 0-4 0Z"
                        />
                        <path
                            d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"
                        />
                    </svg>
                    Modo Privado
                </div>
            </div>
        </template>

        <div class="grid grid-cols-[320px_1fr] gap-10">
            <aside class="pt-2">
                <nav class="space-y-2">
                    <Link
                        v-for="item in menu"
                        :key="item.key"
                        :href="item.href"
                        class="flex items-center gap-4 rounded-2xl px-5 py-4 text-sm font-semibold transition"
                        :class="item.key === currentSection ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' : 'text-slate-500 hover:bg-white hover:ring-1 hover:ring-slate-200/60'"
                    >
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-2xl"
                            :class="item.key === currentSection ? 'bg-white text-emerald-600 ring-1 ring-emerald-100' : 'bg-slate-100 text-slate-400'"
                        >
                            <svg v-if="item.icon === 'user'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c2.5-4 13.5-4 16 0" />
                            </svg>
                            <svg v-else-if="item.icon === 'tag'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 12V7a2 2 0 0 0-2-2H5L2 8l10 10 8-6Z" />
                                <path d="M7.5 9.5h.01" />
                            </svg>
                            <svg v-else-if="item.icon === 'bell'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.4V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                                <path d="M9 17a3 3 0 0 0 6 0" />
                            </svg>
                            <svg v-else-if="item.icon === 'palette'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22a10 10 0 1 1 10-10c0 2-1.5 3.5-3.5 3.5H16a2 2 0 0 0-2 2c0 2-1 3.5-2 3.5Z" />
                                <circle cx="7.5" cy="10.5" r="1" />
                                <circle cx="10.5" cy="7.5" r="1" />
                                <circle cx="13.5" cy="7.5" r="1" />
                                <circle cx="16.5" cy="10.5" r="1" />
                            </svg>
                            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
                                <path d="M9 12l2 2 4-4" />
                            </svg>
                        </span>
                        <span class="flex-1">{{ item.label }}</span>
                    </Link>
                </nav>
            </aside>

            <section class="min-w-0">
                <slot />
            </section>
        </div>
    </DesktopShell>
</template>
