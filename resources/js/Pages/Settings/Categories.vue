<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import MobileShell from '@/Layouts/MobileShell.vue';
import DesktopSettingsShell from '@/Layouts/DesktopSettingsShell.vue';
import { useIsMobile } from '@/composables/useIsMobile';
import PickerSheet from '@/Components/PickerSheet.vue';
import MobileToast from '@/Components/MobileToast.vue';
import { requestJson } from '@/lib/kitamoApi';

const isMobile = useIsMobile();
const page = usePage();

type CategoryType = 'expense' | 'income';
type IconKey = 'food' | 'home' | 'car' | 'pill' | 'briefcase' | 'heart' | 'shirt' | 'bolt';

interface Category {
    id: number;
    name: string;
    type: CategoryType;
    color: string;
    icon: IconKey;
    is_default: boolean;
}

const categories = ref<Category[]>([]);

const categoryType = ref<CategoryType>('expense');
const createCategoryOpen = ref(false);
const editCategoryOpen = ref(false);
const selectedCategory = ref<Category | null>(null);

const newCategoryName = ref('');
const newCategoryIcon = ref<IconKey>('food');
const newCategoryColor = ref('#F97316');

const toastOpen = ref(false);
const toastMessage = ref('');

const showToast = (message: string) => {
    toastMessage.value = message;
    toastOpen.value = true;
};

const filteredCategories = computed(() =>
    categories.value.filter((c) => c.type === categoryType.value),
);

const iconOptions: IconKey[] = ['food', 'home', 'car', 'pill', 'briefcase', 'heart', 'shirt', 'bolt'];

const iconName = (icon: IconKey) => {
    const names: Record<IconKey, string> = {
        food: 'Alimentação',
        home: 'Moradia',
        car: 'Transporte',
        pill: 'Remédio',
        briefcase: 'Trabalho',
        heart: 'Saúde',
        shirt: 'Roupas',
        bolt: 'Energia',
    };
    return names[icon];
};

const getIconColor = (category: Category) => {
    const colors: Record<CategoryType, Record<IconKey, string>> = {
        expense: {
            food: '#F59E0B',
            home: '#EF4444',
            car: '#3B82F6',
            pill: '#A855F7',
            briefcase: '#14B8A6',
            heart: '#EC4899',
            shirt: '#F59E0B',
            bolt: '#10B981',
        },
        income: {
            food: '#10B981',
            home: '#14B8A6',
            car: '#3B82F6',
            pill: '#6366F1',
            briefcase: '#A855F7',
            heart: '#EC4899',
            shirt: '#F59E0B',
            bolt: '#EF4444',
        },
    };
    return colors[category.type]?.[category.icon] || '#14B8A6';
};

const renderIcon = (icon: IconKey) => {
    const icons: Record<IconKey, string> = {
        food: 'M4 3v7M8 3v7M6 3v7M14 3v7c0 2 1 3 3 3v8M20 3v7',
        home: 'M3 10.5L12 3l9 7.5M5 10v10h14V10',
        car: 'M5 16l1-5 1-3h10l1 3 1 5M7 16h10',
        pill: 'M10 14 8 16a4 4 0 0 1-6-6l2-2a4 4 0 0 1 6 6ZM14 10l2-2a4 4 0 0 1 6 6l-2 2a4 4 0 0 1-6-6ZM8 16l8-8',
        briefcase: 'M3 12h18M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2',
        heart: 'M20 8c0 5-8 10-8 10S4 13 4 8a4 4 0 0 1 8 0 4 4 0 0 1 8 0Z',
        shirt: 'M15 3H9l-2 4h10l-2-4ZM7 7v14M17 7v14',
        bolt: 'M13 2 3 14h8l-1 8 10-12h-8l1-8Z',
    };
    return icons[icon] || '';
};

const openCreateCategory = () => {
    newCategoryName.value = '';
    newCategoryIcon.value = 'food';
    newCategoryColor.value = categoryType.value === 'expense' ? '#F97316' : '#10B981';
    createCategoryOpen.value = true;
};

const openEditCategory = (category: Category) => {
    selectedCategory.value = category;
    newCategoryName.value = category.name;
    newCategoryIcon.value = category.icon;
    newCategoryColor.value = category.color;
    editCategoryOpen.value = true;
};

