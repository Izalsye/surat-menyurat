<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import {
    SelectButton, Avatar, FloatLabel, Message, InputText,
    Button,
    FileUpload,
    Image,
} from 'primevue';
import { useI18n } from 'vue-i18n';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'menu.profile',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const { locale } = useI18n();

const form = useForm({
    name: user.name,
    email: user.email,
    locale: user.locale,
    signature_file: null as File | null,
    remove_signature: false,
});

const submit = () => {
    // Since we have a file upload, we need to use POST for multipart/form-data.
    // Inertia's form helper will automatically include a _method: 'PATCH' field.
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            locale.value = form.locale;
            form.signature_file = null; // Reset file input after successful upload
            form.remove_signature = false;
        },
        onError: () => {
            // Handle errors if needed
        }
    });
};

const handleSignatureUpload = (event: { files: File[] }) => {
    if (event.files && event.files.length > 0) {
        form.signature_file = event.files[0];
        form.remove_signature = false; // If a new file is uploaded, we don't want to remove it
    }
};

const removeSignature = () => {
    form.remove_signature = true;
    form.signature_file = null; // Clear any selected file
    // Optionally, immediately hide the preview or update UI
    // This will be handled by the v-if on the Image component based on user.signature_url and form.signature_file
};

type localeType = 'id' | 'en' | 'ko' | 'ja' | 'ar' | 'zh-CN';

interface Locale {
    code: localeType;
    name: string;
    flag: string;
}

const locales: Locale[] = [
    { code: 'ar', name: 'اَلْعَرَبِيَّةُ', flag: 'https://flagsapi.com/SA/flat/64.png' },
    { code: 'en', name: 'English', flag: 'https://flagsapi.com/GB/flat/64.png' },
    { code: 'id', name: 'Bahasa Indonesia', flag: 'https://flagsapi.com/ID/flat/64.png' },
    { code: 'ja', name: '日本語', flag: 'https://flagsapi.com/JP/flat/64.png' },
    { code: 'ko', name: '한국어', flag: 'https://flagsapi.com/KR/flat/64.png' },
    { code: 'zh-CN', name: '中文', flag: 'https://flagsapi.com/CN/flat/64.png' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="$t('menu.profile')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall
                    :title="$t('menu.profile')"
                    :description="$t('label.profile_subtitle')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true" :autofocus="true" id="name"
                                v-model="form.name" type="text" autocomplete="off" />
                            <label for="name" class="text-sm">{{ $t('field.name') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.name" severity="error" size="small" variant="simple">
                            {{ form.errors.name }}
                        </Message>
                    </div>

                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true" id="email"
                                v-model="form.email" type="text" autocomplete="off" />
                            <label for="email" class="text-sm">{{ $t('field.email') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.email" severity="error" size="small" variant="simple">
                            {{ form.errors.email }}
                        </Message>
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div>
                        <SelectButton
                            size="small" v-model="form.locale" option-value="code"
                            :options="locales">
                            <template #option="{ option }: { option: Locale }">
                                <Avatar
                                    :image="option.flag"
                                    shape="circle" class="me-1" />
                                {{ option.code }}
                            </template>
                        </SelectButton>
                    </div>

                    <div>
                        <label for="signature" class="text-sm">{{ $t('field.signature') }}</label>
                        <FileUpload
                            name="signature_file"
                            mode="basic"
                            accept="image/*"
                            :auto="true"
                            :customUpload="true"
                            @uploader="handleSignatureUpload"
                            :chooseLabel="$t('action.choose_signature')"
                            class="mt-2"
                        />
                        <Message v-if="form.errors.signature_file" severity="error" size="small" variant="simple">
                            {{ form.errors.signature_file }}
                        </Message>

                        <div v-if="user.signature_url && !form.signature_file" class="mt-4">
                            <p class="text-sm mb-2">{{ $t('label.current_signature') }}:</p>
                            <Image :src="user.signature_url" alt="Current Signature" width="150" preview />
                            <Button
                                type="button"
                                severity="danger"
                                size="small"
                                :label="$t('action.remove_signature')"
                                @click="removeSignature"
                                class="mt-2"
                                :disabled="form.processing"
                            />
                        </div>
                         <div v-if="form.signature_file" class="mt-4">
                            <p class="text-sm mb-2">{{ $t('label.new_signature_preview') }}:</p>
                            <Image :src="Object.createObjectURL(form.signature_file)" alt="New Signature Preview" width="150" preview />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="button" size="small"
                            :label="$t('action.submit')"
                            :loading="form.processing"
                            :disabled="form.processing"
                            @click="submit"></Button>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
