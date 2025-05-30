<script setup lang="ts">
import NewCategoryModal from '@/pages/letter_category/Index/FormModal.vue';
import type { APIResponse, LetterCategory, SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    type FileUploadSelectEvent, Button, DatePicker, Dialog, FileUpload,
    FloatLabel, Image, InputText, Message, Panel,
    Skeleton, Textarea, Fieldset, MultiSelect, Checkbox
} from 'primevue';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    categories: LetterCategory[];
}>();

const page = usePage<SharedData>();
const visible = ref<boolean>(false);
const extractionLoading = ref<boolean>(false);
const previewUrl = ref<string>('');
const createMore = ref<boolean>(false);
const categoryRecommendations = ref<string[]>([]);
const categoryModal = ref();

const form = useForm<{
    [key: string]: any;
    letter_number: string;
    letter_date: Date | null;
    recipient: string;
    subject: string;
    body: string;
    summary: string;
    is_draft: boolean;
    file: File | null;
    categories: string[];
    disposition_id: string | null;
    incoming_letter_id: string | null;
}>({
    body: '',
    is_draft: false,
    letter_date: null,
    recipient: '',
    subject: '',
    summary: '',
    letter_number: '',
    file: null,
    categories: [],
    disposition_id: null,
    incoming_letter_id: null,
});

const open = (dispositionId: string | null = null, incomingLetterId: string | null = null) => {
    visible.value = true;
    form.disposition_id = dispositionId;
    form.incoming_letter_id = incomingLetterId;
};

const close = () => {
    visible.value = false;
    createMore.value = false;
    previewUrl.value = '';
    form.reset();
};

const upload = (e: FileUploadSelectEvent) => {
    const selectedFile = e.files[0];
    form.file = selectedFile;
    previewUrl.value = URL.createObjectURL(selectedFile);
};

const submit = () => {
    form.post(route('outgoing-letter.store'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            previewUrl.value = '';
            visible.value = createMore.value;
        },
    });
};

const extract = () => {
    const body = new FormData();
    if (form.file !== null) {
        extractionLoading.value = true;
        body.append('file', form.file);
        Promise.allSettled([
            axios.post(route('internal-api.outgoing-letter.extract'), body),
            axios.post(route('internal-api.outgoing-letter.category'), body)
        ]).then(responses => {
            const [extraction, categoryRecommendation] = responses;

            if (extraction.status === 'fulfilled') {
                if (extraction.value.data) {
                    const data: APIResponse<{
                        nomor_surat: string;
                        tanggal_surat: string;
                        penerima: string;
                        perihal: string;
                        body_surat: string;
                        summary: string;
                    }> = extraction.value.data;
                    const extractedData = data.data;
                    if (extractedData) {
                        form.letter_number = extractedData.nomor_surat;
                        const parsedDate = new Date(extractedData.tanggal_surat);
                        parsedDate.setHours(12); // <-- Ini yang penting
                        form.recipient = extractedData.penerima;
                        form.subject = extractedData.perihal;
                        form.body = extractedData.body_surat;
                        form.summary = extractedData.summary;
                    }
                }
            }

            if (categoryRecommendation.status === 'fulfilled') {
                if (categoryRecommendation.value.data) {
                    const data: APIResponse<{
                        existing_category: string[];
                        new_category: string[];
                    }> = categoryRecommendation.value.data;
                    const extractedData = data.data;
                    if (extractedData) {
                        form.categories = props.categories
                            .filter(category => extractedData.existing_category.includes(category.name))
                            .map(category => category.code);
                        categoryRecommendations.value = extractedData.new_category;
                    }
                }
            }
        }).finally(() => {
            extractionLoading.value = false;
        });
    }
};

defineExpose({
    open,
});
</script>

