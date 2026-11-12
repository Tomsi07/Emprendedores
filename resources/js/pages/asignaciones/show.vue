<script setup>
import { computed, ref, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'
import GuardadoIndicador from '@/Components/GuardadoIndicador.vue'

const POR_PAGINA = 15
const TODOS = '__todos__'

const props = defineProps({
  evento: { type: Object, required: true },
  emprendimientos: { type: Array, default: () => [] }
})

const clonar = (lista) => lista.map((e) => ({ ...e }))
const lista = ref(clonar(props.emprendimientos))
watch(
  () => props.emprendimientos,
  (nuevo) => {
    lista.value = clonar(nuevo)
  }
)

const busqueda = ref('')
const pendientes = ref(0) // cambios que se están guardando
const rubroElegido = ref(TODOS)
const pagina = ref(1)
const error = ref('')
const guardadoOk = ref(false) // muestra el tilde un instante cuando termina de guardar
let temporizadorOk = null

const formatFecha = (f) => {
  if (!f) return '-'
  const [y, m, d] = f.substring(0, 10).split('-')
  return `${d}/${m}/${y}`
}

// El rubro puede venir como texto, como objeto (relación) o con otro nombre de campo
const rubroDe = (e) => {
  const r = e.rubro ?? e.nombreRubro ?? e.nombre_rubro ?? e.rubroEmprendimiento
  const nombre = typeof r === 'object' && r !== null ? (r.nombreRubro ?? r.nombre) : r
  return nombre ? String(nombre) : 'Sin rubro'
}

// Responsables y contacto pueden llegar como texto o como lista: se muestran una línea por dato
const lineas = (v) => {
  if (Array.isArray(v)) return v.filter(Boolean).map(String)
  return v ? [String(v)] : []
}

// Datos del evento: cada uno con su color e ícono para reconocerlos de un vistazo
const datosEvento = computed(() => [
  {
    clave: 'fecha',
    etiqueta: 'Fecha',
    valor: formatFecha(props.evento.fecha),
    chip: 'bg-[#f2c230]/25 text-[#7a5d00]',
    icono: [
      'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'
    ]
  },
  {
    clave: 'lugar',
    etiqueta: 'Lugar',
    valor: props.evento.lugar || '-',
    chip: 'bg-[#2b9a82]/15 text-[#1f7a66]',
    icono: [
      'M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
      'M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'
    ]
  },
  {
    clave: 'ciudad',
    etiqueta: 'Ciudad',
    valor: props.evento.ciudad || '-',
    chip: 'bg-[#1d4b44]/10 text-[#1d4b44]',
    icono: ['M4 21V5a1 1 0 011-1h8a1 1 0 011 1v16M14 9h5a1 1 0 011 1v11M3 21h18M8 8h2M8 12h2M8 16h2']
  },
  {
    clave: 'tipo',
    etiqueta: 'Tipo de evento',
    valor: props.evento.tipoDeEvento || '-',
    chip: 'bg-[#0e1729]/10 text-[#0e1729]',
    icono: [
      'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z',
      'M6 6h.008v.008H6V6z'
    ]
  }
])

// Lista completa filtrada por la búsqueda y ordenada por rubro y nombre
const filtrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()
  return lista.value
    .filter(
      (e) =>
        !q ||
        (e.nombreEmprendimiento || '').toLowerCase().includes(q) ||
        rubroDe(e).toLowerCase().includes(q) ||
        lineas(e.responsables).join(' ').toLowerCase().includes(q) ||
        lineas(e.contacto).join(' ').toLowerCase().includes(q)
    )
    .sort((a, b) => {
      const ra = rubroDe(a)
      const rb = rubroDe(b)
      if (ra !== rb) {
        if (ra === 'Sin rubro') return 1
        if (rb === 'Sin rubro') return -1
        return ra.localeCompare(rb)
      }
      return (a.nombreEmprendimiento || '').localeCompare(b.nombreEmprendimiento || '')
    })
})