const saveCategory = async () => {
    if (!newCategoryName.value.trim()) {
        showToast('Nome da categoria é obrigatório');
        return;
    }

    try {
        const response = await requestJson<{ category: Category }>('/categories', {
            method: 'POST',
            body: JSON.stringify({
                name: newCategoryName.value.trim(),
                type: categoryType.value,
                icon: newCategoryIcon.value,
                color: newCategoryColor.value,
            }),
        });

        const newCategory = response.category;
        const idx = categories.value.findIndex((c) => c.id === newCategory.id);
        if (idx >= 0) {
            categories.value[idx] = newCategory;
        } else {
            categories.value.push(newCategory);
        }

        createCategoryOpen.value = false;
        editCategoryOpen.value = false;
        showToast('Categoria salva com sucesso!');
    } catch {
        showToast('Erro ao salvar categoria');
    }
};

const deleteCategory = async () => {
    if (!selectedCategory.value) return;

    try {
        // TODO: Implementar endpoint de delete no backend
        categories.value = categories.value.filter((c) => c.id !== selectedCategory.value!.id);
        editCategoryOpen.value = false;
        showToast('Categoria excluída com sucesso!');
    } catch {
        showToast('Erro ao excluir categoria');
    }
};

const closeCreateModal = () => {
    createCategoryOpen.value = false;
};

const closeEditModal = () => {
    editCategoryOpen.value = false;
    selectedCategory.value = null;
};
</script>

