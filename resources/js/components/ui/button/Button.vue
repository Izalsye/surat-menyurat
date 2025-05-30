<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Primitive, type PrimitiveProps } from 'reka-ui';
import { type ButtonVariants, buttonVariants } from '.';
import { LoaderCircle } from 'lucide-vue-next';

interface Props extends PrimitiveProps {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    processing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    as: 'button'
});
</script>

<template>
    <Primitive
        data-slot="button"
        :as="as"
        :as-child="asChild"
        :class="cn(buttonVariants({ variant, size }), props.class)">
        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
        <slot />
    </Primitive>
</template>
