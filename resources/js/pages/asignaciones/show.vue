<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  evento: { type: Object, required: true },
  emprendimientos: { type: Array, default: () => [] }
})

const formatFecha = (f) => {
  if (!f) return '-'
  const [y, m, d] = f.substring(0, 10).split('-')
  return `${d}/${m}/${y}`
}

// Un solo listado, con una fila de encabezado por rubro
const porRubro = computed(() => {
  const grupos = {}
  props.emprendimientos.forEach((e) => {
    const rubro = e.rubro || 'Sin rubro'
    ;(grupos[rubro] ||= []).push(e)
  })
  return Object.keys(grupos)
    .sort((a, b) => {
      if (a === 'Sin rubro') return 1
      if (b === 'Sin rubro') return -1
      return a.localeCompare(b)
    })
    .map((rubro) => ({ rubro, items: grupos[rubro] }))
})

const totalConvocados = computed(() => props.emprendimientos.filter((e) => e.convocado).length)
const totalAsistentes = computed(() => props.emprendimientos.filter((e) => e.asistio).length)

const actualizar = (e, campo, valor) => {
  router.patch(
    route('asignaciones.update', [props.evento.idEvento, e.idEmprendimiento]),
    {
      convocado: campo === 'convocado' ? valor : e.convocado,
      asistio: campo === 'asistio' ? valor : e.asistio
    },
    { preserveScroll: true, preserveState: true }
  )
}

const etiqueta = 'text-xs text-slate-500'
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

        <Link
          :href="route('asignaciones.index')"
          class="text-sm text-slate-500 hover:text-[#1d4b44] hover:underline underline-offset-4 transition-colors"
        >
          Volver a asignaciones
        </Link>

        <div class="border-l-4 border-[#1d4b44] pl-4">
          <h1 class="text-3xl font-medium text-[#0e1729]">{{ evento.nombreEvento }}</h1>
        </div>
      </header>

      <!-- Datos del evento -->
      <section class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-5">
        <div>
          <p :class="etiqueta">Fecha</p>
          <p class="text-sm">{{ formatFecha(evento.fecha) }}</p>
        </div>
        <div>
          <p :class="etiqueta">Lugar</p>
          <p class="text-sm">{{ evento.lugar || '-' }}</p>
        </div>
        <div>
          <p :class="etiqueta">Ciudad</p>
          <p class="text-sm">{{ evento.ciudad || '-' }}</p>
        </div>
        <div>
          <p :class="etiqueta">Tipo de evento</p>
          <p class="text-sm">{{ evento.tipoDeEvento || '-' }}</p>
        </div>
      </section>

      <!-- Resumen -->
      <p class="text-sm text-slate-600">
        {{ totalConvocados }} convocados y {{ totalAsistentes }} asistentes, de {{ emprendimientos.length }} emprendimientos.
      </p>

      <!-- Tabla de emprendimientos -->
      <section>
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b-2 border-[#1d4b44] text-slate-500 text-xs">
              <th class="py-3 pr-6 font-medium">Emprendimiento</th>
              <th class="py-3 px-6 font-medium text-center">Convocado</th>
              <th class="py-3 pl-6 font-medium text-center">Asistió</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="grupo in porRubro" :key="grupo.rubro">
              <tr class="bg-slate-50">
                <td colspan="3" class="py-2 px-3 text-sm font-medium text-[#1d4b44]">
                  {{ grupo.rubro }}
                </td>
              </tr>
              <tr
                v-for="e in grupo.items"
                :key="e.idEmprendimiento"
                class="border-b border-slate-200 hover:bg-slate-50 transition-colors"
              >
                <td class="py-3 pr-6 font-semibold">{{ e.nombreEmprendimiento }}</td>
                <td class="py-3 px-6 text-center">
                  <input
                    type="checkbox"
                    :checked="e.convocado"
                    @change="actualizar(e, 'convocado', $event.target.checked)"
                    class="h-4 w-4 rounded border-slate-300 text-[#1d4b44] focus:ring-[#2b9a82]"
                    :aria-label="`Convocado: ${e.nombreEmprendimiento}`"
                  />
                </td>
                <td class="py-3 pl-6 text-center">
                  <input
                    type="checkbox"
                    :checked="e.asistio"
                    @change="actualizar(e, 'asistio', $event.target.checked)"
                    class="h-4 w-4 rounded border-slate-300 text-[#1d4b44] focus:ring-[#2b9a82]"
                    :aria-label="`Asistió: ${e.nombreEmprendimiento}`"
                  />
                </td>
              </tr>
            </template>
            <tr v-if="emprendimientos.length === 0">
              <td colspan="3" class="py-10 text-center text-slate-400">
                Todavía no hay emprendimientos cargados.
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</template>