const estadoDe = (items, campo) => {
  const marcados = items.filter((e) => e[campo]).length
  return {
    marcados,
    todos: items.length > 0 && marcados === items.length,
    parcial: marcados > 0 && marcados < items.length
  }
}

const armarPestania = (rubro, nombre, items) => ({
  rubro,
  nombre,
  items,
  convocado: estadoDe(items, 'convocado'),
  asistio: estadoDe(items, 'asistio')
})

// Pestañas: "Todos" primero y después un rubro por pestaña
const pestanias = computed(() => {
  const mapa = new Map()
  filtrados.value.forEach((e) => {
    const rubro = rubroDe(e)
    if (!mapa.has(rubro)) mapa.set(rubro, [])
    mapa.get(rubro).push(e)
  })
  return [
    armarPestania(TODOS, 'Todos', filtrados.value),
    ...[...mapa.entries()].map(([rubro, items]) => armarPestania(rubro, rubro, items))
  ]
})

// Si el rubro elegido ya no existe (por la búsqueda), vuelve a "Todos"
const actual = computed(
  () => pestanias.value.find((p) => p.rubro === rubroElegido.value) ?? pestanias.value[0]
)

watch([rubroElegido, busqueda], () => {
  pagina.value = 1
})

const totalPaginas = computed(() => Math.max(1, Math.ceil(actual.value.items.length / POR_PAGINA)))
const paginaActual = computed(() => Math.min(pagina.value, totalPaginas.value))
const desde = computed(() => (paginaActual.value - 1) * POR_PAGINA)
const visibles = computed(() => actual.value.items.slice(desde.value, desde.value + POR_PAGINA))
const hasta = computed(() => Math.min(desde.value + POR_PAGINA, actual.value.items.length))

const ventana = computed(() => {
  const inicio = Math.max(1, Math.min(paginaActual.value - 2, totalPaginas.value - 4))
  const fin = Math.min(totalPaginas.value, inicio + 4)
  const numeros = []
  for (let i = inicio; i <= fin; i++) numeros.push(i)
  return numeros
})

const irA = (n) => {
  pagina.value = Math.min(Math.max(1, n), totalPaginas.value)
}

// Resumen
const totalConvocados = computed(() => lista.value.filter((e) => e.convocado).length)
const totalAsistentes = computed(() => lista.value.filter((e) => e.asistio).length)
const puedeExportar = computed(() => totalAsistentes.value > 0 && pendientes.value === 0)

// Estado del indicador de guardado (circulito, tilde o error)
const estadoGuardado = computed(() => {
  if (pendientes.value > 0) return 'guardando'
  if (error.value) return 'error'
  return guardadoOk.value ? 'guardado' : 'idle'
})

// Token de seguridad (CSRF) que Laravel deja en una cookie
const tokenCsrf = () => {
  const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/)
  return m ? decodeURIComponent(m[1]) : ''
}

// Los cambios se envían de a uno, en orden
let cola = Promise.resolve()

const guardar = (url, cuerpo, alFallar) => {
  pendientes.value++
  guardadoOk.value = false
  clearTimeout(temporizadorOk)
  cola = cola
    .then(async () => {
      const r = await fetch(url, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-XSRF-TOKEN': tokenCsrf()
        },
        body: JSON.stringify(cuerpo)
      })
      if (!r.ok) throw new Error(`Error ${r.status}`)
      guardadoOk.value = true
      clearTimeout(temporizadorOk)
      temporizadorOk = setTimeout(() => {
        guardadoOk.value = false
      }, 1500)
    })
    .catch((err) => {
      console.error('Asignaciones:', err)
      alFallar()
      error.value = 'No se pudo guardar el último cambio y se deshizo. Probá de nuevo.'
    })
    .finally(() => {
      pendientes.value--
    })
}

// Un emprendimiento
const actualizar = (e, campo, valor) => {
  error.value = ''
  const anterior = e[campo]
  e[campo] = valor
  guardar(
    route('asignaciones.update', [props.evento.idEvento, e.idEmprendimiento]),
    { convocado: e.convocado, asistio: e.asistio },
    () => {
      e[campo] = anterior
    }
  )
}

