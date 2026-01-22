<script setup lang="ts">
import { computed } from 'vue';

export type PendingItem = {
  id: string;
  name: string;
  type: 'account' | 'card';
};

const props = defineProps<{
  items: PendingItem[];
}>();

const emit = defineEmits<{
  (event: 'fix-items'): void;
  (event: 'dismiss'): void;
}>();

const accountsCount = computed(() => props.items.filter(i => i.type === 'account').length);
const cardsCount = computed(() => props.items.filter(i => i.type === 'card').length);

const message = computed(() => {
  const parts: string[] = [];
  if (accountsCount.value > 0) {
    parts.push(`${accountsCount.value} conta${accountsCount.value > 1 ? 's' : ''}`);
  }
  if (cardsCount.value > 0) {
    parts.push(`${cardsCount.value} cartão${cardsCount.value > 1 ? 'es' : ''}`);
  }
  return parts.join(' e ');
});
</script>

<template>
  <div
    v-if="items.length > 0"
    class="rounded-2xl border-l-4 border-amber-500 bg-amber-50 p-4 shadow-sm ring-1 ring-amber-100"
  >
    <div class="flex items-start gap-3">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100">
        <svg class="h-5 w-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 9v4" />
          <path d="M12 17h.01" />
          <path d="M12 3c4.97 0 9 4.03 9 9s-4.03 9-9 9-9-4.03-9-9 4.03-9 9-9Z" />
        </svg>
      </div>

      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-bold text-amber-900">
          Ação necessária
        </h3>
        <p class="mt-1 text-sm text-amber-800">
          Você tem {{ message }} sem instituição configurada. Configure agora para ver as logos dos bancos!
        </p>

        <div class="mt-3 flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
            @click="emit('fix-items')"
          >
            Configurar agora
          </button>
          <button
            type="button"
            class="rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-50"
            @click="emit('dismiss')"
          >
            Lembrar depois
          </button>
        </div>
      </div>

      <button
        type="button"
        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-amber-600 transition hover:bg-amber-100"
        @click="emit('dismiss')"
        aria-label="Fechar"
      >
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 6L6 18" />
          <path d="M6 6l12 12" />
        </svg>
      </button>
    </div>
  </div>
</template>
