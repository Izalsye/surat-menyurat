<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { DatePicker } from 'primevue';
import { computed, ref } from 'vue';
import Statistic from '@/pages/dashboard/Dashboard/Statistic.vue';
import AvgProcessTime from '@/pages/dashboard/Dashboard/AvgProcessTime.vue';
import DispositionPunctuality from '@/pages/dashboard/Dashboard/DispositionPunctuality.vue';
import DispositionConversionRate from '@/pages/dashboard/Dashboard/DispositionConversionRate.vue';

interface Props {
    summaries: { title: string; total: number; link: string; allow: boolean; }[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.dashboard',
        href: route('dashboard'),
    },
];

const page = usePage<SharedData>();
const params = new URLSearchParams(window.location.search);
const selectedMonth = ref<Date>(
    new Date(Number(params.get('year') ?? new Date().getFullYear()), Number(params.get('month') ?? new Date().getMonth() + 1) - 1, 10),
);
const month = computed<number>(() => selectedMonth.value.getMonth() + 1);
const year = computed<number>(() => selectedMonth.value.getFullYear());

const changeMonth = () => {
    const url = new URL(window.location.href);
    url.searchParams.set('month', (selectedMonth.value.getMonth() + 1).toString());
    url.searchParams.set('year', selectedMonth.value.getFullYear().toString());

    router.get(url.toString(), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head :title="$t('menu.dashboard')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="grid auto-rows-min gap-6 md:grid-cols-4">
                <div v-for="summary in summaries" :key="summary.title" class="bg-card text-card-foreground flex flex-col gap-0.5 rounded-2xl border p-4 shadow-xs">
                    <h2 :class="{ 'blur-xs hover:blur-none ease-in-out duration-150': !summary.allow }" class="font-semibold text-2xl">{{ summary.total }}</h2>
                    <div>
                        <Link v-if="summary.allow" :href="summary.link" class="text-sm hover:text-emerald-500 hover:underline">
                            {{ summary.title }}
                        </Link>
                        <span v-else class="text-sm">
                            {{ summary.title }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h3 class="mb-0.5 text-base font-medium">{{ $t('menu.dashboard') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $t('label.menu_subtitle', { menu: $t('menu.dashboard') }) }}</p>
                </div>
                <div class="flex gap-x-2">
                    <DatePicker
                        @date-select="changeMonth" v-model="selectedMonth"
                        class="cursor-pointer" :max-date="new Date()" show-icon icon-display="input"
                        view="month" date-format="MM yy" size="small"
                        :manual-input="false" />
                </div>
            </div>

            <div class="grid auto-rows-min gap-6 lg:grid-cols-4">
                <AvgProcessTime class="lg:col-span-2"
                    :month :year />
                <DispositionPunctuality
                    :month :year />
                <DispositionConversionRate
                    :month :year />
            </div>

            <Statistic
                v-if="page.props.auth.allow.view_incoming_letter || page.props.auth.allow.view_outgoing_letter || page.props.auth.allow.view_disposition"
                :month :year />

        </div>
    </AppLayout>
</template>
