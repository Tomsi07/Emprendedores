<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Panel Superadmin" />

    <div class="min-h-screen bg-white p-10">
        <p class="text-sm uppercase tracking-wide text-gray-400">Superadmin</p>
        <h1 class="mt-1 text-2xl font-semibold text-gray-900">Panel de Superadmin</h1>
        <p class="mt-2 text-gray-500">Acá vas a gestionar todo lo que necesite control total.</p>

        <div class="mt-8 flex gap-4">
            <Link
                :href="route('superadmin.usuarios.index')"
                class="bg-[#1b4b43] px-4 py-2 text-sm font-medium text-white hover:bg-[#153b35]"
            >
                Gestionar usuarios
            </Link>
        </div>
    </div>
</template>