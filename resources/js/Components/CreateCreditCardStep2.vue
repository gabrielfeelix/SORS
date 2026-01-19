<script setup lang="ts">
import type { BancoSelecionado } from './CreateCreditCardStep1.vue';

const props = defineProps<{
  open: boolean;
  banco: BancoSelecionado | null;
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'back'): void;
  (event: 'select-method', method: 'manual' | 'automatic'): void;
}>();
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[60]">
    <button
      class="absolute inset-0 bg-black/50 backdrop-blur-sm"
      type="button"
      @click="$emit('close')"
      aria-label="Fechar"
    ></button>

    <div class="absolute inset-0 w-full bg-white" role="dialog" aria-modal="true">
      <div class="flex h-full flex-col">
        <header class="flex items-center justify-between px-4 pt-[calc(0.5rem+env(safe-area-inset-top))] pb-4">
          <button class="h-6 w-6 text-[#6B7280]" type="button" @click="$emit('back')" aria-label="Voltar">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 18l-6-6 6-6" />
            </svg>
          </button>
          <div class="text-[14px] font-bold text-[#1F2937]">Como adicionar?</div>
          <button class="h-6 w-6 text-[#6B7280]" type="button" @click="$emit('close')" aria-label="Fechar">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>
        </header>

        <div class="flex-1 overflow-y-auto px-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <!-- Manual -->
          <button
            type="button"
            class="mt-6 w-full rounded-2xl border-2 border-slate-200 bg-white px-5 py-6 text-left transition hover:border-teal-500 hover:bg-teal-50"
            @click="$emit('select-method', 'manual')"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-semibold text-slate-900">Preenchimento manual</div>
                <div class="mt-1 text-sm text-slate-500">Insira os dados do cartão manualmente</div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </button>

          <!-- Automatic (Disabled for now) -->
          <button
            type="button"
            disabled
            class="mt-4 w-full rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50 px-5 py-6 text-left opacity-50 cursor-not-allowed"
          >
            <div class="flex items-center justify-between">
              <div>
                <div class="text-lg font-semibold text-slate-900">Open Finance (em breve)</div>
                <div class="mt-1 text-sm text-slate-500">Conecte sua conta automaticamente</div>
              </div>
              <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 5l7 7-7 7" />
              </svg>
            </div>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
