<script setup lang="ts">
import type { IncomingLetter as IncomingLetterBase, LetterCategory, SharedData } from '@/types';
import NewCategoryModal from '@/pages/letter_category/Index/FormModal.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Button, DatePicker, Dialog, MultiSelect,
    FloatLabel, Image, InputText, Message, Textarea, Fieldset,
} from 'primevue';
import { ref } from 'vue';

interface IncomingLetter extends IncomingLetterBase {
    letter_categories: LetterCategory[];
}

defineProps<{
    categories: LetterCategory[];
}>();

const page = usePage<SharedData>();
const visible = ref<boolean>(false);
const letter = ref<IncomingLetter | null>(null);
const categoryModal = ref();

const form = useForm<{
    [key: string]: any;
    letter_number: string;
    letter_date: Date | null;
    sender: string;
    institution: string;
    subject: string;
    body: string;
    summary: string;
    is_draft: boolean;
    categories: string[];
    created_at: Date | null;
}>({
    body: '',
    is_draft: false,
    letter_date: null,
    sender: '',
    institution: '',
    subject: '',
    summary: '',
    letter_number: '',
    categories: [],
    created_at: null,
});

const open = (item: IncomingLetter) => {
    visible.value = true;
    letter.value = item;
    form.body = item.body ?? '';
    if (typeof item.letter_date === 'string') {
        form.letter_date = new Date(item.letter_date);
    } else {
        form.letter_date = item.letter_date;
    }
    form.sender = item.sender ?? '';
    form.institution = item.institution ?? '';
    form.subject = item.subject ?? '';
    form.letter_number = item.letter_number ?? '';
    form.summary = item.summary ?? '';
    form.is_draft = item.is_draft ?? false;
    form.categories = item.letter_categories.map(category => category.code);
    if (typeof item.created_at === 'string') {
        form.created_at = new Date(item.created_at);
    } else {
        form.created_at = item.created_at;
    }
};

const close = () => {
    visible.value = false;
    form.reset();
};

const submit = () => {
    form.put(route('incoming-letter.update', letter.value?.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: close,
    });
};

defineExpose({
    open,
});
</script>

<template>
    <Dialog v-model:visible="visible" modal @after-hide="close" maximizable :header="$t('menu.incoming_letter')" :style="{ width: '65rem' }">
        <div class="grid gap-6 pt-2 pb-8 lg:grid-cols-2" v-if="letter">
            <div class="relative">
                <iframe
                    v-if="letter.file_url.endsWith('pdf')"
                    :src="letter.file_url" width="100%"
                    height="500px"></iframe>
                <Image
                    v-else
                    :src="letter.file_url" :alt="letter.subject"
                    preview class="h-[500px] w-full" />
            </div>
            <div class="flex flex-col gap-6">
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
                        <InputText :fluid="true" id="sender" v-model="form.sender" type="text" />
                        <label for="sender" class="text-sm">{{ $t('field.sender') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.sender" severity="error" size="small" variant="simple">
                        {{ form.errors.sender }}
                    </Message>
                </div>
                <div class="grid">
                    <FloatLabel variant="on">
                        <InputText :fluid="true" id="institution" v-model="form.institution" type="text" />
                        <label for="institution" class="text-sm">{{ $t('field.institution') }}</label>
                    </FloatLabel>
                    <Message v-if="form.errors.institution" severity="error" size="small" variant="simple">
                        {{ form.errors.institution }}
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
                <div class="grid w-full gap-6 lg:grid-cols-2">
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
                        <Message v-if="form.errors.categories" severity="error" size="small" variant="simple">
                            {{ form.errors.categories }}
                        </Message>
                    </div>
                    <div class="grid">
                        <FloatLabel variant="on">
                            <DatePicker
                                date-format="dd/mm/yy"
                                showTime
                                hourFormat="24"
                                fluid
                                input-id="created_at"
                                v-model="form.created_at"
                            />
                            <label for="created_at" class="text-sm">{{ $t('field.created_at') }}</label>
                        </FloatLabel>

                        <Message v-if="form.errors.created_at" severity="error" size="small" variant="simple">
                            {{ form.errors.created_at }}
                        </Message>
                    </div>
                </div>
                
                <div class="grid">
                    <Fieldset v-if="letter.summary">
                        <template #legend>
                            <div class="flex items-center pl-2">
                                <i class="pi pi-sparkles"></i>
                                <span class="font-bold p-2">{{ $t('label.summary_by_ai') }}</span>
                            </div>
                        </template>
                        <p class="m-0" v-html="letter.summary"></p>
                    </Fieldset>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" size="small" :label="$t('action.cancel')" severity="secondary" @click="close"></Button>
            <Button
                type="button"
                size="small"
                :label="$t('action.submit')"
                :loading="form.processing"
                :disabled="form.processing"
                @click="submit"
            ></Button>
        </div>
    </Dialog>

    <NewCategoryModal ref="categoryModal" />
</template>
