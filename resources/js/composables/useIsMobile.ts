import { useMediaQuery } from '@/composables/useMediaQuery';

// Mobile sempre. Tablet: retrato => mobile, paisagem => desktop.
export const useIsMobile = () =>
    useMediaQuery(
        '(max-width: 767px), ((hover: none) and (pointer: coarse) and (orientation: portrait))',
    );
