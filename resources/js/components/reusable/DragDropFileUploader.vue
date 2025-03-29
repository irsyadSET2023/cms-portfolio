<script setup lang="ts">
import { computed, ref } from 'vue';

interface FilePreview {
    file: File;
    url: string;
}

interface Props {
    multiple?: boolean;
    maxFiles?: number;
    maxFileSize?: number;
    acceptedFileTypes?: string[];
    modelValue?: File | File[] | null;
}

const props = withDefaults(defineProps<Props>(), {
    multiple: false,
    maxFiles: 0,
    maxFileSize: 5 * 1024 * 1024, // 5MB
    acceptedFileTypes: () => [
        'application/pdf',
        'application/msword',
        'text/csv',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
    modelValue: null,
});

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | File[] | null): void;
    (e: 'file-uploaded', file: File | File[]): void;
    (e: 'file-removed', index?: number): void;
}>();

const isDragging = ref(false);
const singlePreview = ref<string | null>(null);
const filePreviews = ref<FilePreview[]>([]);
const errorMessage = ref<string>('');
const fileInput = ref<HTMLInputElement | null>(null);

const isEmpty = computed(() => (props.multiple ? filePreviews.value.length === 0 : !singlePreview.value));
const fileTypesFormatted = computed(() => props.acceptedFileTypes.map((type) => type.split('/')[1].toUpperCase()).join(', '));
const maxFileSizeMB = computed(() => Math.round(props.maxFileSize / 1024 / 1024));

const openFileDialog = () => fileInput.value?.click();

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (!e.dataTransfer?.files.length) return;
    props.multiple ? processMultipleFiles(Array.from(e.dataTransfer.files)) : processSingleFile(e.dataTransfer.files[0]);
};

const handleFileChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (!files || files.length === 0) return;
    props.multiple ? processMultipleFiles(Array.from(files)) : processSingleFile(files[0]);
};

const validateFile = (file: File): boolean => {
    if (!props.acceptedFileTypes.includes(file.type)) {
        errorMessage.value = `Invalid file type. Allowed: ${fileTypesFormatted.value}`;
        return false;
    }
    if (file.size > props.maxFileSize) {
        errorMessage.value = `File too large. Max: ${maxFileSizeMB.value} MB`;
        return false;
    }
    return true;
};

const processSingleFile = (file: File) => {
    errorMessage.value = '';
    if (!validateFile(file)) return;
    singlePreview.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
    emit('file-uploaded', file);
};

const processMultipleFiles = (newFiles: File[]) => {
    errorMessage.value = '';
    let validFiles = newFiles.filter(validateFile);
    if (!validFiles.length) return;

    if (props.maxFiles > 0 && filePreviews.value.length + validFiles.length > props.maxFiles) {
        errorMessage.value = `Max ${props.maxFiles} files allowed.`;
        validFiles = validFiles.slice(0, props.maxFiles - filePreviews.value.length);
    }

    const newPreviews = validFiles.map((file) => ({
        file,
        url: URL.createObjectURL(file),
    }));

    filePreviews.value = [...filePreviews.value, ...newPreviews];
    emit(
        'update:modelValue',
        filePreviews.value.map((p) => p.file),
    );
    emit('file-uploaded', validFiles);
};

const removeSingleFile = () => {
    singlePreview.value = null;
    emit('update:modelValue', null);
    emit('file-removed');
    if (fileInput.value) fileInput.value.value = '';
};

const removeFile = (index: number) => {
    filePreviews.value.splice(index, 1);
    emit('update:modelValue', filePreviews.value.length ? filePreviews.value.map((p) => p.file) : null);
    emit('file-removed', index);
};
</script>

<template>
    <div class="w-full">
        <!-- Upload Area -->
        <div
            v-if="isEmpty"
            class="cursor-pointer rounded-lg border-2 border-dashed p-4 text-center transition-colors"
            :class="{ 'border-primary bg-primary/5': isDragging, 'border-gray-300 hover:border-gray-500': !isDragging }"
            @dragenter.prevent="isDragging = true"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="openFileDialog"
        >
            <input type="file" ref="fileInput" class="hidden" :accept="acceptedFileTypes.join(',')" @change="handleFileChange" :multiple="multiple" />
            <div class="py-6">
                <p class="mb-1 font-medium text-gray-700">
                    {{ isDragging ? 'Drop files here' : 'Drag & drop files or click to upload' }}
                </p>
                <p class="text-sm text-gray-500">Formats: {{ fileTypesFormatted }} | Max: {{ maxFileSizeMB }} MB</p>
                <p v-if="multiple && maxFiles > 0" class="text-sm text-gray-500">Max files: {{ maxFiles }}</p>
            </div>
        </div>

        <!-- Single File Preview -->
        <div v-else-if="!multiple && singlePreview" class="relative flex items-center space-x-4 rounded-lg border bg-white p-4 shadow-sm">
            <!-- Image Preview -->
            <div v-if="modelValue && !Array.isArray(modelValue) && modelValue.type.startsWith('image/')" class="h-24 w-24">
                <img :src="singlePreview" alt="Uploaded Image" class="h-full w-full rounded-md border object-cover" />
            </div>

            <!-- File Info -->
            <div class="flex-1">
                <div class="flex items-center space-x-2">
                    <!-- File Icon -->
                    <svg
                        v-if="modelValue && !Array.isArray(modelValue) && !modelValue?.type.startsWith('image/')"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-gray-500"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>

                    <!-- File Name -->
                    <a v-if="!Array.isArray(modelValue)" :href="singlePreview" target="_blank" class="w-full truncate text-blue-600 underline">
                        {{ modelValue?.name }}
                    </a>
                </div>
            </div>

            <!-- Remove Button -->
            <button
                class="absolute right-2 top-2 rounded-full bg-gray-100 p-2 text-gray-600 transition hover:bg-gray-200"
                @click="removeSingleFile"
                aria-label="Remove file"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Multiple File Previews -->
        <div v-else class="grid grid-cols-2 gap-3 md:grid-cols-3">
            <div
                v-for="(preview, index) in filePreviews"
                :key="index"
                class="relative flex flex-col items-center space-y-2 rounded-lg border bg-white p-4 shadow-sm"
            >
                <!-- Image Preview -->
                <div v-if="preview.file.type.startsWith('image/')" class="h-24 w-24">
                    <img :src="preview.url" alt="Uploaded Image" class="h-full w-full rounded-md border object-cover" />
                </div>

                <!-- File Name -->
                <a :href="preview.url" target="_blank" class="w-full truncate text-center text-blue-600 underline">
                    {{ preview.file.name }}
                </a>

                <!-- Remove Button -->
                <button
                    class="absolute right-2 top-2 rounded-full bg-gray-100 p-2 text-gray-600 transition hover:bg-gray-200"
                    @click="removeFile(index)"
                    aria-label="Remove file"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="errorMessage" class="mt-2 text-sm text-red-500">{{ errorMessage }}</p>
    </div>
</template>
