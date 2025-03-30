<script setup lang="ts">
import { computed, ref } from 'vue';

interface FilePreview {
    file: File;
    url: string;
}

interface Props {
    /** Enable multiple file selection */
    multiple?: boolean;
    /** Maximum number of files (only applies when multiple is true, 0 for unlimited) */
    maxFiles?: number;
    /** Maximum file size in bytes (default: 5MB) */
    maxFileSize?: number;
    /** Accepted file MIME types */
    acceptedFileTypes?: string[];
    /** Model value (selected file or files) */
    modelValue?: File | File[] | null;
    /** Existing image URL */
    existingImageUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    multiple: false,
    maxFiles: 0,
    maxFileSize: 5 * 1024 * 1024,
    acceptedFileTypes: () => ['image/jpeg', 'image/png', 'image/gif', 'image/svg'],
    modelValue: null,
    existingImageUrl: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | File[] | null): void;
    (e: 'image-uploaded', file: File | File[]): void;
    (e: 'image-removed', index?: number): void;
}>();

// Reactive state
const isDragging = ref(false);
const singlePreview = ref<string | null>(null);
const filePreviews = ref<FilePreview[]>([]);
const errorMessage = ref<string>('');
const fileInput = ref<HTMLInputElement | null>(null);

// Computed properties
const isEmpty = computed((): boolean => {
    if (props.existingImageUrl && !props.multiple && !singlePreview.value) {
        return false;
    }
    return props.multiple ? filePreviews.value.length === 0 : !singlePreview.value;
});

const fileTypesFormatted = computed((): string => {
    return props.acceptedFileTypes.map((type) => type.split('/')[1].toUpperCase()).join(', ');
});

const maxFileSizeMB = computed((): number => {
    return Math.round(props.maxFileSize / 1024 / 1024);
});

// Methods
const openFileDialog = (): void => {
    fileInput.value?.click();
};

const handleDrop = (e: DragEvent): void => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        if (props.multiple) {
            // Convert FileList to array for multiple mode
            const fileArray = Array.from(files);
            processMultipleFiles(fileArray);
        } else {
            // For single mode, only use the first file
            processSingleFile(files[0]);
        }
    }
};

const handleFileChange = (e: Event): void => {
    const target = e.target as HTMLInputElement;
    const files = target.files;

    if (files && files.length > 0) {
        if (props.multiple) {
            // Convert FileList to array for multiple mode
            const fileArray = Array.from(files);
            processMultipleFiles(fileArray);
        } else {
            // For single mode, only use the first file
            processSingleFile(files[0]);
        }
    }
};

const validateFile = (file: File): boolean => {
    // Check file type
    if (!props.acceptedFileTypes.includes(file.type)) {
        errorMessage.value = `File type not accepted. Please upload ${fileTypesFormatted.value} files.`;
        return false;
    }

    // Check file size
    if (file.size > props.maxFileSize) {
        errorMessage.value = `File is too large. Maximum size is ${maxFileSizeMB.value} MB.`;
        return false;
    }

    return true;
};

const processSingleFile = (file: File): void => {
    errorMessage.value = '';
    if (!validateFile(file)) return;

    const reader = new FileReader();
    reader.onload = (e: ProgressEvent<FileReader>) => {
        singlePreview.value = e.target?.result as string;
        emit('update:modelValue', file);
        emit('image-uploaded', file);
    };
    reader.readAsDataURL(file);
};

const processMultipleFiles = (newFiles: File[]): void => {
    errorMessage.value = '';

    // Filter out invalid files
    const validFiles = newFiles.filter((file) => validateFile(file));

    if (validFiles.length === 0) return;

    // Check max files limit
    if (props.maxFiles > 0) {
        const totalFiles = filePreviews.value.length + validFiles.length;
        if (totalFiles > props.maxFiles) {
            errorMessage.value = `You can upload a maximum of ${props.maxFiles} files.`;
            // Process only up to the max allowed
            validFiles.splice(0, validFiles.length - (props.maxFiles - filePreviews.value.length));
            if (validFiles.length === 0) return;
        }
    }

    // Process each valid file
    const filePromises = validFiles.map((file) => {
        return new Promise<FilePreview>((resolve) => {
            const reader = new FileReader();
            reader.onload = (e: ProgressEvent<FileReader>) => {
                resolve({
                    file: file,
                    url: e.target?.result as string,
                });
            };
            reader.readAsDataURL(file);
        });
    });

    // When all files are processed
    Promise.all(filePromises).then((newPreviews) => {
        // Add new files to existing ones
        filePreviews.value = [...filePreviews.value, ...newPreviews];

        // Extract just the File objects for the emit
        const allFiles = filePreviews.value.map((preview) => preview.file);
        emit('update:modelValue', allFiles);
        emit('image-uploaded', allFiles);
    });
};

