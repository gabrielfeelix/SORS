<script setup lang="ts">
import { computed, ref } from 'vue';
import { requestJson } from '@/lib/kitamoApi';
import { getBankSvgPath } from '@/lib/bankLogos';
import InstitutionAvatar from '@/Components/InstitutionAvatar.vue';
import InstitutionPickerModal from '@/Components/InstitutionPickerModal.vue';

export type PendingItem = {
  id: string;
  name: string;
  type: 'account' | 'card';
};

const props = defineProps<{
  open: boolean;
  items: PendingItem[];
}>();

const emit = defineEmits<{
  (event: 'close'): void;
  (event: 'updated'): void;
}>();

const selectedInstitutions = ref<Record<string, string>>({});
const savingIds = ref<Set<string>>(new Set());
const fixedIds = ref<Set<string>>(new Set());
const pickerForId = ref<string | null>(null);

const pendingItems = computed(() => {
  return props.items.filter(item => !fixedIds.value.has(item.id));
});

const saveInstitution = async (item: PendingItem) => {
  const institution = selectedInstitutions.value[item.id];
  if (!institution) return;

  savingIds.value.add(item.id);

  try {
    const endpoint = item.type === 'account' ? `/api/contas/${item.id}` : `/api/cartoes/${item.id}`;
    await requestJson(endpoint, {
      method: 'PATCH',
      body: JSON.stringify({ institution }),
    });

    fixedIds.value.add(item.id);
    emit('updated');
  } catch (error) {
    console.error('Erro ao salvar instituição:', error);
  } finally {
    savingIds.value.delete(item.id);
  }
};

const getItemIcon = (type: 'account' | 'card') => {
  if (type === 'card') {
    return `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="5" width="18" height="14" rx="3" />
      <path d="M3 10h18" />
    </svg>`;
  }
  return `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M3 10h18" />
    <path d="M5 10V8l7-5 7 5v2" />
    <path d="M6 10v9" />
    <path d="M18 10v9" />
  </svg>`;
};

const openPicker = (itemId: string) => {
  pickerForId.value = itemId;
};

const closePicker = () => {
  pickerForId.value = null;
};

const pickerSelected = computed(() => {
  if (!pickerForId.value) return null;
  return selectedInstitutions.value[pickerForId.value] ?? null;
});
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-[70]">
    <button
      class="absolute inset-0 bg-black/50 backdrop-blur-sm"
      type="button"
      @click="$emit('close')"
      aria-label="Fechar"
    ></button>

    <div class="absolute inset-0 w-full bg-white md:inset-auto md:left-1/2 md:top-1/2 md:max-h-[90vh] md:w-[600px] md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-3xl md:shadow-2xl">
      <div class="flex h-full flex-col md:max-h-[90vh]">
        <!-- Header -->
        <header class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
          <div>
            <h2 class="text-lg font-bold text-slate-900">Configurar Instituições</h2>
            <p class="mt-1 text-sm text-slate-500">
              {{ pendingItems.length }} {{ pendingItems.length === 1 ? 'item pendente' : 'itens pendentes' }}
            </p>
          </div>
          <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600 transition hover:bg-slate-100"
            @click="$emit('close')"
            aria-label="Fechar"
          >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18" />
              <path d="M6 6l12 12" />
            </svg>
          </button>
        </header>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto px-6 py-4">
          <div v-if="pendingItems.length === 0" class="flex h-full items-center justify-center py-12">
            <div class="text-center">
              <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 6 9 17l-5-5" />
                </svg>
              </div>
              <h3 class="mt-4 text-lg font-semibold text-slate-900">Tudo configurado!</h3>
              <p class="mt-2 text-sm text-slate-500">Todas as instituições foram configuradas.</p>
              <button
                type="button"
                class="mt-6 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                @click="$emit('close')"
              >
                Fechar
              </button>
            </div>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="item in pendingItems"
              :key="item.id"
              class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
              <div class="flex items-start gap-3">
                <div
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                  :class="item.type === 'card' ? 'bg-purple-50 text-purple-600' : 'bg-blue-50 text-blue-600'"
                  v-html="getItemIcon(item.type)"
                ></div>

                <div class="flex-1 min-w-0">
                  <div class="text-sm font-semibold text-slate-900">{{ item.name }}</div>
                  <div class="mt-1 text-xs text-slate-500">
                    {{ item.type === 'card' ? 'Cartão de crédito' : 'Conta bancária' }}
                  </div>

                  <div class="mt-3">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">
                      Instituição
                    </label>
                    <button
                      type="button"
                      class="mt-2 flex w-full items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-left"
                      @click="openPicker(item.id)"
                    >
                      <div class="flex min-w-0 items-center gap-3">
                        <InstitutionAvatar
                          :institution="selectedInstitutions[item.id] ?? null"
                          :svg-path="getBankSvgPath(selectedInstitutions[item.id] ?? null)"
                          fallback-icon="account"
                          container-class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-2xl bg-white"
                          img-class="h-7 w-7 object-contain"
                          fallback-icon-class="h-5 w-5 text-slate-500"
                        />
                        <div class="min-w-0">
                          <div class="truncate text-sm font-semibold text-slate-900">
                            {{ selectedInstitutions[item.id] || 'Selecione o banco' }}
                          </div>
                        </div>
                      </div>
                      <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6" />
                      </svg>
                    </button>
                  </div>

                  <button
                    type="button"
                    class="mt-3 w-full rounded-xl py-2 text-sm font-semibold transition"
                    :class="selectedInstitutions[item.id] && !savingIds.has(item.id)
                      ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                      : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    :disabled="!selectedInstitutions[item.id] || savingIds.has(item.id)"
                    @click="saveInstitution(item)"
                  >
                    {{ savingIds.has(item.id) ? 'Salvando...' : 'Salvar' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <InstitutionPickerModal
    :open="Boolean(pickerForId)"
    title="Instituição financeira"
    :selected="pickerSelected"
    @close="closePicker"
    @select="(banco) => { if (pickerForId) selectedInstitutions[pickerForId] = banco.nome; closePicker(); }"
  />
</template>
