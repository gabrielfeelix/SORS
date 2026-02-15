<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';

const props = defineProps<{
    value: number;
    duration?: number;
    format?: (val: number) => string;
}>();

const displayValue = ref(0);
const duration = props.duration ?? 1000;

let startTime: number | null = null;
let startValue = 0;

const animate = (timestamp: number) => {
    if (!startTime) startTime = timestamp;
    const progress = Math.min((timestamp - startTime) / duration, 1);
    
    // Ease out quart
    const ease = 1 - Math.pow(1 - progress, 4);
    
    displayValue.value = startValue + (props.value - startValue) * ease;

    if (progress < 1) {
        requestAnimationFrame(animate);
    } else {
        displayValue.value = props.value;
    }
};

watch(() => props.value, (newVal, oldVal) => {
    startValue = oldVal ?? 0;
    startTime = null;
    requestAnimationFrame(animate);
});

onMounted(() => {
    startValue = 0;
    requestAnimationFrame(animate);
});

const formatted = computed(() => {
    if (props.format) return props.format(displayValue.value);
    return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(displayValue.value);
});
</script>

<template>
    <span>{{ formatted }}</span>
</template>
