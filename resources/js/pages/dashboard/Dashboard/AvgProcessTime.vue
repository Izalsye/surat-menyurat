<script setup lang="ts">
import { onMounted, ref, toRefs, watch } from 'vue';
import { APIResponse } from '@/types';
import { monthYearFormat } from '@/lib/utils';
import { useI18n } from 'vue-i18n';
import { ProgressSpinner } from 'primevue';

const props = defineProps<{
    month: number;
    year: number;
}>();

interface Summary {
    title: string;
    minutes: number;
    hours: number;
    days: number;
}

const loading = ref<boolean>(false);
const summaries = ref<Summary[]>([]);
const { locale } = useI18n();

const load = () => {
    loading.value = true;
    fetch(route('internal-api.dashboard.avg-process-time', { month: month.value, year: year.value }))
        .then(response => response.json())
        .then((response: APIResponse<Summary[]>) => {
            if (response.error)
                return;

            summaries.value = response.data;
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
    <div class="bg-card text-card-foreground flex flex-col justify-between gap-6 rounded-2xl border p-4 shadow-xs">
        <div class="flex gap-x-1 items-baseline">
            <h3 class="text font-medium">{{ $t('label.avg_process_time') }}</h3>
            <small class="text-gray-500">{{ monthYearFormat(month, year, locale) }}</small>
        </div>

        <div class="flex items-center justify-center min-h-[100px]" v-if="loading">
            <ProgressSpinner
                style="width: 50px; height: 50px" stroke-width="8" fill="transparent"
                animation-duration=".5s" aria-label="Custom ProgressSpinner" />
        </div>
        <div class="grid lg:grid-cols-3 gap-6" v-else>
            <div v-for="summary in summaries" :key="summary.title" class="flex flex-col items-center gap-1 justify-between pb-4">
                <h2 class="font-semibold text-xl" v-if="summary.minutes >= 60">&plusmn; {{ $t('label.hour', summary.hours, { named: { hour: summary.hours } }) }}</h2>
                <h2 class="font-semibold text-xl" v-else-if="summary.minutes > 0">&lt; {{ $t('label.hour', 1, { named: { hour: 1 } }) }}</h2>
                <h2 class="font-semibold text-xl" v-else>{{ $t('label.hour', 0, { named: { hour: 0 } }) }}</h2>
                <small class="text-xs text-gray-500" v-if="summary.days > 0">
                    ({{ $t('label.day', summary.days, { named: { day: summary.days } }) }})
                </small>
                <h3 class="text-sm">
                    {{ $t(`label.${summary.title}`) }}
                    <i class="pi pi-question-circle text-gray-400 hover:text-gray-600 cursor-pointer"
                       v-tooltip="$t(`label.${summary.title}_detail`)"
                       style="font-size: 0.6rem"></i>
                </h3>
            </div>
        </div>
    </div>
</template>
