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
    },
    status: {
        type: String,
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
    <GuestLayout>
        <Head title="Iniciar sesión" />
    
        <div class="flex min-h-screen bg-white">
            <div class="hidden lg:flex lg:w-2/5 flex-col justify-between bg-[#0f172a] p-12">
                <div class="flex gap-1.5">
                    <div class="h-2 w-8 bg-[#f4c430]"></div>
                    <div class="h-2 w-8 bg-[#2e9678]"></div>
                    <div class="h-2 w-8 bg-[#1b4b43]"></div>
    
                    <div class="h-2 w-8 bg-[#F4DE0A]"></div>
                    <div class="h-2 w-8 bg-[#4EA238]"></div>
                    <div class="h-2 w-8 bg-[#38ADBB]"></div>
                </div>
    
                <div>
                    <img
                        src="/images/saladilloImg3.jpeg"
                        alt="Saladillo en Desarrollo"
                        class="w-full max-w-xs"
                    />
                </div>
    
                <div class="text-white">
                    <h2 class="mt-1 text-4xl font-semibold">EMPRENDIMIENTOS</h2>
                    <h2 class="mt-1 text-xl font-semibold text-white/60">Secretaría de Desarrollo Local</h2>
                    <p class="mt-1 text-sm text-white/60">MUNICIPALIDAD DE SALADILLO</p>
                </div>
            </div>
    
            <div class="flex w-full lg:w-3/5 items-center justify-center px-6 py-12">
                <div class="w-full max-w-sm border-l-4 border-[#1b4b43] pl-8">
                    <h1 class="text-3xl font-semibold text-gray-900">Iniciar sesión</h1>
    
                    <div v-if="status" class="mt-4 text-sm font-medium text-green-600">
                        {{ status }}
                    </div>
    
                    <form @submit.prevent="submit" class="mt-8">
                        <div>
                            <label for="email" class="block text-sm text-gray-600">Email</label>
                            <input
                                id="email"
                                type="email"
                                class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
    
                        <div class="mt-6">
                            <label for="password" class="block text-sm text-gray-600">Contraseña</label>
                            <input
                                id="password"
                                type="password"
                                class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
    
                        <div class="mt-6 flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox name="remember" v-model:checked="form.remember" />
                                <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                            </label>
    
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-gray-500 hover:text-gray-800"
                            >
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>
    
                        <button
                            type="submit"
                            class="mt-8 w-full bg-[#1b4b43] px-4 py-3 text-sm font-medium text-white hover:bg-[#153b35] focus:outline-none focus:ring-2 focus:ring-[#1b4b43] focus:ring-offset-2"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Ingresar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
