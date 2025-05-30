<script setup lang="ts">
import VueApexCharts from 'vue3-apexcharts';
import { APIResponse, SharedData } from '@/types';
import { onMounted, ref, toRefs, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import { monthYearFormat } from '@/lib/utils';

const props = defineProps<{
    month: number;
    year: number;
}>();

const page = usePage<SharedData>();
const { t, locale } = useI18n();

const series = ref<any[]>([]);
const categories = ref<string[]>([]);
const options = ref({
    chart: {
        type: 'bar',
        height: 350,
        toolbar: { show: true }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded',
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: categories.value,
        labels: {
            show: true,
        }
    },
    stroke: {
        curve: 'smooth',
    },
    legend: {
        position: 'top',
    },
    grid: {
        xaxis: {
            lines: {
                show: true
            }
        }
    },
});

const totalIncoming = ref<number>(0);
const totalOutgoing = ref<number>(0);
const totalDisposition = ref<number>(0);

const load = () => {
    fetch(route('internal-api.dashboard.statistic', { month: month.value, year: year.value }))
        .then(response => response.json())
        .then((response: APIResponse<Record<string, { disposition: number; outgoing: number; incoming: number; }>>) => {
            if (response.error)
                return;

            const labels: string[] = Object.keys(response.data);
            const incomingData = labels.map((date: string) => response.data[date].incoming);
            const outgoingData = labels.map((date: string) => response.data[date].outgoing);
            const dispositionData = labels.map((date: string) => response.data[date].disposition);

            categories.value = labels;
            series.value = [];
            if (page.props.auth.allow.view_incoming_letter) {
                series.value.push({ name: t('menu.incoming_letter'), data: incomingData, });
            }
            if (page.props.auth.allow.view_outgoing_letter) {
                series.value.push({ name: t('menu.outgoing_letter'), data: outgoingData, });
            }
            if (page.props.auth.allow.view_disposition) {
                series.value.push({ name: t('menu.disposition'), data: dispositionData, });
            }

            options.value.xaxis.categories = labels;

            totalIncoming.value = incomingData.reduce((a, b) => a + b, 0);
            totalOutgoing.value = outgoingData.reduce((a, b) => a + b, 0);
            totalDisposition.value = dispositionData.reduce((a, b) => a + b, 0);
        });
};

const { month, year } = toRefs(props);
watch([month, year], load);

onMounted(() => {
    load();
});
</script>

<template>
    <div class="bg-card text-card-foreground flex flex-col gap-3 lg:gap-0.5 rounded-2xl border p-4 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:justify-between gap-3">
            <div class="flex gap-x-1 items-baseline">
                <h3 class="text font-medium">{{ t('label.statistic') }}</h3>
                <small class="text-gray-500">{{ monthYearFormat(month, year, locale) }}</small>
            </div>
            <div class="flex gap-x-3 justify-between lg:justify-end">
                <div class="flex flex-col lg:flex-row gap-x-1 items-center lg:items-baseline">
                    <h3 class="text font-medium">{{ totalIncoming }}</h3>
                    <small class="text-gray-500">{{ t('menu.incoming_letter') }}</small>
                </div>
                <div class="flex flex-col lg:flex-row gap-x-1 items-center lg:items-baseline">
                    <h3 class="text font-medium">{{ totalOutgoing }}</h3>
                    <small class="text-gray-500">{{ t('menu.outgoing_letter') }}</small>
                </div>
                <div class="flex flex-col lg:flex-row gap-x-1 items-center lg:items-baseline">
                    <h3 class="text font-medium">{{ totalDisposition }}</h3>
                    <small class="text-gray-500">{{ t('menu.disposition') }}</small>
                </div>
            </div>
        </div>
        <VueApexCharts
            type="bar" height="350"
            :options="options" :series="series" />
    </div>
</template>
