<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="w-full appearance-none rounded-lg border border-slate-200 bg-white px-3 py-2 text-base text-slate-700 placeholder:text-slate-300 shadow-sm outline-none focus:border-[#14B8A6] focus:outline-none focus:ring-0"
        v-model="model"
        ref="input"
    />
</template>
