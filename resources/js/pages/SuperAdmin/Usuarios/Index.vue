<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    usuarios: Array,
});

function eliminar(id, nombre) {
    if (confirm(`¿Eliminar al usuario "${nombre}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('superadmin.usuarios.destroy', id));
    }
}
</script>

<template>
    <Head title="Usuarios" />

    <div class="min-h-screen bg-white p-10">
        <div class="flex items-center justify-between border-b border-gray-200 pb-6">
            <div>
                <p class="text-sm uppercase tracking-wide text-gray-400">ADMINISTRADOR</p>
                <h1 class="mt-1 text-2xl font-semibold text-gray-900">Usuarios</h1>
            </div>
            <Link
                :href="route('superadmin.usuarios.create')"
                class="bg-[#1b4b43] px-4 py-2 text-sm font-medium text-white hover:bg-[#153b35]"
            >
                + Nuevo usuario
            </Link>
        </div>

        <table class="mt-8 w-full text-left text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-gray-400">
                    <th class="py-2 font-medium">Nombre</th>
                    <th class="py-2 font-medium">Email</th>
                    <th class="py-2 font-medium">Rol</th>
                    <th class="py-2 font-medium text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="usuario in usuarios" :key="usuario.id" class="border-b border-gray-100">
                    <td class="py-3 text-gray-800">{{ usuario.name }}</td>
                    <td class="py-3 text-gray-600">{{ usuario.email }}</td>
                    <td class="py-3">
                        <span
                            class="px-2 py-1 text-xs font-medium"
                            :class="{
                                'bg-[#1b4b43]/10 text-[#1b4b43]': usuario.role === 'superadmin',
                                'bg-blue-50 text-blue-700': usuario.role === 'admin',
                                'bg-gray-100 text-gray-600': usuario.role === 'user',
                            }"
                        >
                            {{ usuario.role }}
                        </span>
                    </td>
                    <td class="py-3 text-right">
                        <Link
                            :href="route('superadmin.usuarios.edit', usuario.id)"
                            class="text-sm text-gray-500 hover:text-gray-900"
                        >
                            Editar
                        </Link>
                        <button
                            @click="eliminar(usuario.id, usuario.name)"
                            class="ms-4 text-sm text-red-500 hover:text-red-700"
                        >
                            Eliminar
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>