<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import DragDropImageUploader from '@/components/reusable/DragDropImageUploader.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormLabel } from '@/components/ui/form';
import FormItem from '@/components/ui/form/FormItem.vue';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm as useVeeForm } from 'vee-validate';
import { z } from 'zod';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const formSchema = toTypedSchema(
    z.object({
        name: z.string(),
        email: z.string(),
        image: z.any(),
    }),
);

const page = usePage<SharedData>();
const user = ref<User>(page.props.auth.user as User); // Make user reactive

const { handleSubmit, setFieldError, resetField } = useVeeForm({
    validationSchema: formSchema,
    initialValues: {
        name: user.value.name,
        email: user.value.email,
        image: null,
    },
});

const form = useForm<{ name: string; email: string; image: File | File[] | null }>({
    name: user.value.name,
    email: user.value.email,
    image: null,
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
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />
                <form @submit.prevent="onSubmit" enctype="multipart/form-data" class="space-y-5">
                    <FormField name="image" v-slot="{ componentField }">
                        <FormItem>
                            <FormLabel>Profile Image</FormLabel>
                            <FormControl>
                                <DragDropImageUploader v-bind="componentField" :multiple="false" v-model="form.image" @image-uploaded="getFile" />
                            </FormControl>
                        </FormItem>
                    </FormField>

                    <FormField name="name" v-slot="{ componentField }">
                        <FormItem>
                            <FormLabel required>Name</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.name" v-bind="componentField" />
                            </FormControl>
                        </FormItem>
                    </FormField>
                    <FormField name="email" v-slot="{ componentField }">
                        <FormItem>
                            <FormLabel required>Email</FormLabel>
                            <FormControl>
                                <Input type="text" v-model="form.email" v-bind="componentField" />
                            </FormControl>
                        </FormItem>
                    </FormField>
                    <div class="mt-5">
                        <Button type="submit"> Submit </Button>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