// Muchos a la vez (todo un rubro, o todos)
const marcarGrupo = (pestania, campo, valor) => {
  const items = pestania.items
  if (items.length === 0) return
  error.value = ''
  const anteriores = items.map((e) => e[campo])
  items.forEach((e) => {
    e[campo] = valor
  })
  guardar(
    route('asignaciones.updateMasivo', props.evento.idEvento),
    { campo, valor, ids: items.map((e) => e.idEmprendimiento) },
    () => {
      items.forEach((e, i) => {
        e[campo] = anteriores[i]
      })
    }
  )
}

// Color de la fila según su estado: asistió (verde), convocado (amarillo) o sin marcar (blanco)
const claseFila = (e) =>
  e.asistio
    ? 'bg-[#e6f4ef] hover:bg-[#d9eee7]'
    : e.convocado
      ? 'bg-[#fff6d9] hover:bg-[#ffefbd]'
      : 'bg-white hover:bg-slate-50'

// Casilla chica; el área para tocar sigue siendo toda la celda
const casilla = 'h-4 w-4 rounded border-slate-400 text-[#1d4b44] focus:ring-[#2b9a82]'
const botonPagina =
  'min-w-[2rem] rounded-md border px-2.5 py-1.5 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-[#2b9a82] disabled:cursor-not-allowed disabled:opacity-40'
</script>

