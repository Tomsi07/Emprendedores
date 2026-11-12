<script setup>
import { computed, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const POR_PAGINA = 15

const props = defineProps({
  grupos: {
    type: Array,
    default: () => []
  }
})

const busqueda = ref('')
const buscando = computed(() => busqueda.value.trim() !== '')

// Año actual (2026 hoy, 2027 el año que viene, etc.)
const anioActual = new Date().getFullYear()

// Años del más reciente al más antiguo. El año actual siempre está en la lista,
// aunque todavía no tenga eventos.
const aniosOrdenados = computed(() => {
  const lista = [...props.grupos]
  if (!lista.some((g) => Number(g.anio) === anioActual)) {
    lista.push({ anio: anioActual, eventos: [] })
  }
  return lista.sort((a, b) => Number(b.anio) - Number(a.anio))
})

// Arranca siempre en el año actual
const anioElegido = ref(String(anioActual))

// año -> página actual de ese año
const paginas = ref({})
watch([busqueda, anioElegido], () => {
  paginas.value = {}
})

const formatFecha = (f) => {
  if (!f) return '-'
  const [y, m, d] = f.substring(0, 10).split('-')
  return `${d}/${m}/${y}`
}

const ventana = (actual, total) => {
  const inicio = Math.max(1, Math.min(actual - 2, total - 4))
  const fin = Math.min(total, inicio + 4)
  const lista = []
  for (let i = inicio; i <= fin; i++) lista.push(i)
  return lista
}

// Sin búsqueda: solo el año elegido. Con búsqueda: todos los años que tengan coincidencias.
const gruposFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  if (!q) {
    return aniosOrdenados.value.filter((g) => String(g.anio) === anioElegido.value)
  }

  return aniosOrdenados.value
    .map((grupo) => ({
      ...grupo,
      eventos: grupo.eventos.filter(
        (ev) =>
          String(grupo.anio).includes(q) ||
          (ev.nombreEvento || '').toLowerCase().includes(q) ||
          (ev.lugar || '').toLowerCase().includes(q)
      )
    }))
    .filter((grupo) => grupo.eventos.length > 0)
})

// Cada año con su propia paginación
const gruposVisibles = computed(() =>
  gruposFiltrados.value.map((grupo) => {
    const total = grupo.eventos.length
    const totalPaginas = Math.max(1, Math.ceil(total / POR_PAGINA))
    const pagina = Math.min(paginas.value[grupo.anio] || 1, totalPaginas)
    const inicio = (pagina - 1) * POR_PAGINA
    return {
      ...grupo,
      total,
      totalPaginas,
      pagina,
      desde: inicio + 1,
      hasta: Math.min(inicio + POR_PAGINA, total),
      visibles: grupo.eventos.slice(inicio, inicio + POR_PAGINA),
      ventana: ventana(pagina, totalPaginas)
    }
  })
)

const irA = (grupo, n) => {
  paginas.value = {
    ...paginas.value,
    [grupo.anio]: Math.min(Math.max(1, n), grupo.totalPaginas)
  }
}

const botonPagina =
  'min-w-[2rem] rounded-md border px-2.5 py-1.5 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-[#2b9a82] disabled:cursor-not-allowed disabled:opacity-40'
</script>

