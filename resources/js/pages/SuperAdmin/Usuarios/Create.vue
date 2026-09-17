<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'user',
});

function submit() {
    form.post(route('superadmin.usuarios.store'));
}
</script>

<template>
    <Head title="Nuevo usuario" />

    <div class="min-h-screen bg-white p-10">
        <div class="border-b border-gray-200 pb-6">
            <p class="text-sm uppercase tracking-wide text-gray-400">Superadmin</p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-900">Nuevo usuario</h1>
        </div>

        <form @submit.prevent="submit" class="mt-8 max-w-md border-l-4 border-[#1b4b43] pl-8">
            <div>
                <label class="block text-sm text-gray-600">Nombre</label>
                <input
                    type="text"
                    v-model="form.name"
                    class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                    required
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-6">
                <label class="block text-sm text-gray-600">Email</label>
                <input
                    type="email"
                    v-model="form.email"
                    class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                    required
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-6">
                <label class="block text-sm text-gray-600">Contraseña</label>
                <input
                    type="password"
                    v-model="form.password"
                    class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                    required
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6">
                <label class="block text-sm text-gray-600">Rol</label>
                <select
                    v-model="form.role"
                    class="mt-1 block w-full border-0 border-b border-gray-300 px-0 py-2 focus:border-[#1b4b43] focus:ring-0"
                >
                    <option value="user">user</option>
                    <option value="admin">admin</option>
                    <option value="superadmin">superadmin</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div class="mt-8 flex items-center gap-4">
                <button
                    type="submit"
                    class="bg-[#1b4b43] px-4 py-2 text-sm font-medium text-white hover:bg-[#153b35]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Crear usuario
                </button>
                <Link :href="route('superadmin.usuarios.index')" class="text-sm text-gray-500 hover:text-gray-800">
                    Cancelar
                </Link>
            </div>
        </form>
    </div>
</template>