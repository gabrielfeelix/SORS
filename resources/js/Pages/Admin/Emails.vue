<script setup lang="ts">
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MobileShell from '@/Layouts/MobileShell.vue';
import DesktopShell from '@/Layouts/DesktopShell.vue';
import { useIsMobile } from '@/composables/useIsMobile';
import AdminHeader from '@/Components/AdminHeader.vue';

const isMobile = useIsMobile();
const Shell = computed(() => (isMobile.value ? MobileShell : DesktopShell));
const shellProps = computed(() => (isMobile.value ? { showNav: false } : { title: 'Administração', showSearch: false, showNewAction: false }));
</script>

<template>
    <Head title="Administração · E-mails" />

    <component :is="Shell" v-bind="shellProps">
        <div class="space-y-4">
            <AdminHeader description="E-mails e comunicados (em breve)." />
        </div>
    </component>
</template>
