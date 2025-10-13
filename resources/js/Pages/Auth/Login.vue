<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: false, // It's good practice to provide defaults
    },
    status: {
        type: String,
        default: null, // Default to null for clarity
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    
        <Head title="Log in" />

        <div class="min-h-screen flex">
            <div class="hidden lg:flex w-1/2 bg-blue-600 items-center justify-center">
                <img
                    src="/images/pharmcity-login.png"
                    alt="Pharmacist organizing medication at Pharmcity."
                    class="object-contain h-full w-full rounded-xl shadow-2xl"
                />
            </div>

            <div class="flex flex-1 flex-col justify-center px-8 py-12 sm:px-6 lg:px-20 bg-white">
                <div class="mx-auto w-full max-w-md">
                    <header class="text-center">
                        <h2 class="text-3xl font-extrabold text-blue-700">Welcome to Pharmcity</h2>
                        <p class="mt-4 text-sm text-gray-500">Sign in to access your dashboard</p>
                    </header>

                    <div
                        v-if="status"
                        class="mt-8 mb-4 text-sm font-medium text-green-600"
                        role="status"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="mt-8 space-y-6">
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600">Remember me</span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-blue-600 hover:text-blue-800 underline rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Forgot your password?
                            </Link>
                        </div>

                        <PrimaryButton
                            class="w-full"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Log in
                        </PrimaryButton>
                    </form>

                    <p class="mt-6 text-center text-sm text-gray-500">
                        Don't have an account?
                        <Link
                            :href="route('register')"
                            class="font-medium text-blue-600 hover:text-blue-800 underline"
                        >
                            Sign up
                        </Link>
                    </p>
                </div>
            </div>
        </div>
    
</template>