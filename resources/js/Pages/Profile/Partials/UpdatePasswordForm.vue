<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const hasMinLength = computed(() => form.password.length >= 8);
const hasUppercase = computed(() => /[A-Z]/.test(form.password));
const hasNumber = computed(() => /\d/.test(form.password));

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Atualizar senha
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Use uma senha forte para manter sua conta segura.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Senha atual" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div>
                <InputLabel for="password" value="Nova senha" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError :message="form.errors.password" class="mt-2" />
                <ul class="mt-3 space-y-1 text-xs font-semibold text-gray-600">
                    <li class="flex items-center gap-2" :class="hasMinLength ? 'text-emerald-600' : 'text-gray-600'">
                        <span class="inline-flex h-4 w-4 items-center justify-center">
                            <svg v-if="hasMinLength" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            <span v-else class="h-2 w-2 rounded-full bg-gray-300"></span>
                        </span>
                        8+ caracteres
                    </li>
                    <li class="flex items-center gap-2" :class="hasUppercase ? 'text-emerald-600' : 'text-gray-600'">
                        <span class="inline-flex h-4 w-4 items-center justify-center">
                            <svg v-if="hasUppercase" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            <span v-else class="h-2 w-2 rounded-full bg-gray-300"></span>
                        </span>
                        1 letra maiúscula
                    </li>
                    <li class="flex items-center gap-2" :class="hasNumber ? 'text-emerald-600' : 'text-gray-600'">
                        <span class="inline-flex h-4 w-4 items-center justify-center">
                            <svg v-if="hasNumber" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M20 6 9 17l-5-5" />
                            </svg>
                            <span v-else class="h-2 w-2 rounded-full bg-gray-300"></span>
                        </span>
                        1 número
                    </li>
                </ul>
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar senha"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Salvo.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
