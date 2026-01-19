<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { formatMoneyInputCentsShift, moneyInputToNumber } from '@/lib/moneyInput';
import { preventNonDigitKeydown } from '@/lib/inputGuards';

const props = defineProps<{
  open: boolean;
  wallet: {
    id: string;
    name: string;
    initial_balance?: number;
    current_balance: number;
    color?: string;
    incluir_soma?: boolean;
  } | null;
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'save', payload: {
    id: string;
    name: string;
    initial_balance: number;
    color: string;
    incluir_soma: boolean;
  }): void;
}>();

// State
const name = ref('');
const initialBalance = ref('');
const color = ref('#14B8A6');
const incluirSoma = ref(true);
const originalBalance = ref(0);

// Colors (4 colors like CreateAccountStep3)
const cores = ['#14B8A6', '#8B10AE', '#10B981', '#FF7A00'];

// Computed
const balanceChanged = computed(() => {
  const current = moneyInputToNumber(initialBalance.value);
  return current !== originalBalance.value;
});

const initialBalanceNumber = computed(() => {
  return moneyInputToNumber(initialBalance.value);
});

// Methods
const onBalanceInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  initialBalance.value = formatMoneyInputCentsShift(target.value);
};

const save = () => {
  if (!props.wallet) return;
  emit('save', {
    id: props.wallet.id,
    name: name.value.trim(),
    initial_balance: initialBalanceNumber.value,
    color: color.value,
    incluir_soma: incluirSoma.value,
  });
};

// Watch modal open
watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen || !props.wallet) return;
    name.value = props.wallet.name;
    const balance = props.wallet.initial_balance ?? props.wallet.current_balance;
    originalBalance.value = balance;
    initialBalance.value = formatMoneyInputCentsShift(String(balance * 100));
    color.value = props.wallet.color ?? '#14B8A6';
    incluirSoma.value = props.wallet.incluir_soma ?? true;
  }
);
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
        <header class="relative flex items-center px-4 pt-[calc(0.5rem+env(safe-area-inset-top))] pb-3">
          <button class="h-6 w-6 text-[#6B7280]" type="button" @click="$emit('close')" aria-label="Voltar">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M15 18l-6-6 6-6" />
            </svg>
          </button>

          <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div class="text-[14px] font-bold text-[#1F2937]">Editar Carteira</div>
          </div>

          <button class="ml-auto h-6 w-6 text-[#6B7280]" type="button" @click="$emit('close')" aria-label="Fechar">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>
        </header>

        <div class="flex-1 overflow-y-auto px-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <!-- Wallet Icon -->
          <div class="flex justify-center mt-6 mb-6">
            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-teal-50 text-teal-600">
              <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="3"/>
                <path d="M2 10h20"/>
                <circle cx="16" cy="14" r="2"/>
              </svg>
            </div>
          </div>

          <!-- Saldo Display -->
          <div class="mt-6 flex items-center justify-center py-6">
            <div class="text-center">
              <div class="text-4xl font-bold text-[#14B8A6]">R$</div>
              <input
                class="mt-2 w-full appearance-none border-0 bg-transparent text-center text-3xl font-bold text-slate-900 outline-none focus:outline-none focus:ring-0 focus-visible:outline-none"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                :value="initialBalance"
                @input="onBalanceInput"
                @keydown="preventNonDigitKeydown"
                placeholder="0,00"
                aria-label="Saldo inicial"
              />
            </div>
          </div>

          <!-- Warning when balance changed -->
          <div v-if="balanceChanged" class="mt-6 rounded-lg border-l-4 border-amber-500 bg-amber-50 p-3">
            <div class="flex items-start gap-2">
              <div class="text-lg">⚠️</div>
              <div class="flex-1">
                <h3 class="mb-1 text-sm font-semibold text-amber-900">Atenção!</h3>
                <p class="text-xs text-amber-700">
                  Alterar o saldo inicial pode impactar a conciliação de contas.
                  Recomendamos criar uma transação de ajuste ao invés de alterar diretamente.
                </p>
              </div>
            </div>
          </div>

          <!-- Descrição -->
          <div class="mt-6">
            <div class="mb-2 text-xs font-semibold uppercase text-slate-500">Descrição (opcional)</div>
            <input
              v-model="name"
              type="text"
              placeholder="Ex: Carteira principal, Carteira pessoal..."
              class="w-full appearance-none rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] px-4 py-3 text-sm placeholder:text-[#9CA3AF] focus:border-[#14B8A6] focus:outline-none focus:ring-0 focus-visible:outline-none"
            />
          </div>

          <!-- Tipo de Conta (Fixed for Wallet) -->
          <div class="mt-6">
            <div class="mb-3 text-xs font-semibold uppercase text-slate-500">Tipo de conta</div>
            <div class="rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] px-4 py-3">
              <div class="text-sm text-slate-900">💼 Carteira</div>
            </div>
          </div>

          <!-- Cor da Carteira -->
          <div class="mt-6">
            <div class="mb-3 text-xs font-semibold uppercase text-slate-500">Cor da carteira</div>
            <div class="flex gap-2">
              <button
                v-for="c in cores"
                :key="c"
                type="button"
                class="h-10 w-10 rounded-lg border-2 transition"
                :style="{ backgroundColor: c }"
                :class="color === c ? 'border-gray-800' : 'border-transparent'"
                @click="color = c"
                :aria-label="`Cor ${c}`"
              ></button>
            </div>
          </div>

          <!-- Include in Summary -->
          <div class="mt-6 rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] p-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-slate-700">Incluir na soma da tela inicial</div>
              <button
                type="button"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                :class="incluirSoma ? 'bg-[#14B8A6]' : 'bg-[#E5E7EB]'"
                @click="incluirSoma = !incluirSoma"
                aria-label="Incluir na soma"
              >
                <span
                  class="inline-block h-5 w-5 transform rounded-full bg-white transition"
                  :class="incluirSoma ? 'translate-x-5' : 'translate-x-0.5'"
                ></span>
              </button>
            </div>
          </div>
        </div>

        <footer class="shrink-0 border-t border-slate-100 px-6 pt-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
          <div class="flex gap-3">
            <button
              class="flex-1 rounded-xl border border-[#E5E7EB] py-3 text-sm font-semibold text-[#6B7280] transition hover:bg-slate-50"
              type="button"
              @click="$emit('close')"
            >
              Cancelar
            </button>
            <button
              class="flex-1 rounded-xl bg-[#14B8A6] py-3 text-sm font-semibold text-white shadow-lg shadow-teal-500/20 transition hover:bg-[#0D9488]"
              type="button"
              @click="save"
            >
              Salvar
            </button>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<style scoped>
select {
  background-image: none !important;
  -webkit-appearance: none !important;
  -moz-appearance: none !important;
  appearance: none !important;
}
</style>
