<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem, Session, SharedData } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

import { Card, useConfirm, ConfirmPopup, Tag, Button } from 'primevue';
import { dateHumanFormatWithTime, parseUserAgent } from '@/lib/utils';
import { useI18n } from 'vue-i18n';

interface Props extends SharedData {
    items: Session[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'menu.session',
        href: route('session.index'),
    },
];
const confirm = useConfirm();
const { t } = useI18n();

const destroy = (event: MouseEvent, item: Session | null) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: t('label.are_you_sure_delete'),
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: t('action.cancel'),
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: t('action.delete'),
            severity: 'danger',
        },
        accept: () => {
            const ids = item ? [item.id] : null;
            router.delete(route('session.destroy'), {
                preserveScroll: true,
                preserveState: true,
                data: { ids },
            });
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="$t('menu.session')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="$t('menu.session')"
                    :description="$t('label.session_subtitle')" />

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="item in props.items" :key="item.id">
                        <Card>
                            <template #title>
                                <div class="flex justify-between">
                                    <div class="flex items-center gap-x-1">
                                        <div>{{ parseUserAgent(item.user_agent).browser }}</div>
                                        <Tag severity="success" :value="$t('label.current')" size="small" v-if="item.is_current"></Tag>
                                    </div>
                                    <Button
                                        icon="pi pi-trash" size="small" variant="outlined" severity="danger" :disabled="item.is_current"
                                        @click="destroy($event, item)" rounded></Button>
                                </div>
                            </template>
                            <template #subtitle>
                                <small>{{ $t('label.last_active') }}: {{ dateHumanFormatWithTime(item.last_active, 0, $page.props.auth.user.locale) }}</small>
                            </template>
                            <template #content>
                                <div class="flex flex-col gap-y-2 my-4">
                                    <div class="grid lg:grid-cols-2 gap-2.5 text-sm">
                                        <div class="capitalize ">IP Address</div>
                                        <div class="lg:text-end font-medium">{{ item.ip_address }}</div>
                                    </div>
                                    <div class="grid lg:grid-cols-2 gap-2.5 text-sm">
                                        <div class="capitalize ">Device</div>
                                        <div class="lg:text-end font-medium">{{ parseUserAgent(item.user_agent).deviceType }}</div>
                                    </div>
                                    <div class="grid lg:grid-cols-2 gap-2.5 text-sm">
                                        <div class="capitalize ">OS</div>
                                        <div class="lg:text-end font-medium">{{ parseUserAgent(item.user_agent).os }}</div>
                                    </div>
                                    <div class="grid lg:grid-cols-2 gap-2.5 text-sm">
                                        <div class="capitalize ">Browser</div>
                                        <div class="lg:text-end font-medium">{{ parseUserAgent(item.user_agent).browser }}</div>
                                    </div>
                                </div>
                            </template>
                        </Card>
                    </div>
                </div>

                <ConfirmPopup />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