<template>
    <Head title="Categorias & Orçamentos" />

    <MobileShell v-if="isMobile" :show-nav="false">
        <header class="flex items-center justify-between pt-2">
            <Link
                :href="route('settings')"
                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-slate-600 shadow-sm ring-1 ring-slate-200/60"
                aria-label="Voltar"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </Link>

            <div class="text-center">
                <div class="text-lg font-semibold text-slate-900">Categorias</div>
            </div>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#14B8A6] text-white shadow-sm"
                aria-label="Criar categoria"
                @click="openCreateCategory"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14" />
                    <path d="M5 12h14" />
                </svg>
            </button>
        </header>

        <!-- Type Tabs -->
        <div class="mt-4 flex gap-2">
            <button
                type="button"
                class="flex-1 rounded-2xl px-4 py-2.5 text-sm font-semibold transition"
                :class="
                    categoryType === 'expense'
                        ? 'bg-red-50 text-red-500 ring-1 ring-red-100'
                        : 'bg-white text-slate-500 ring-1 ring-slate-200'
                "
                @click="categoryType = 'expense'"
            >
                Despesas
            </button>
            <button
                type="button"
                class="flex-1 rounded-2xl px-4 py-2.5 text-sm font-semibold transition"
                :class="
                    categoryType === 'income'
                        ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100'
                        : 'bg-white text-slate-500 ring-1 ring-slate-200'
                "
                @click="categoryType = 'income'"
            >
                Receitas
            </button>
        </div>

        <!-- Create Category Modal -->
        <PickerSheet :open="createCategoryOpen" title="Nova Categoria" @close="closeCreateModal">
            <div class="space-y-4 pb-2">
                <div class="flex justify-center pt-2">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl text-white shadow-lg"
                        :style="{ backgroundColor: newCategoryColor }"
                    >
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path :d="renderIcon(newCategoryIcon)" />
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-300">Nome da Categoria</label>
                    <input
                        v-model="newCategoryName"
                        type="text"
                        placeholder="Ex: Alimentação..."
                        class="mt-3 h-12 w-full rounded-2xl bg-slate-50 px-4 text-sm font-semibold text-slate-900 ring-1 ring-slate-200/60 focus:outline-none focus:ring-2 focus:ring-[#14B8A6]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-300">Ícone</label>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        <button
                            v-for="icon in iconOptions"
                            :key="icon"
                            type="button"
                            class="flex h-12 w-full items-center justify-center rounded-xl ring-1 transition"
                            :class="
                                newCategoryIcon === icon
                                    ? 'bg-slate-50 ring-2 ring-[#14B8A6]'
                                    : 'bg-slate-50 ring-slate-200'
                            "
                            @click="newCategoryIcon = icon"
                        >
                            <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path :d="renderIcon(icon)" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        class="flex-1 h-12 rounded-2xl bg-slate-100 text-sm font-semibold text-slate-600 transition hover:bg-slate-200"
                        @click="closeCreateModal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="flex-1 h-12 rounded-2xl bg-[#14B8A6] text-sm font-semibold text-white transition hover:bg-[#0D9488]"
                        @click="saveCategory"
                    >
                        Salvar
                    </button>
                </div>
            </div>
        </PickerSheet>

        <!-- Edit Category Modal -->
        <PickerSheet :open="editCategoryOpen" title="Editar Categoria" @close="closeEditModal">
            <div v-if="selectedCategory" class="space-y-4 pb-2">
                <div class="flex justify-center pt-2">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl text-white shadow-lg"
                        :style="{ backgroundColor: getIconColor(selectedCategory) }"
                    >
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path :d="renderIcon(selectedCategory.icon)" />
                        </svg>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-300">Nome da Categoria</label>
                    <input
                        v-model="newCategoryName"
                        type="text"
                        class="mt-3 h-12 w-full rounded-2xl bg-slate-50 px-4 text-sm font-semibold text-slate-900 ring-1 ring-slate-200/60 focus:outline-none focus:ring-2 focus:ring-[#14B8A6]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-300">Ícone</label>
                    <div class="mt-3 grid grid-cols-4 gap-2">
                        <button
                            v-for="icon in iconOptions"
                            :key="icon"
                            type="button"
                            class="flex h-12 w-full items-center justify-center rounded-xl ring-1 transition"
                            :class="
                                newCategoryIcon === icon
                                    ? 'bg-slate-50 ring-2 ring-[#14B8A6]'
                                    : 'bg-slate-50 ring-slate-200'
                            "
                            @click="newCategoryIcon = icon"
                        >
                            <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path :d="renderIcon(icon)" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="button"
                        class="flex-1 h-12 rounded-2xl bg-slate-100 text-sm font-semibold text-slate-600 transition hover:bg-slate-200"
                        @click="closeEditModal"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="flex-1 h-12 rounded-2xl bg-[#14B8A6] text-sm font-semibold text-white transition hover:bg-[#0D9488]"
                        @click="saveCategory"
                    >
                        Salvar
                    </button>
                </div>

                <button
                    type="button"
                    class="w-full py-2 text-center text-sm font-semibold text-red-500 transition hover:text-red-600"
                    @click="deleteCategory"
                >
                    Excluir Categoria
                </button>
            </div>
        </PickerSheet>

        <!-- Categories Grid -->
        <div v-if="filteredCategories.length > 0" class="mt-6 grid grid-cols-2 gap-3 pb-8">
            <button
                v-for="category in filteredCategories"
                :key="category.id"
                type="button"
                class="flex flex-col items-center gap-2 rounded-3xl bg-white px-4 py-5 shadow-sm ring-1 ring-slate-200/60 transition hover:shadow-md"
                @click="openEditCategory(category)"
            >
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl text-white"
                    :style="{ backgroundColor: getIconColor(category) }"
                >
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path :d="renderIcon(category.icon)" />
                    </svg>
                </div>
                <div class="text-xs font-semibold text-slate-900">{{ category.name }}</div>
            </button>

            <button
                type="button"
                class="flex flex-col items-center gap-2 rounded-3xl border-2 border-dashed border-slate-200 bg-white px-4 py-5 transition hover:border-slate-300"
                @click="openCreateCategory"
            >
                <div class="flex h-12 w-12 items-center justify-center text-slate-300">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14" />
                        <path d="M5 12h14" />
                    </svg>
                </div>
                <div class="text-xs font-semibold text-slate-400">Nova Categoria</div>
            </button>
        </div>

        <MobileToast :show="toastOpen" :message="toastMessage" @dismiss="toastOpen = false" />
    </MobileShell>

    <DesktopSettingsShell v-else>
        <div class="overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200/60">
            <div class="px-10 py-9">
                <div class="flex items-center justify-between">
                    <div class="text-lg font-semibold text-slate-900">Suas Categorias</div>
                    <button
                        type="button"
                        class="inline-flex h-11 items-center gap-2 rounded-xl bg-[#14B8A6] px-5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20"
                        @click="openCreateCategory"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        Nova Categoria
                    </button>
                </div>

                <div v-if="filteredCategories.length > 0" class="mt-8 grid grid-cols-2 gap-6">
                    <div
                        v-for="category in filteredCategories"
                        :key="category.id"
                        class="flex items-center justify-between rounded-2xl bg-slate-50 px-6 py-5 ring-1 ring-slate-200/60 transition hover:bg-slate-100 cursor-pointer"
                        @click="openEditCategory(category)"
                    >
                        <div class="flex items-center gap-4">
                            <span
                                class="flex h-12 w-12 items-center justify-center rounded-2xl text-white"
                                :style="{ backgroundColor: getIconColor(category) }"
                            >
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path :d="renderIcon(category.icon)" />
                                </svg>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ category.name }}</div>
                                <div class="mt-1 text-xs font-semibold text-slate-400">{{ categoryType }}</div>
                            </div>
                        </div>
                        <svg class="h-5 w-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6" />
                        </svg>
                    </div>
                </div>

                <div v-else class="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                    <div class="text-sm text-slate-500">Nenhuma categoria criada ainda. Crie uma para começar!</div>
                </div>
            </div>
        </div>
    </DesktopSettingsShell>
</template>