<template>
  <div class="flex min-h-screen bg-white text-[#0e1729]">
    <SidebarNav />

    <main class="flex-1 ml-64 min-w-0 px-10 py-8 space-y-8">
      <header class="space-y-6">
        <div class="flex h-1 w-24" aria-hidden="true">
          <span class="flex-1 bg-[#f2c230]"></span>
          <span class="flex-1 bg-[#2b9a82]"></span>
          <span class="flex-1 bg-[#1d4b44]"></span>
        </div>

        <Link
          :href="route('asignaciones.index')"
          class="inline-flex items-center gap-2 rounded-md border border-[#1d4b44] bg-[#e6f2ef] px-3 py-2 text-sm font-medium text-[#1d4b44] hover:bg-[#1d4b44] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#2b9a82] transition-colors"
        >
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path
              fill-rule="evenodd"
              d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
              clip-rule="evenodd"
            />
          </svg>
          Volver a asignaciones
        </Link>

        <div class="border-l-4 border-[#1d4b44] pl-4">
          <h1 class="text-3xl font-medium text-[#0e1729]">{{ evento.nombreEvento }}</h1>
        </div>
      </header>

      <!-- Datos del evento: un color e ícono por dato -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div
          v-for="dato in datosEvento"
          :key="dato.clave"
          class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
        >
          <span
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg"
            :class="dato.chip"
            aria-hidden="true"
          >
            <svg
              class="h-6 w-6"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.7"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path v-for="(d, i) in dato.icono" :key="i" :d="d" />
            </svg>
          </span>
          <div class="min-w-0">
            <p class="text-xs text-slate-500">{{ dato.etiqueta }}</p>
            <p class="text-base font-semibold text-[#0e1729] break-words">{{ dato.valor }}</p>
          </div>
        </div>
      </section>

      <!-- Buscador y resumen -->
      <section class="flex flex-wrap items-center justify-between gap-4">
        <div class="w-full max-w-md">
          <label for="buscar-emprendimiento" class="sr-only">Buscar emprendimiento</label>
          <input
            id="buscar-emprendimiento"
            v-model="busqueda"
            type="search"
            placeholder="Buscar por emprendimiento, rubro, responsable o contacto"
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm placeholder:text-slate-400 focus:border-[#2b9a82] focus:outline-none focus:ring-2 focus:ring-[#2b9a82]/30"
          />
        </div>
        <div class="flex flex-wrap items-center gap-4">
          <p class="text-sm text-slate-600">
            {{ totalConvocados }} convocados y {{ totalAsistentes }} asistentes, de
            {{ lista.length }} emprendimientos.
          </p>
          <GuardadoIndicador :estado="estadoGuardado" />
          <a
            v-if="puedeExportar"
            :href="route('asignaciones.exportar', evento.idEvento)"
            class="inline-flex items-center gap-2 rounded-md bg-[#1d4b44] px-4 py-2 text-sm font-medium text-white hover:bg-[#163a35] focus:outline-none focus:ring-2 focus:ring-[#2b9a82] focus:ring-offset-2 transition-colors"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Exportar PDF
          </a>
          <span
            v-else
            class="inline-flex cursor-not-allowed items-center gap-2 rounded-md bg-slate-200 px-4 py-2 text-sm font-medium text-slate-500"
            :title="pendientes > 0 ? 'Guardando cambios…' : 'Marcá al menos un asistente para exportar'"
          >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Exportar PDF
          </span>
        </div>
      </section>

      <p
        v-if="error"
        role="alert"
        class="rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
      >
        {{ error }}
      </p>

      <p v-if="lista.length === 0" class="py-10 text-center text-slate-400">
        Todavía no hay emprendimientos cargados.
      </p>
      <p v-else-if="filtrados.length === 0" class="py-10 text-center text-slate-400">
        No se encontraron emprendimientos para "{{ busqueda }}".
      </p>

      <template v-else>
        <!-- Rubros: una pestaña por rubro, con sus dos checks para marcar todo el rubro -->
        <nav aria-label="Rubros" class="flex gap-2 overflow-x-auto pb-2">
          <div
            v-for="p in pestanias"
            :key="p.rubro"
            class="w-48 shrink-0 overflow-hidden rounded-lg border"
            :class="
              actual.rubro === p.rubro
                ? 'border-[#1d4b44] bg-[#e6f2ef]'
                : 'border-slate-300 bg-white hover:bg-slate-50'
            "
          >
            <button
              type="button"
              class="block w-full px-3 pb-1.5 pt-2.5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#2b9a82]"
              :aria-pressed="actual.rubro === p.rubro"
              @click="rubroElegido = p.rubro"
            >
              <span class="block truncate text-sm font-semibold text-[#1d4b44]">{{ p.nombre }}</span>
              <span class="text-xs text-slate-500">
                {{ p.items.length }} {{ p.items.length === 1 ? 'emprendimiento' : 'emprendimientos' }}
              </span>
            </button>
            <div class="flex border-t border-slate-200 text-xs text-slate-600">
              <label
                class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 py-2 hover:bg-black/5"
                :title="`Marcar o desmarcar convocados de ${p.nombre}`"
              >
                <input
                  type="checkbox"
                  :class="casilla"
                  :checked="p.convocado.todos"
                  :indeterminate="p.convocado.parcial"
                  :aria-label="`Convocar a todos los de ${p.nombre}`"
                  @change="marcarGrupo(p, 'convocado', $event.target.checked)"
                />
                Conv. {{ p.convocado.marcados }}
              </label>
              <label
                class="flex flex-1 cursor-pointer items-center justify-center gap-1.5 border-l border-slate-200 py-2 hover:bg-black/5"
                :title="`Marcar o desmarcar asistentes de ${p.nombre}`"
              >
                <input
                  type="checkbox"
                  :class="casilla"
                  :checked="p.asistio.todos"
                  :indeterminate="p.asistio.parcial"
                  :aria-label="`Marcar asistencia de todos los de ${p.nombre}`"
                  @change="marcarGrupo(p, 'asistio', $event.target.checked)"
                />
                Asist. {{ p.asistio.marcados }}
              </label>
            </div>
          </div>
        </nav>

        <!-- Tabla del rubro elegido -->
        <section class="space-y-3">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-medium text-[#1d4b44]">{{ actual.nombre }}</h2>
            <div class="flex flex-wrap items-center gap-5 text-xs text-slate-600">
              <span class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-sm border border-[#f2c230] bg-[#fff6d9]"></span>
                Convocado
              </span>
              <span class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-sm border border-[#2b9a82] bg-[#e6f4ef]"></span>
                Asistió
              </span>
              <span class="flex items-center gap-2">
                <span class="h-3 w-3 rounded-sm border border-slate-300 bg-white"></span>
                Sin marcar
              </span>
            </div>
          </div>

          <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full table-fixed text-left text-sm">
              <thead>
                <tr class="border-b border-slate-300 bg-slate-100 text-xs text-slate-700">
                  <th class="w-[34%] py-3 pl-4 pr-4 font-medium">Emprendimiento</th>
                  <th class="w-[27%] py-3 pr-4 font-medium">Responsables</th>
                  <th class="w-[27%] py-3 pr-4 font-medium">Contacto</th>
                  <th class="w-20 py-3 text-center font-medium">Convocado</th>
                  <th class="w-20 py-3 text-center font-medium">Asistió</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="e in visibles"
                  :key="e.idEmprendimiento"
                  :class="claseFila(e)"
                  class="border-b border-slate-200 align-middle transition-colors"
                >
                  <td class="py-3 pl-4 pr-4 break-words">
                    <span class="block font-semibold">{{ e.nombreEmprendimiento }}</span>
                    <span v-if="actual.rubro === TODOS" class="block text-xs text-slate-500">
                      {{ rubroDe(e) }}
                    </span>
                  </td>
                  <td class="py-3 pr-4 text-slate-700 break-words">
                    <template v-if="lineas(e.responsables).length">
                      <span v-for="(r, i) in lineas(e.responsables)" :key="i" class="block">{{ r }}</span>
                    </template>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="py-3 pr-4 text-slate-700 break-words">
                    <template v-if="lineas(e.contacto).length">
                      <span v-for="(c, i) in lineas(e.contacto)" :key="i" class="block">{{ c }}</span>
                    </template>
                    <span v-else class="text-slate-400">-</span>
                  </td>
                  <td class="p-0">
                    <label class="flex h-14 w-full cursor-pointer items-center justify-center hover:bg-black/5">
                      <input
                        type="checkbox"
                        :checked="e.convocado"
                        :class="casilla"
                        :aria-label="`Convocado: ${e.nombreEmprendimiento}`"
                        @change="actualizar(e, 'convocado', $event.target.checked)"
                      />
                    </label>
                  </td>
                  <td class="p-0">
                    <label class="flex h-14 w-full cursor-pointer items-center justify-center hover:bg-black/5">
                      <input
                        type="checkbox"
                        :checked="e.asistio"
                        :class="casilla"
                        :aria-label="`Asistió: ${e.nombreEmprendimiento}`"
                        @change="actualizar(e, 'asistio', $event.target.checked)"
                      />
                    </label>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación: 15 por página -->
          <nav
            v-if="totalPaginas > 1"
            class="flex flex-wrap items-center justify-between gap-3"
            :aria-label="`Paginación de ${actual.nombre}`"
          >
            <p class="text-xs text-slate-500">
              Mostrando {{ desde + 1 }}-{{ hasta }} de {{ actual.items.length }}
            </p>
            <div class="flex items-center gap-1">
              <button
                type="button"
                :class="[botonPagina, 'border-slate-300 text-slate-600 hover:bg-slate-50']"
                :disabled="paginaActual === 1"
                @click="irA(paginaActual - 1)"
              >
                Anterior
              </button>
              <button
                v-for="n in ventana"
                :key="n"
                type="button"
                :class="[
                  botonPagina,
                  n === paginaActual
                    ? 'border-[#1d4b44] bg-[#1d4b44] text-white'
                    : 'border-slate-300 text-slate-600 hover:bg-slate-50'
                ]"
                :aria-current="n === paginaActual ? 'page' : undefined"
                @click="irA(n)"
              >
                {{ n }}
              </button>
              <button
                type="button"
                :class="[botonPagina, 'border-slate-300 text-slate-600 hover:bg-slate-50']"
                :disabled="paginaActual === totalPaginas"
                @click="irA(paginaActual + 1)"
              >
                Siguiente
              </button>
            </div>
          </nav>
        </section>
      </template>
    </main>
  </div>
</template>