<script setup>
import { Link } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

defineProps({
  grupos: {
    type: Array,
    default: () => []
  }
})

const formatFecha = (f) => {
  if (!f) return '-'
  const [y, m, d] = f.substring(0, 10).split('-')
  return `${d}/${m}/${y}`
}
</script>

<template>
  <div class="flex min-h-screen bg-white text-[#0e1729]">
    <SidebarNav />

    <main class="flex-1 ml-64 px-10 py-8 space-y-10">
      <header class="space-y-6">
        <div class="flex h-1 w-24" aria-hidden="true">
          <span class="flex-1 bg-[#f2c230]"></span>
          <span class="flex-1 bg-[#2b9a82]"></span>
          <span class="flex-1 bg-[#1d4b44]"></span>
        </div>
        <div class="border-l-4 border-[#1d4b44] pl-4">
          <h1 class="text-3xl font-medium text-[#0e1729]">Asignaciones</h1>
          <p class="text-sm text-slate-500 mt-1">
            Eventos por año. Entrá a cada uno para ver qué emprendimientos fueron convocados y cuáles asistieron.
          </p>
        </div>
      </header>

      <p v-if="grupos.length === 0" class="py-10 text-slate-400">
        Todavía no hay eventos registrados.
      </p>

      <section v-for="grupo in grupos" :key="grupo.anio" class="space-y-3">
        <h2 class="text-2xl font-medium text-[#1d4b44] border-b-2 border-[#1d4b44] pb-2">
          {{ grupo.anio }}
        </h2>

        <table class="w-full text-left text-sm">
          <thead>
            <tr class="text-slate-500 text-xs">
              <th class="py-3 pr-6 font-medium">Evento</th>
              <th class="py-3 pr-6 font-medium">Fecha</th>
              <th class="py-3 pr-6 font-medium">Lugar</th>
              <th class="py-3 text-right font-medium">Emprendimientos</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr
              v-for="ev in grupo.eventos"
              :key="ev.idEvento"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="py-4 pr-6 font-semibold text-[#0e1729]">{{ ev.nombreEvento }}</td>
              <td class="py-4 pr-6 text-slate-600">{{ formatFecha(ev.fecha) }}</td>
              <td class="py-4 pr-6 text-slate-600">{{ ev.lugar || '-' }}</td>
              <td class="py-4 text-right">
                <Link
                  :href="route('asignaciones.show', ev.idEvento)"
                  class="text-xs font-medium text-[#1d4b44] hover:text-[#163b36] hover:underline underline-offset-4 transition-colors"
                >
                  Ver emprendimientos
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>