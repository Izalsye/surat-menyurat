<script setup lang="ts">
import { onMounted, ref, toRefs, watch } from 'vue';
import { APIResponse } from '@/types';
import { monthYearFormat } from '@/lib/utils';
import { useI18n } from 'vue-i18n';
import { Knob, ProgressSpinner } from 'primevue';

const props = defineProps<{
    month: number;
    year: number;
}>();

interface Summary {
    total: number;
    converted: number;
    percentage: number;
}

const loading = ref<boolean>(false);
const summary = ref<Summary>();
const { locale } = useI18n();

const load = () => {
    loading.value = true;
    fetch(route('internal-api.dashboard.disposition-conversion', { month: month.value, year: year.value }))
        .then(response => response.json())
        .then((response: APIResponse<Summary>) => {
            if (response.error)
                return;

            summary.value = response.data;
        })
        .finally(() => {
            loading.value = false;
        });
};

const { month, year } = toRefs(props);
watch([month, year], load);

onMounted(() => {
    load();
});

</script>

<template>
    <div class="bg-card text-card-foreground flex flex-col gap-6 rounded-2xl border p-4 shadow-xs">
        <div class="flex gap-x-1 items-baseline">
            <h3 class="text font-medium">{{ $t('label.disposition_conversion') }}</h3>
            <small class="text-gray-500" v-if="false">{{ monthYearFormat(month, year, locale) }}</small>
        </div>

        <div class="flex items-center justify-center min-h-[100px]" v-if="loading">
            <ProgressSpinner
                style="width: 50px; height: 50px" stroke-width="8" fill="transparent"
                animation-duration=".5s" aria-label="Custom ProgressSpinner" />
        </div>
        <div class="grid lg:grid-cols-2 lg:gap-6 gap-y-8" v-else-if="summary">
            <div class="flex items-center justify-center">
                <Knob
                    value-color="MediumTurquoise"
                    readonly :value-template="`${summary.percentage}%`" class="text-xs"
                    v-model="summary.converted" :min="0" :max="summary.total" />
            </div>
            <div class="flex lg:flex-col justify-between">
                <div class="py-1.5 flex flex-col items-center">
                    <span class="font-semibold text-xl">{{ summary.converted }}</span>
                    <small class="text-xs text-gray-500">
                        {{ $t('menu.outgoing_letter') }}
                        <i class="pi pi-question-circle text-gray-400 hover:text-gray-600 cursor-pointer"
                           v-tooltip.bottom="$t('label.disposition_outgoing_letter')"
                           style="font-size: 0.6rem"></i>
                    </small>
                </div>
                <div class="py-1.5 flex flex-col items-center">
                    <span class="font-semibold text-xl">{{ summary.total }}</span>
                    <small class="text-xs text-gray-500">
                        {{ $t('menu.disposition') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</template>
