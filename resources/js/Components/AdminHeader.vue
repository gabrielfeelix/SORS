<script setup lang="ts">
import AdminNav from '@/Components/AdminNav.vue';

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
    }>(),
    {
        title: 'Administração',
        description: '',
    },
);
</script>

<template>
    <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-slate-200/60">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="text-xl font-semibold text-slate-900">{{ props.title }}</div>
                <p v-if="props.description" class="mt-2 text-sm text-slate-500">{{ props.description }}</p>
            </div>
            <div class="md:max-w-[720px] md:justify-end">
                <AdminNav />
            </div>
        </div>
    </div>
</template>

