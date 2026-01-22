<script setup lang="ts">
import { ref, computed } from 'vue';

export type BancoSelecionado = {
  nome: string;
  logo: string;
  cor: string;
  svgFile?: string | null;
};

const props = defineProps<{
  open: boolean;
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'select', banco: BancoSelecionado): void;
  (event: 'select-wallet'): void;
}>();

const search = ref('');

const bancos = [
  { nome: 'Nubank', logo: '🟣', cor: '#8B10AE', svgFile: 'Nu Pagamentos S.A/nubank-branco.svg' },
  { nome: 'Banco Inter', logo: '🟢', cor: '#FF7A00', svgFile: 'Banco Inter S.A/inter.svg' },
  { nome: 'Itaú', logo: '🟠', cor: '#EC7000', svgFile: null },
  { nome: 'Bradesco', logo: '🔴', cor: '#CC092F', svgFile: 'Bradesco S.A/bradesco com nome.svg' },
  { nome: 'Banco do Brasil', logo: '🟡', cor: '#FFDD00', svgFile: 'Banco do Brasil S.A/banco-do-brasil-com-fundo.svg' },
  { nome: 'Caixa', logo: '🔵', cor: '#0066B3', svgFile: 'Caixa Econômica Federal/caixa-economica-federal-1.svg' },
  { nome: 'Santander', logo: '🔴', cor: '#EC0000', svgFile: 'Banco Santander Brasil S.A/banco-santander-logo.svg' },
  { nome: 'C6 Bank', logo: '⚫', cor: '#000000', svgFile: 'Banco C6 S.A/c6 bank- branco.svg' },
  { nome: 'PicPay', logo: '🟢', cor: '#21C25E', svgFile: 'PicPay/Logo-PicPay -nome .svg' },
  { nome: 'Neon', logo: '🔵', cor: '#00D9E1', svgFile: 'Neon/header-logo-neon.svg' },
  { nome: 'Outro banco', logo: '⚪', cor: '#64748B', svgFile: null },
];

const bancosFiltrados = computed(() => {
  const term = search.value.toLowerCase();
  return term ? bancos.filter((b) => b.nome.toLowerCase().includes(term)) : bancos;
});

const selectBanco = (banco: typeof bancos[0]) => {
  if (banco.nome === 'Carteira') {
    emit('select-wallet');
  } else {
    emit('select', banco);
  }
};
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
          <button class="h-6 w-6 text-[#6B7280]" type="button" @click="$emit('close')" aria-label="Fechar">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>

          <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div class="text-center text-[14px] font-bold text-[#1F2937]">
              Qual é a instituição financeira?
            </div>
          </div>
        </header>

        <div class="flex-1 overflow-y-auto px-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <!-- Search -->
          <div class="mt-4">
            <input
              v-model="search"
              type="text"
              placeholder="🔍 Buscar por nome"
              class="w-full rounded-xl border border-[#E5E7EB] bg-[#F9FAFB] px-4 py-3 text-sm placeholder:text-[#9CA3AF] focus:border-[#14B8A6] focus:outline-none focus:ring-0"
            />
          </div>

          <!-- Banks List -->
          <div class="mt-6 grid grid-cols-3 gap-3">
            <button
              v-for="banco in bancosFiltrados"
              :key="banco.nome"
              type="button"
              class="flex flex-col items-center gap-2 rounded-2xl border-2 border-slate-200 bg-white p-3 transition hover:border-slate-400 hover:bg-slate-50"
              :style="{ borderColor: banco.svgFile ? banco.cor : undefined }"
              @click="selectBanco(banco)"
            >
              <!-- Logo Card -->
              <div
                class="flex h-16 w-16 items-center justify-center rounded-xl"
                :style="{ backgroundColor: banco.svgFile ? `${banco.cor}15` : `${banco.cor}22` }"
              >
                <img
                  v-if="banco.svgFile"
                  :src="`/Bancos-em-SVG-main/${banco.svgFile}`"
                  :alt="banco.nome"
                  class="h-12 w-12 object-contain"
                  @error="($event.target as HTMLImageElement).style.display = 'none'"
                />
                <span
                  v-else
                  class="text-2xl"
                  aria-hidden="true"
                >
                  {{ banco.logo }}
                </span>
              </div>

              <!-- Bank Name -->
              <div class="text-center text-xs font-semibold text-slate-900 line-clamp-2">
                {{ banco.nome }}
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
