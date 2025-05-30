<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import {
    FloatLabel, Message, Password,
    Button,
} from 'primevue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'menu.password',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="$t('menu.password')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="$t('menu.password')"
                    :description="$t('label.password_subtitle')" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid">
                        <FloatLabel variant="on">
                            <Password v-model="form.current_password" :autofocus="true" inputId="current_password" :fluid="true" :feedback="false" toggleMask required />
                            <label class="text-sm" for="current_password">{{ $t('field.current_password') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.current_password" severity="error" size="small" variant="simple">{{ form.errors.current_password }}</Message>
                    </div>

                    <div class="grid">
                        <FloatLabel variant="on">
                            <Password v-model="form.password" inputId="password" :fluid="true" toggleMask required />
                            <label class="text-sm" for="password">{{ $t('field.new_password') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.password" severity="error" size="small" variant="simple">{{ form.errors.password }}</Message>
                    </div>

                    <div class="grid">
                        <FloatLabel variant="on">
                            <Password
                                :invalid="form.password !== form.password_confirmation"
                                v-model="form.password_confirmation"
                                inputId="password_confirmation" :fluid="true"
                                :feedback="false" toggleMask required />
                            <label class="text-sm" for="password_confirmation">{{ $t('field.password_confirmation') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.password_confirmation" severity="error" size="small" variant="simple">{{ form.errors.password_confirmation }}</Message>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit" size="small"
                            :label="$t('action.submit')"
                            :loading="form.processing"
                            :disabled="form.processing"></Button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
