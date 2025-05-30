<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import type {
    ActivityLog as ActivityLogBase, BreadcrumbItem,
    Paginate, SharedData, User
} from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import { Button, Column, ConfirmPopup, DataTable, InputText } from 'primevue';
import { dateHumanFormatWithTime } from '@/lib/utils';

interface ActivityLog extends ActivityLogBase {
    causer: User;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.activity_log',
        href: route('activity-log.index'),
    },
];

defineProps<{
    items: Paginate<ActivityLog>;
}>();

const page = usePage<SharedData>();

const filters = ref({});

const initFilter = () => {
    filters.value = {
        description: { value: null, matchMode: FilterMatchMode.CONTAINS },
    };
};

watch(filters, (newFilters) => {
    router.reload({
        only: ['items'],
        data: { filter: newFilters },
        replace: true,
    });
}, { deep: true });

onMounted(initFilter);

</script>

<template>
    <Head :title="$t('menu.activity_log')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="mb-0.5 text-base font-medium">{{ $t('menu.activity_log') }}</h3>
                    <p class="text-sm text-muted-foreground">{{ $t('label.menu_subtitle', { menu: $t('menu.activity_log') }) }}</p>
                </div>
                <div class="flex gap-x-2">
                    <Button
                        @click="router.reload({ only: ['items'], data: { filter: null }, replace: true, onSuccess: initFilter })"
                        icon="pi pi-filter-slash" severity="secondary" size="small" />
                </div>
            </header>

            <DataTable
                :value="items.data"
                :global-filter-fields="['log_name']"
                v-model:filters="filters"
                table-style="min-width: 50rem"
                filter-display="menu" scrollable
                lazy striped-rows show-gridlines>
                <Column field="created_at" :header="$t('field.created_at')">
                    <template #body="{ data }: { data: ActivityLog }">
                        {{ dateHumanFormatWithTime(data.created_at, 0, page.props.auth.user.locale) }}
                    </template>
                </Column>
                <Column field="description" :header="$t('field.description')">
                    <template #body="{ data }: { data: ActivityLog }">
                        <div v-html="data.description"></div>
                    </template>
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" :placeholder="$t('label.search_by_field', { field: $t('field.name') })" />
                    </template>
                </Column>
                <Column field="module" :header="$t('field.module')">
                    <template #body="{ data }: { data: ActivityLog }">
                        {{ $t('menu.' + data.module) }}
                    </template>
                </Column>
                <Column field="causer" :header="$t('menu.user')">
                    <template #body="{ data }: { data: ActivityLog }">
                        {{ data.causer.name }}
                    </template>
                </Column>
                <template #empty>{{ $t('label.no_data_available', { data: $t('menu.activity_log') }) }}</template>
            </DataTable>

            <Pagination :paginator="items" />

            <ConfirmPopup />
        </div>
    </AppLayout>
</template>
