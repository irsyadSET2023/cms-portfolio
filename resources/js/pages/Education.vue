<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormLabel, FormMessage } from '@/components/ui/form';
import FormItem from '@/components/ui/form/FormItem.vue';
import { Input } from '@/components/ui/input';
import { toast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { QuillEditor } from '@vueup/vue-quill';
import { useForm as useVeeForm } from 'vee-validate';
import { z } from 'zod';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Education',
        href: '/education',
    },
];

const formSchema = toTypedSchema(
    z.object({
        university_name: z.string(),
        degree_type: z.string(),
        field_of_study: z.string(),
        start_date: z.string(),
        end_date: z.string().optional(),
        gpa: z.number().optional(),
        description: z.string().optional(),
        is_current: z.boolean(),
        location: z.string(),
        honors: z.string().optional(),
        images: z.array(z.instanceof(File)),
    }),
);

const { handleSubmit, setFieldError, resetField, defineField } = useVeeForm({
    validationSchema: formSchema,
    initialValues: {
        university_name: '',
        degree_type: '',
        field_of_study: '',
        start_date: '',
        end_date: '',
        gpa: undefined,
        description: '',
        is_current: false,
        location: '',
        honors: '',
    },
});

const [description] = defineField('description');

const form = useForm({
    educations: [
        {
            university_name: '',
            degree_type: '',
            field_of_study: '',
            start_date: '',
            end_date: '',
            gpa: null,
            description: '',
            is_current: false,
            location: '',
            honors: '',
            images: [] as File[],
        },
    ],
});

const addEducation = () => {
    form.educations.push({
        university_name: '',
        degree_type: '',
        field_of_study: '',
        start_date: '',
        end_date: '',
        gpa: null,
        description: '',
        is_current: false,
        location: '',
        honors: '',
        images: [] as File[],
    });
};

const removeEducation = (index: number) => {
    if (form.educations.length > 1) {
        form.educations.splice(index, 1);
    }
};

const onSubmit = handleSubmit(() => {
    form.post(route('education.store'), {
        onSuccess: () => {
            toast({
                title: 'Success',
                description: 'Education records created successfully',
            });
        },
        onError: () => {
            toast({
                title: 'Error',
                description: 'Error creating education records',
                variant: 'destructive',
            });
        },
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto mt-10 max-w-4xl px-4 pb-20 md:px-6">
            <div class="mb-8 flex items-center justify-between">
                <HeadingSmall title="Education Information" description="Add your education details" />
                <Button type="button" @click="addEducation" variant="outline">Add Another Education</Button>
            </div>

            <div class="space-y-6">
                <div
                    v-for="(education, index) in form.educations"
                    :key="index"
                    class="rounded-lg border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-medium">Education #{{ index + 1 }}</h3>
                        <Button v-if="form.educations.length > 1" type="button" @click="() => removeEducation(index)" variant="destructive" size="sm">
                            Remove
                        </Button>
                    </div>

                    <form @submit.prevent="onSubmit" enctype="multipart/form-data" class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <FormField :name="`educations.${index}.university_name`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>University Name</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="education.university_name"
                                            v-bind="componentField"
                                            placeholder="Enter university name"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField :name="`educations.${index}.degree_type`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Degree Type</FormLabel>
                                    <FormControl>
                                        <Input type="text" v-model="education.degree_type" v-bind="componentField" placeholder="Enter degree type" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <FormField :name="`educations.${index}.field_of_study`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Field of Study</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="education.field_of_study"
                                            v-bind="componentField"
                                            placeholder="Enter field of study"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField :name="`educations.${index}.location`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Location</FormLabel>
                                    <FormControl>
                                        <Input type="text" v-model="education.location" v-bind="componentField" placeholder="City, Country" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <FormField :name="`educations.${index}.start_date`" v-slot="{ componentField }">
                                    <FormItem>
                                        <FormLabel required>Start Date</FormLabel>
                                        <FormControl>
                                            <Input type="date" v-model="education.start_date" v-bind="componentField" />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>

                                <FormField :name="`educations.${index}.end_date`" v-slot="{ componentField }">
                                    <FormItem>
                                        <FormLabel>End Date</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="date"
                                                v-model="education.end_date"
                                                v-bind="componentField"
                                                :disabled="education.is_current"
                                            />
                                        </FormControl>
                                        <FormMessage />
                                    </FormItem>
                                </FormField>
                            </div>

                            <FormField :name="`educations.${index}.is_current`" v-slot="{ componentField }">
                                <FormItem class="flex items-center space-x-2">
                                    <FormControl>
                                        <input
                                            type="checkbox"
                                            v-model="education.is_current"
                                            v-bind="componentField"
                                            class="h-4 w-4 rounded border-gray-300"
                                        />
                                    </FormControl>
                                    <FormLabel class="!mt-0">Currently studying</FormLabel>
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <FormField :name="`educations.${index}.gpa`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel>GPA</FormLabel>
                                    <FormControl>
                                        <Input type="number" step="0.01" v-model="education.gpa" v-bind="componentField" placeholder="Enter GPA" />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField :name="`educations.${index}.honors`" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel>Honors</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="education.honors"
                                            v-bind="componentField"
                                            placeholder="Enter honors/achievements"
                                        />
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>

                        <FormField :name="`educations.${index}.description`" v-slot="{ componentField, errorMessage }">
                            <FormItem class="w-full">
                                <FormLabel>Description</FormLabel>
                                <FormControl>
                                    <div :class="['rounded-md', errorMessage ? 'border-destructive' : 'border-input']">
                                        <QuillEditor
                                            v-model:content="education.description"
                                            content-type="html"
                                            theme="snow"
                                            v-bind="componentField"
                                            v-on:update:content="education.description = $event"
                                            class="min-h-[200px]"
                                        />
                                    </div>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </form>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <Button type="submit" size="lg" @click="onSubmit">Save All Education</Button>
            </div>
        </div>
    </AppLayout>
</template>
