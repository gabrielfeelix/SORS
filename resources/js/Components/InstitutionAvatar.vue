<script setup lang="ts">
import { computed, ref } from 'vue';

type FallbackIcon = 'account' | 'credit-card' | 'wallet';

const props = withDefaults(
    defineProps<{
        institution?: string | null;
        svgPath?: string | null;
        fallbackIcon: FallbackIcon;
        isWallet?: boolean;
        containerClass?: string;
        imgClass?: string;
        fallbackBgClass?: string;
        fallbackIconClass?: string;
    }>(),
    {
        institution: null,
        svgPath: null,
        isWallet: false,
        containerClass: 'flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl bg-white',
        imgClass: 'h-8 w-8 object-contain',
        fallbackBgClass: '',
        fallbackIconClass: 'h-5 w-5',
    },
);

const logoFailed = ref(false);

const shouldShowLogo = computed(() => !props.isWallet && Boolean(props.svgPath) && !logoFailed.value);

const src = computed(() => (props.svgPath ? `/Bancos-em-SVG-main/${props.svgPath}` : ''));

const onImgError = () => {
    logoFailed.value = true;
};
</script>

<template>
    <div :class="containerClass">
        <img
            v-if="shouldShowLogo"
            :src="src"
            :alt="institution ?? ''"
            :class="imgClass"
            @error="onImgError"
        />

        <div v-else class="flex h-full w-full items-center justify-center" :class="fallbackBgClass">
            <svg
                v-if="fallbackIcon === 'wallet'"
                :class="fallbackIconClass"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M4 7h16v12H4z" />
                <path d="M4 7V5h12v2" />
                <path d="M16 12h4" />
            </svg>
            <svg
                v-else-if="fallbackIcon === 'credit-card'"
                :class="fallbackIconClass"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <rect x="2" y="5" width="20" height="14" rx="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
            <svg
                v-else
                :class="fallbackIconClass"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path d="M3 10h18" />
                <path d="M5 10V8l7-5 7 5v2" />
                <path d="M6 10v9" />
                <path d="M18 10v9" />
            </svg>
        </div>
    </div>
</template>

