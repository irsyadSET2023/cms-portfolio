<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import DragDropImageUploader from '@/components/reusable/DragDropImageUploader.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormLabel, FormMessage } from '@/components/ui/form';
import FormItem from '@/components/ui/form/FormItem.vue';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { Profile, type BreadcrumbItem } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { QuillEditor } from '@vueup/vue-quill';
import { useForm as useVeeForm } from 'vee-validate';
import { z } from 'zod';

interface Props {
    profile: Profile;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile',
        href: '/profile',
    },
];

const formSchema = toTypedSchema(
    z.object({
        name: z.string().min(1, 'Name is required'),
        email: z.string().min(1, 'Email is required').email('Invalid email format'),
        image: z.any(),
        description: z.string().optional(),
        dob: z.string().min(1, 'Date of birth is required'),
    }),
);

const profile = ref<Profile>(props.profile as Profile);

const { handleSubmit, setFieldError, resetField, defineField, setFieldValue } = useVeeForm({
    validationSchema: formSchema,
    initialValues: {
        name: profile?.value?.fullname || '',
        email: profile?.value?.email || '',
        image: null,
        dob: profile?.value?.dob ? new Date(profile?.value?.dob).toISOString().split('T')[0] : '',
        description: profile?.value?.description || '',
    },
});

const [content] = defineField('description');

const form = useForm<{ name: string; email: string; image: File | File[] | null; description: string; dob: string }>({
    name: profile?.value?.fullname,
    email: profile?.value?.email,
    image: null,
    description: profile?.value?.description || '',
    dob: profile?.value?.dob ? new Date(profile?.value?.dob).toISOString().split('T')[0] : new Date().toISOString().split('T')[0],
});

const getFile = (file: File | File[]) => {
    resetField('image');
    if (Array.isArray(file) && file.length > 0) {
        form.image = file[0]; // Take the first file from the array
        setFieldValue('image', file[0]);
    } else if (file instanceof File) {
        form.image = file;
        setFieldValue('image', file);
    } else {
        form.image = null;
        setFieldValue('image', null);
    }
};

const onSubmit = handleSubmit((values) => {
    // Ensure form data is properly set
    form.name = values.name;
    form.email = values.email;
    form.description = content.value || '';
    form.dob = values.dob;

    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: (response) => {
            if (response?.props?.success) {
                toast({
                    title: 'Success',
                    description: response.props.success as string,
                });
            }
        },
        onError: (errors) => {
            // Map backend errors to form fields
            Object.keys(errors).forEach((key) => {
                setFieldError(key, errors[key]);
            });

            toast({
                title: 'Error',
                description: 'Please check the form for errors',
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
                                <DragDropImageUploader
                                    v-bind="componentField"
                                    :multiple="false"
                                    v-model="form.image"
                                    :existing-image-url="props?.profile?.image_url"
                                    @image-uploaded="getFile"
                                />
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

                    <FormField name="dob" v-slot="{ componentField }">
                        <FormItem>
                            <FormLabel required>Date of Birth</FormLabel>
                            <FormControl>
                                <Input
                                    type="date"
                                    v-model="form.dob"
                                    v-bind="componentField"
                                    placeholder="Enter your date of birth"
                                    class="[&::-webkit-calendar-picker-indicator]:dark:invert"
                                />
                            </FormControl>
                        </FormItem>
                    </FormField>

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
