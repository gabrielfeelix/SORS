<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useIsMobile } from '@/composables/useIsMobile';
import MobileShell from '@/Layouts/MobileShell.vue';
import DesktopShell from '@/Layouts/DesktopShell.vue';
import ToggleSwitch from '@/Components/ToggleSwitch.vue';
import { useMediaQuery } from '@/composables/useMediaQuery';
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const isMobile = useIsMobile();
const Shell = computed(() => (isMobile.value ? MobileShell : DesktopShell));
const shellProps = computed(() =>
    isMobile.value ? { showNav: false } : { title: 'Aparência', showSearch: false, showNewAction: false },
);
const page = usePage<any>();
const darkMode = ref(false);
const brl = ref(true);

const userTheme = computed(() => page.props.auth?.user?.theme ?? 'light');

watch(
    userTheme,
    (theme) => {
        darkMode.value = theme === 'dark';
        const resolved = darkMode.value ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', resolved);
        localStorage.setItem('theme', resolved);
    },
    { immediate: true },
);

watch(darkMode, async (enabled, old) => {
    if (enabled === old) return;
    const next = enabled ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);

    try {
        await axios.patch('/api/user/theme', { theme: next });
    } catch {
        const fallback = old ? 'dark' : 'light';
        darkMode.value = old;
        document.documentElement.setAttribute('data-theme', fallback);
        localStorage.setItem('theme', fallback);
    }
});
</script>

<template>
    <Head title="Aparência & Moeda" />

    <component :is="Shell" v-bind="shellProps">
        <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
            <div class="text-sm font-semibold text-slate-900">Aparência & Moeda</div>
            <div class="mt-2 text-sm text-slate-500">Ainda vamos definir essa tela no mobile.</div>
        </div>
    </component>

    
</template>
