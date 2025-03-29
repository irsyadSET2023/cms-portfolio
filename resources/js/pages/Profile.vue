<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import DragDropImageUploader from '@/components/reusable/DragDropImageUploader.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormLabel, FormMessage } from '@/components/ui/form';
import FormItem from '@/components/ui/form/FormItem.vue';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { QuillEditor } from '@vueup/vue-quill';
import { useForm as useVeeForm } from 'vee-validate';
import { z } from 'zod';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

const formSchema = toTypedSchema(
    z.object({
        name: z.string(),
        email: z.string(),
        image: z.any(),
        description: z.string().optional(),
    }),
);

const page = usePage<SharedData>();
const user = ref<User>(page.props.auth.user as User); // Make user reactive

const { handleSubmit, setFieldError, resetField, defineField } = useVeeForm({
    validationSchema: formSchema,
    initialValues: {
        name: user.value.name,
        email: user.value.email,
        image: null,
    },
});

const [content] = defineField('description');

const form = useForm<{ name: string; email: string; image: File | File[] | null; description: string }>({
    name: user.value.name,
    email: user.value.email,
    image: null,
    description: '',
});

const getFile = (file: File | File[]) => {
    resetField('image');
    if (Array.isArray(file) && file.length > 0) {
        form.image = file; // Take the first file from the array
    } else if (file instanceof File) {
        form.image = file;
    } else {
        form.image = null;
    }
};

const onSubmit = handleSubmit((values) => {
    console.log(form.image, form.name, form.email);

    form.post(route('profile.update'), {
        onSuccess: () => {
            toast({
                title: 'Success',
                description: 'Success update Profile',
            });
        },
        onError: (errors) => {
            toast({
                title: 'Error',
                description: 'Error updating profile',
                variant: 'destructive',
            });
        },
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto mt-10 max-w-4xl px-4 pb-20 md:px-6">
            <div class="mb-8">
                <HeadingSmall title="Profile Information" description="Update your personal information" />
            </div>

            <div class="rounded-lg border bg-card p-6 shadow-sm transition-all hover:shadow-md">
                <form @submit.prevent="onSubmit" enctype="multipart/form-data" class="space-y-6">
                    <FormField name="image" v-slot="{ componentField }">
                        <FormItem>
                            <FormLabel>Profile Image</FormLabel>
                            <FormControl>
                                <DragDropImageUploader v-bind="componentField" :multiple="false" v-model="form.image" @image-uploaded="getFile" />
                            </FormControl>
                        </FormItem>
                    </FormField>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <FormField name="name" v-slot="{ componentField }">
                            <FormItem>
                                <FormLabel required>Name</FormLabel>
                                <FormControl>
                                    <Input type="text" v-model="form.name" v-bind="componentField" placeholder="Enter your name" />
                                </FormControl>
                            </FormItem>
                        </FormField>

                        <FormField name="email" v-slot="{ componentField }">
                            <FormItem>
                                <FormLabel required>Email</FormLabel>
                                <FormControl>
                                    <Input type="text" v-model="form.email" v-bind="componentField" placeholder="Enter your email" />
                                </FormControl>
                            </FormItem>
                        </FormField>
                    </div>

                    <FormField v-slot="{ componentField, errorMessage }" name="description">
                        <FormItem class="w-full">
                            <FormLabel required>Description</FormLabel>
                            <FormControl>
                                <div :class="['rounded-md', errorMessage ? 'border-destructive' : 'border-input']">
                                    <QuillEditor
                                        v-model:content="content"
                                        content-type="html"
                                        theme="snow"
                                        v-bind="componentField"
                                        v-on:update:content="form.description = $event"
                                        class="min-h-[200px]"
                                    />
                                </div>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="mt-8 flex justify-end">
                        <Button type="submit" size="lg">Save Profile</Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
