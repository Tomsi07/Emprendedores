<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = computed(() => page.props.auth.user)
</script>

<template>
  <aside class="w-64 bg-slate-900 text-slate-100 min-h-screen p-4 flex flex-col justify-between fixed left-0 top-0 bottom-0 z-40">
    <div>
      <!-- Encabezado / Logo -->
      <div class="flex items-center space-x-2 px-2 py-3 mb-6 border-b border-slate-800">
        <span class="font-bold text-xl text-indigo-400">Panel Gestión</span>
      </div>

      <!-- Menú de Navegación -->
      <nav class="space-y-1">
        <Link
          :href="route('emprendimientos.index')"
          :class="[route().current('emprendimientos.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800']"
          class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition"
        >
          <span>Emprendimientos</span>
        </Link>

        <Link
          :href="route('actividades.index')"
          :class="[route().current('actividades.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800']"
          class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition"
        >
          <span>Actividades</span>
        </Link>

        <Link
          :href="route('necesidades.index')"
          :class="[route().current('necesidades.*') ? 'bg-indigo-600 text-white' : 'text-slate-300 hover:bg-slate-800']"
          class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition"
        >
          <span>Necesidades</span>
        </Link>
      </nav>
    </div>

    <!-- Sección Usuario y Cerrar Sesión -->
    <div class="border-t border-slate-800 pt-4 space-y-3">
      <!-- Información del Usuario Logueado -->
      <div v-if="user" class="px-3 py-1">
        <p class="text-xs font-semibold text-slate-200 truncate">{{ user.name }}</p>
        <p class="text-xs text-slate-400 truncate">{{ user.email }}</p>
      </div>

      <!-- Botón Cerrar Sesión -->
      <Link
        :href="route('logout')"
        method="post"
        as="button"
        class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-rose-400 hover:bg-rose-950/30 rounded-lg transition border border-rose-900/30"
      >
        <span>Cerrar Sesión</span>
      </Link>
    </div>
  </aside>
</template>