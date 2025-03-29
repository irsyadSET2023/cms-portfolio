<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormLabel, FormMessage } from '@/components/ui/form';
import FormItem from '@/components/ui/form/FormItem.vue';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { QuillEditor } from '@vueup/vue-quill';
import { Plus, Trash } from 'lucide-vue-next';
import { useForm as useVeeForm } from 'vee-validate';
import { z } from 'zod';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Experience',
        href: '/experience',
    },
];

const formSchema = toTypedSchema(
    z.object({
        company_name: z.string(),
        designation: z.string(),
        start_date: z.string(),
        end_date: z.string().optional(),
        employment_type: z.string(),
        description: z.string(),
        location: z.string(),
        is_current: z.boolean(),
        industry: z.string(),
        image: z.any(),
    }),
);

const page = usePage<SharedData>();
const user = ref<User>(page.props.auth.user as User); // Make user reactive

const { handleSubmit, setFieldError, resetField } = useVeeForm({
    validationSchema: formSchema,
});

// Add new ref for experiences array
const experiences = ref([
    {
        company_name: '',
        designation: '',
        start_date: '',
        end_date: '',
        employment_type: '',
        description: '',
        location: '',
        is_current: false,
        industry: '',
        image: null,
    },
]);

const addExperience = () => {
    experiences.value.push({
        company_name: '',
        designation: '',
        start_date: '',
        end_date: '',
        employment_type: '',
        description: '',
        location: '',
        is_current: false,
        industry: '',
        image: null,
    });
};

const removeExperience = (index: number) => {
    experiences.value.splice(index, 1);
};

// Modify onSubmit to just console.log for now
const onSubmit = handleSubmit(() => {
    console.log('Experiences to submit:', experiences.value);
    toast({
        title: 'Success',
        description: 'Form validated successfully (frontend only)',
    });
});

// Add validation helper
const validateExperience = (experience: (typeof experiences.value)[0]) => {
    // Add frontend validation logic here
    const errors: string[] = [];

    if (!experience.company_name) errors.push('Company name is required');
    if (!experience.designation) errors.push('Designation is required');
    if (!experience.start_date) errors.push('Start date is required');
    if (!experience.employment_type) errors.push('Employment type is required');
    if (!experience.location) errors.push('Location is required');
    if (!experience.industry) errors.push('Industry is required');
    if (!experience.description) errors.push('Description is required');

    return errors;
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto mt-10 max-w-4xl px-4 pb-20 md:px-6">
            <div class="mb-8 flex items-center justify-between">
                <HeadingSmall title="Work Experience" description="Add your professional experience details" />
                <Button type="button" @click="addExperience" variant="outline" class="gap-2">
                    <Plus class="h-4 w-4" />
                    Add Experience
                </Button>
            </div>

            <div class="space-y-8">
                <div
                    v-for="(experience, index) in experiences"
                    :key="index"
                    class="rounded-lg border bg-card p-6 shadow-sm transition-all hover:shadow-md"
                >
                    <div class="mb-6 flex items-center justify-between border-b pb-4">
                        <HeadingSmall :title="`Experience ${index + 1}`" class="text-lg" />
                        <Button
                            v-if="experiences.length > 1"
                            type="button"
                            @click="() => removeExperience(index)"
                            variant="ghost"
                            size="sm"
                            class="text-destructive hover:bg-destructive/10"
                        >
                            <Trash class="mr-2 h-4 w-4" />
                            Remove
                        </Button>
                    </div>

                    <form @submit.prevent="onSubmit" class="space-y-6">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <FormField name="company_name" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Company Name</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="experiences[index].company_name"
                                            v-bind="componentField"
                                            placeholder="Enter company name"
                                        />
                                    </FormControl>
                                </FormItem>
                            </FormField>

                            <FormField name="designation" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Designation</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="experiences[index].designation"
                                            v-bind="componentField"
                                            placeholder="Enter your job title"
                                        />
                                    </FormControl>
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <FormField name="employment_type" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Employment Type</FormLabel>
                                    <Select v-model="experiences[index].employment_type" v-bind="componentField">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="full_time">Full-time</SelectItem>
                                                <SelectItem value="part_time">Part-time</SelectItem>
                                                <SelectItem value="contract">Contract</SelectItem>
                                                <SelectItem value="internship">Internship</SelectItem>
                                                <SelectItem value="freelance">Freelance</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </FormItem>
                            </FormField>

                            <FormField name="location" v-slot="{ componentField }">
                                <FormItem>
                                    <FormLabel required>Location</FormLabel>
                                    <FormControl>
                                        <Input
                                            type="text"
                                            v-model="experiences[index].location"
                                            v-bind="componentField"
                                            placeholder="City, Country"
                                        />
                                    </FormControl>
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-6 sm:grid-cols-2">
                                <FormField name="start_date" v-slot="{ componentField }">
                                    <FormItem>
                                        <FormLabel required>Start Date</FormLabel>
                                        <FormControl>
                                            <Input type="date" v-model="experiences[index].start_date" v-bind="componentField" />
                                        </FormControl>
                                    </FormItem>
                                </FormField>

                                <FormField name="end_date" v-slot="{ componentField }">
                                    <FormItem>
                                        <FormLabel>End Date</FormLabel>
                                        <FormControl>
                                            <Input
                                                type="date"
                                                v-model="experiences[index].end_date"
                                                v-bind="componentField"
                                                :disabled="experiences[index].is_current"
                                            />
                                        </FormControl>
                                    </FormItem>
                                </FormField>
                            </div>

                            <FormField name="is_current" v-slot="{ componentField }">
                                <FormItem class="flex items-center space-x-2">
                                    <FormControl>
                                        <input
                                            type="checkbox"
                                            v-model="experiences[index].is_current"
                                            v-bind="componentField"
                                            class="h-4 w-4 rounded border-gray-300"
                                        />
                                    </FormControl>
                                    <FormLabel class="!mt-0">I currently work here</FormLabel>
                                </FormItem>
                            </FormField>
                        </div>

                        <FormField v-slot="{ componentField, errorMessage }" name="description">
                            <FormItem class="w-full">
                                <FormLabel required>Description</FormLabel>
                                <FormControl>
                                    <div :class="['rounded-md', errorMessage ? 'border-destructive' : 'border-input']">
                                        <QuillEditor
                                            v-model:content="experiences[index].description"
                                            content-type="html"
                                            theme="snow"
                                            v-bind="componentField"
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
                <Button type="submit" @click="onSubmit" size="lg"> Save All Experiences </Button>
            </div>
        </div>
    </AppLayout>
</template>