<template>
  <div class="flex min-h-screen bg-white text-[#0e1729]">
    <SidebarNav />

    <main class="flex-1 ml-64 px-10 py-8 space-y-8">
      <header class="space-y-6">
        <div class="flex h-1 w-24" aria-hidden="true">
          <span class="flex-1 bg-[#f2c230]"></span>
          <span class="flex-1 bg-[#2b9a82]"></span>
          <span class="flex-1 bg-[#1d4b44]"></span>
        </div>
        <div class="border-l-4 border-[#1d4b44] pl-4">
          <h1 class="text-3xl font-medium text-[#0e1729]">ASIGNACIONES</h1>
          <p class="text-sm text-slate-500 mt-1">
            Elegí un año y después un evento para marcar qué emprendimientos fueron convocados y
            cuáles asistieron.
          </p>
        </div>
      </header>

      <!-- Buscador y selector de año -->
      <div class="flex flex-wrap items-end gap-4">
        <div class="w-full max-w-md">
          <label for="buscar-evento" class="sr-only">Buscar evento</label>
          <input
            id="buscar-evento"
            v-model="busqueda"
            type="search"
            placeholder="Buscar por evento, lugar o año en todos los años"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-[#2b9a82] focus:outline-none focus:ring-2 focus:ring-[#2b9a82]/30"
          />
        </div>

        <div v-if="!buscando && aniosOrdenados.length">
          <label for="anio" class="sr-only">Año</label>
          <select
            id="anio"
            v-model="anioElegido"
            class="rounded-md border border-slate-300 bg-white py-2 pl-3 pr-9 text-sm font-medium focus:border-[#2b9a82] focus:outline-none focus:ring-2 focus:ring-[#2b9a82]/30"
          >
            <option v-for="g in aniosOrdenados" :key="g.anio" :value="String(g.anio)">
              {{ g.anio }} ({{ g.eventos.length }})
            </option>
          </select>
        </div>
        <p v-else-if="buscando" class="pb-2 text-xs text-slate-500">Buscando en todos los años.</p>
      </div>

      <p v-if="gruposVisibles.length === 0" class="py-10 text-slate-400">
        No se encontraron eventos para "{{ busqueda }}".
      </p>

      <section v-for="grupo in gruposVisibles" :key="grupo.anio" class="space-y-2">
        <h2 class="text-lg font-medium text-[#1d4b44] border-b border-slate-300 pb-2">
          {{ grupo.anio }}
        </h2>

        <p v-if="grupo.total === 0" class="py-6 text-slate-400">
          No hay eventos cargados en {{ grupo.anio }}.
        </p>

        <table v-else class="w-full text-left text-sm">
          <thead>
            <tr class="text-slate-500 text-xs">
              <th class="py-2 pr-6 font-medium">Evento</th>
              <th class="py-2 pr-6 font-medium">Fecha</th>
              <th class="py-2 pr-6 font-medium">Lugar</th>
              <th class="py-2 text-right font-medium"><span class="sr-only">Acción</span></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr
              v-for="ev in grupo.visibles"
              :key="ev.idEvento"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="py-3 pr-6 font-semibold text-[#0e1729]">{{ ev.nombreEvento }}</td>
              <td class="py-3 pr-6 text-slate-600">{{ formatFecha(ev.fecha) }}</td>
              <td class="py-3 pr-6 text-slate-600">{{ ev.lugar || '-' }}</td>
              <td class="py-3 text-right">
                <Link
                  :href="route('asignaciones.show', ev.idEvento)"
                  class="inline-block rounded-md border border-[#1d4b44] px-3 py-1.5 text-xs font-medium text-[#1d4b44] hover:bg-[#1d4b44] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#2b9a82] transition-colors"
                >
                  Asignar emprendimientos
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación del año -->
        <nav
          v-if="grupo.totalPaginas > 1"
          class="flex flex-wrap items-center justify-between gap-3 pt-3"
          :aria-label="`Paginación de ${grupo.anio}`"
        >
          <p class="text-xs text-slate-500">
            Mostrando {{ grupo.desde }}-{{ grupo.hasta }} de {{ grupo.total }}
          </p>
          <div class="flex items-center gap-1">
            <button
              type="button"
              :class="[botonPagina, 'border-slate-300 text-slate-600 hover:bg-slate-50']"
              :disabled="grupo.pagina === 1"
              @click="irA(grupo, grupo.pagina - 1)"
            >
              Anterior
            </button>
            <button
              v-for="n in grupo.ventana"
              :key="n"
              type="button"
              :class="[
                botonPagina,
                n === grupo.pagina
                  ? 'border-[#1d4b44] bg-[#1d4b44] text-white'
                  : 'border-slate-300 text-slate-600 hover:bg-slate-50'
              ]"
              :aria-current="n === grupo.pagina ? 'page' : undefined"
              @click="irA(grupo, n)"
            >
              {{ n }}
            </button>
            <button
              type="button"
              :class="[botonPagina, 'border-slate-300 text-slate-600 hover:bg-slate-50']"
              :disabled="grupo.pagina === grupo.totalPaginas"
              @click="irA(grupo, grupo.pagina + 1)"
            >
              Siguiente
            </button>
          </div>
        </nav>
      </section>
    </main>
  </div>
</template>