const removeSingleImage = (): void => {
    singlePreview.value = null;
    emit('update:modelValue', null);
    emit('image-removed');
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const removeImage = (index: number): void => {
    filePreviews.value.splice(index, 1);

    // Update model value
    if (filePreviews.value.length === 0) {
        emit('update:modelValue', null);
    } else {
        const files = filePreviews.value.map((preview) => preview.file);
        emit('update:modelValue', files);
    }

    emit('image-removed', index);
};
</script>

<template>
    <div class="w-full">
        <!-- Upload area when no images are selected -->
        <div
            v-if="isEmpty"
            class="cursor-pointer rounded-lg border-2 border-dashed p-4 text-center transition-colors"
            :class="{ 'border-primary bg-primary/5': isDragging, 'border-border hover:border-muted-foreground': !isDragging }"
            @dragenter.prevent="isDragging = true"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="openFileDialog"
        >
            <input type="file" ref="fileInput" class="hidden" :accept="acceptedFileTypes.join(',')" @change="handleFileChange" :multiple="multiple" />
            <div class="flex flex-col items-center justify-center py-6">
                <!-- Upload icon -->
                <div class="mb-4 text-muted-foreground">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="h-10 w-10"
                    >
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                </div>
                <p class="mb-1 font-medium text-foreground">
                    {{
                        isDragging
                            ? 'Drop your image' + (multiple ? 's' : '') + ' here'
                            : 'Drag & drop ' + (multiple ? 'images' : 'an image') + ' or click to browse'
                    }}
                </p>
                <p class="text-xs text-muted-foreground">Supported formats: {{ fileTypesFormatted }}</p>
                <p class="text-xs text-muted-foreground">Max size: {{ maxFileSizeMB }} MB {{ multiple ? 'per file' : '' }}</p>
                <p v-if="multiple && maxFiles > 0" class="text-xs text-muted-foreground">Max files: {{ maxFiles }}</p>
            </div>
        </div>

        <!-- Single image preview -->
        <div v-else-if="!multiple && (singlePreview || existingImageUrl)" class="relative h-64 w-64 overflow-hidden rounded-lg border">
            <img :src="singlePreview || existingImageUrl" alt="Preview" class="max-h-64 w-full object-cover" />
            <button
                type="button"
                class="absolute right-2 top-2 rounded-full bg-background/80 p-1 text-foreground shadow-sm transition-colors hover:bg-background"
                @click.stop="removeSingleImage"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="h-5 w-5"
                >
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>

        <!-- Multiple images preview -->
        <div v-else class="space-y-3">
            <!-- Add more button -->
            <div
                v-if="multiple && (!maxFiles || filePreviews.length < maxFiles)"
                class="cursor-pointer rounded-lg border-2 border-dashed p-2 text-center transition-colors hover:border-muted-foreground"
                @click="openFileDialog"
            >
                <input
                    ref="fileInput"
                    type="file"
                    :multiple="multiple"
                    :accept="acceptedFileTypes.join(',')"
                    class="hidden"
                    @change="handleFileChange"
                />
                <div class="flex items-center justify-center gap-2 py-2">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="24"
                        height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="h-5 w-5"
                    >
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span class="text-sm font-medium">Add {{ filePreviews.length > 0 ? 'more' : '' }} images</span>
                </div>
            </div>

            <!-- Images grid -->
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                <div v-for="(preview, index) in filePreviews" :key="index" class="relative aspect-square overflow-hidden rounded-lg border">
                    <img :src="preview.url" :alt="`Preview ${index + 1}`" class="h-full w-full object-cover" />
                    <button
                        class="absolute right-2 top-2 rounded-full bg-background/80 p-1 text-foreground shadow-sm transition-colors hover:bg-background"
                        @click="removeImage(index)"
                        type="button"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-5 w-5"
                        >
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Error message display -->
        <div v-if="errorMessage" class="mt-2 text-sm text-destructive">
            {{ errorMessage }}
        </div>
    </div>
</template>