<template>
    <Dialog v-model:visible="visible" modal @after-hide="close" maximizable :header="$t('menu.outgoing_letter')" :style="{ width: '65rem' }">
        <div class="grid gap-6 pt-2 pb-8 lg:grid-cols-2" v-if="form.file">
            <div class="relative">
                <Image v-if="form.file.type.startsWith('image')" :src="previewUrl" :alt="form.file.name" preview class="h-[500px] w-full" />
                <iframe v-else-if="form.file.type.endsWith('pdf')" :src="previewUrl" width="100%" height="500px"></iframe>
                <p v-else class="text-red-500">Unsupported file type</p>
                <div class="absolute top-3 right-3" v-if="!form.summary && page.props.assistantAIAvailability">
                    <Button
                        v-tooltip.bottom="$t('label.extract_text')"
                        type="button" size="small" :disabled="extractionLoading"
                        :icon="extractionLoading ? `pi pi-spinner-dotted animate-spin` : `pi pi-sparkles `" rounded
                        severity="secondary" @click="extract"
                    ></Button>
                </div>
            </div>
            <div class="flex flex-col gap-6" v-if="extractionLoading">
                <div class="grid w-full gap-6 lg:grid-cols-2">
                    <div class="grid">
                        <Skeleton height="2.625rem" />
                    </div>
                    <div class="grid">
                        <Skeleton height="2.625rem" />
                    </div>
                </div>
                <div class="grid">
                    <Skeleton height="2.625rem" />
                </div>
                <div class="grid">
                    <Skeleton height="2.625rem" />
                </div>
                <div class="grid">
                    <Skeleton height="2.625rem" />
                </div>
                <div class="grid">
                    <Skeleton height="4.125rem" />
                </div>
                <div class="grid">
                    <Skeleton height="4.125rem" />
                </div>
            </div>
            <div class="flex flex-col gap-6" v-else>
                <div class="grid w-full gap-6 lg:grid-cols-2">
                    <div class="grid">
                        <FloatLabel variant="on">
                            <InputText
                                :fluid="true"
                                :autofocus="true"
                                id="letter_number"
                                v-model="form.letter_number"
                                type="text"
                                autocomplete="off"
                            />
                            <label for="letter_number" class="text-sm">{{ $t('field.letter_number') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.letter_number" severity="error" size="small" variant="simple">
                            {{ form.errors.letter_number }}
                        </Message>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <DatePicker
                                date-format="dd/mm/yy"
                                fluid input-id="letter_date" v-model="form.letter_date" />
                            <label for="letter_date" class="text-sm">{{ $t('field.letter_date') }}</label>
                        </FloatLabel>
                        <Message v-if="form.errors.letter_date" severity="error" size="small" variant="simple">
                            {{ form.errors.letter_date }}
                        </Message>
                    </div>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <InputText :fluid="true" id="recipient" v-model="form.recipient" type="text" />
                        <label for="recipient" class="text-sm">{{ $t('field.recipient') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.recipient" severity="error" size="small" variant="simple">
                        {{ form.errors.recipient }}
                    </Message>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <InputText :fluid="true" id="subject" v-model="form.subject" type="text" />
                        <label for="subject" class="text-sm">{{ $t('field.subject') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.subject" severity="error" size="small" variant="simple">
                        {{ form.errors.subject }}
                    </Message>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <Textarea
                            :fluid="true" id="body" v-model="form.body" />
                        <label for="body" class="text-sm">{{ $t('field.body') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.body" severity="error" size="small" variant="simple">
                        {{ form.errors.body }}
                    </Message>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <MultiSelect
                            :max-selected-labels="3" show-clear :options="categories" option-label="name" option-value="code" filter
                            fluid input-id="categories" v-model="form.categories">
                            <template #footer>
                                <div class="p-3 flex justify-between" v-if="page.props.auth.allow.add_letter_category">
                                    <Button
                                        @click="() => categoryModal?.open(null)"
                                        :label="$t('action.new_menu', { menu: $t('menu.letter_category') })" severity="secondary"
                                        text size="small" icon="pi pi-plus" />
                                </div>
                            </template>
                        </MultiSelect>
                        <label for="categories" class="text-sm">{{ $t('menu.letter_category') }}</label>
                    </FloatLabel>
                    <small v-if="categoryRecommendations.length > 0" class="text-gray-500">
                        {{ $t('label.recommendation') }}: {{ categoryRecommendations.join(', ') }}
                    </small>
                    <Message v-if="form.errors.categories" severity="error" size="small" variant="simple">
                        {{ form.errors.categories }}
                    </Message>
                </div>
                <div class="grid">
                    <Fieldset v-if="form.summary">
                        <template #legend>
                            <div class="flex items-center pl-2">
                                <i class="pi pi-sparkles"></i>
                                <span class="font-bold p-2">{{ $t('label.summary_by_ai') }}</span>
                            </div>
                        </template>
                        <p class="m-0" v-html="form.summary"></p>
                    </Fieldset>
                </div>
            </div>
        </div>
        <div class="pb-8" v-else>
            <Panel>
                <template #header>
                    <FileUpload mode="basic" accept="image/*,application/pdf" :maxFileSize="1000000" @select="upload" />
                </template>
            </Panel>
        </div>

        <div class="flex justify-between items-center">
            <div>
                <div class="flex items-center gap-2" v-if="form.file && !form.incoming_letter_id">
                    <Checkbox v-model="createMore" input-id="create_more" binary />
                    <label for="create_more">{{ $t('label.create_more') }}</label>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
                <Button
                    type="button"
                    size="small"
                    :label="$t('action.submit')"
                    :loading="form.processing"
                    :disabled="form.processing || form.file === null"
                    @click="submit"
                ></Button>
            </div>
        </div>
    </Dialog>

    <NewCategoryModal ref="categoryModal" />
</template>
