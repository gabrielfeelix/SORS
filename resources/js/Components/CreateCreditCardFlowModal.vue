<script setup lang="ts">
import { computed, ref } from 'vue';
import CreateCreditCardStep1 from './CreateCreditCardStep1.vue';
import CreateCreditCardStep2 from './CreateCreditCardStep2.vue';
import CreateCreditCardStep3 from './CreateCreditCardStep3.vue';

export type BancoSelecionado = {
  nome: string;
  logo: string;
  cor: string;
};

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'save'): void;
}>();

const step = ref(1);
const banco = ref<BancoSelecionado | null>(null);

const close = () => emit('close');

const handleSelectBank = (value: BancoSelecionado) => {
  banco.value = value;
  step.value = 2;
};

const handleSelectMethod = (method: 'manual' | 'automatic') => {
  if (method === 'manual') step.value = 3;
  // TODO: Handle automatic method when available
};

const handleSave = () => {
  emit('save');
  close();
};

const openStep1 = computed(() => props.open && step.value === 1);
const openStep2 = computed(() => props.open && step.value === 2);
const openStep3 = computed(() => props.open && step.value === 3);
</script>

<template>
  <CreateCreditCardStep1 :open="openStep1" @close="close" @select="handleSelectBank" />
  <CreateCreditCardStep2
    :open="openStep2"
    :banco="banco"
    @close="close"
    @back="step = 1"
    @select-method="handleSelectMethod"
  />
  <CreateCreditCardStep3
    :open="openStep3"
    :banco="banco"
    @close="close"
    @back="step = 1"
    @save="handleSave"
  />
</template>
