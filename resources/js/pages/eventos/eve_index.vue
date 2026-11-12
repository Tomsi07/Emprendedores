<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  eventos: {
    type: Array,
    default: () => []
  }
})

// Tipos de evento (lista fija; tiene que coincidir con la del controlador)
const listaTipos = ['Capacitación', 'Encuentro', 'Exposición', 'Feria']

// ---------- Filtros y paginación (todo en el navegador) ----------
const filtrosVacios = () => ({ nombre: '', ciudad: '', lugar: '', tipo: '', desde: '', hasta: '' })
const filtros = ref(filtrosVacios())
const porPagina = 15
const pagina = ref(1)

// Ignora mayúsculas y tildes ("exposicion" encuentra "Exposición")
const normalizar = (texto) =>
  (texto ?? '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

const filtrados = computed(() => {
  const nombre = normalizar(filtros.value.nombre)
  const ciudad = normalizar(filtros.value.ciudad)
  const lugar = normalizar(filtros.value.lugar)
  const { tipo, desde, hasta } = filtros.value

  return props.eventos.filter((item) => {
    if (nombre && !normalizar(item.nombreEvento).includes(nombre)) return false
    if (ciudad && !normalizar(item.ciudad).includes(ciudad)) return false
    if (lugar && !normalizar(item.lugar).includes(lugar)) return false
    if (tipo && item.tipoDeEvento !== tipo) return false
    // Las fechas llegan como AAAA-MM-DD, así que se pueden comparar como texto
    if (desde && (!item.fecha || item.fecha < desde)) return false
    if (hasta && (!item.fecha || item.fecha > hasta)) return false
    return true
  })
})

const totalPaginas = computed(() => Math.max(1, Math.ceil(filtrados.value.length / porPagina)))
const paginaActual = computed(() => Math.min(pagina.value, totalPaginas.value))

const visibles = computed(() => {
  const inicio = (paginaActual.value - 1) * porPagina
  return filtrados.value.slice(inicio, inicio + porPagina)
})

const desdeNumero = computed(() => (filtrados.value.length === 0 ? 0 : (paginaActual.value - 1) * porPagina + 1))
const hastaNumero = computed(() => Math.min(paginaActual.value * porPagina, filtrados.value.length))

const botonesPagina = computed(() => {
  const total = totalPaginas.value
  const actual = paginaActual.value
  const paginas = []
  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || Math.abs(i - actual) <= 2) {
      paginas.push(i)
    } else if (paginas[paginas.length - 1] !== '…') {
      paginas.push('…')
    }
  }
  return paginas
})

// Al cambiar cualquier filtro se vuelve a la primera página
watch(filtros, () => { pagina.value = 1 }, { deep: true })

const limpiar = () => {
  filtros.value = filtrosVacios()
}

// ---------- Formulario ----------
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = useForm({
  nombreEvento: '',
  ciudad: '',
  lugar: '',
  tipoDeEvento: '',
  fecha: ''
})

const formatFecha = (f) => {
  if (!f) return '-'
  const [y, m, d] = f.substring(0, 10).split('-')
  return `${d}/${m}/${y}`
}

const abrirCrear = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  showModal.value = true
}

const abrirEditar = (item) => {
  isEditing.value = true
  editingId.value = item.idEvento || item.id
  form.clearErrors()

  form.nombreEvento = item.nombreEvento || ''
  form.ciudad = item.ciudad || ''
  form.lugar = item.lugar || ''
  form.tipoDeEvento = item.tipoDeEvento || ''
  form.fecha = item.fecha ? item.fecha.substring(0, 10) : ''

  showModal.value = true
}

const submit = () => {
  if (isEditing.value) {
    form.put(`/eventos/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  } else {
    form.post('/eventos', {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  }
}

const eliminar = (id) => {
  if (!id) return

  if (confirm('¿Estás seguro de que querés eliminar este evento?')) {
    router.delete(`/eventos/${id}`, {
      preserveScroll: true
    })
  }
}

// Campos de la ventana (línea simple) y de los filtros (caja con foco verde)
const inputLinea =
  'w-full bg-transparent border-0 border-b border-slate-300 px-0 py-2 text-sm text-[#0e1729] placeholder-slate-400 focus:border-[#1d4b44] focus:ring-0 outline-none transition-colors'
const inputCaja =
  'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm placeholder:text-slate-400 focus:border-[#2b9a82] focus:outline-none focus:ring-2 focus:ring-[#2b9a82]/30'
const etiqueta = 'block text-xs text-slate-500'
const th = 'px-4 py-3 font-medium'
const td = 'px-4 py-3'
const botonPag = 'min-w-[2rem] rounded-md border px-2.5 py-1.5 text-center text-xs font-medium transition-colors'
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

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div class="border-l-4 border-[#1d4b44] pl-4">
            <h1 class="text-3xl font-medium text-[#0e1729]">ACTIVIDADES / EVENTOS</h1>
            <p class="text-sm text-slate-500 mt-1">
              Listado general de ferias, muestras y encuentros programados.
            </p>
          </div>
          <button
            @click="abrirCrear"
            class="rounded-md bg-[#1d4b44] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#163a35] focus:outline-none focus:ring-2 focus:ring-[#2b9a82] focus:ring-offset-2 transition-colors"
          >
            + Nuevo evento
          </button>
        </div>
      </header>

      <!-- Buscadores -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-x-4 gap-y-4 items-end">
        <label class="block space-y-1">
          <span :class="etiqueta">Evento</span>
          <input v-model="filtros.nombre" type="search" placeholder="Buscar por nombre" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Ciudad</span>
          <input v-model="filtros.ciudad" type="search" placeholder="Ej: Saladillo" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Lugar</span>
          <input v-model="filtros.lugar" type="search" placeholder="Ej: Plaza Principal" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Tipo de evento</span>
          <select v-model="filtros.tipo" :class="inputCaja">
            <option value="">Todos</option>
            <option v-for="tipo in listaTipos" :key="tipo" :value="tipo">{{ tipo }}</option>
          </select>
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Fecha desde</span>
          <input v-model="filtros.desde" type="date" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Fecha hasta</span>
          <input v-model="filtros.hasta" type="date" :class="inputCaja" />
        </label>

        <div class="flex items-end">
          <button
            type="button"
            @click="limpiar"
            class="py-2 text-sm text-slate-500 hover:text-[#1d4b44] underline-offset-4 hover:underline transition-colors"
          >
            Limpiar filtros
          </button>
        </div>
      </section>

      <!-- Tabla -->
      <section class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-slate-300 bg-slate-100 text-xs text-slate-700">
                <th :class="th">Evento</th>
                <th :class="th">Ciudad</th>
                <th :class="th">Lugar</th>
                <th :class="th">Tipo de evento</th>
                <th :class="th">Fecha</th>
                <th :class="[th, 'text-right']">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in visibles"
                :key="item.idEvento || item.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition-colors"
              >
                <td :class="[td, 'font-semibold']">{{ item.nombreEvento }}</td>
                <td :class="[td, 'text-slate-600']">{{ item.ciudad || '-' }}</td>
                <td :class="[td, 'text-slate-600']">{{ item.lugar || '-' }}</td>
                <td :class="[td, 'text-slate-600']">{{ item.tipoDeEvento || '-' }}</td>
                <td :class="[td, 'text-slate-600']">{{ formatFecha(item.fecha) }}</td>
                <td :class="[td, 'text-right space-x-2']">
                  <button
                    @click="abrirEditar(item)"
                    class="rounded-md border border-[#1d4b44] px-3 py-1.5 text-xs font-medium text-[#1d4b44] hover:bg-[#1d4b44] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#2b9a82] transition-colors"
                  >
                    Editar
                  </button>
                  <button
                    @click="eliminar(item.idEvento || item.id)"
                    class="rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 transition-colors"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
              <tr v-if="visibles.length === 0">
                <td colspan="6" class="py-10 text-center text-slate-400">
                  No hay eventos que coincidan con los filtros.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
          <span class="text-xs text-slate-500">
            Mostrando {{ desdeNumero }}-{{ hastaNumero }} de {{ filtrados.length }}
          </span>
          <div v-if="totalPaginas > 1" class="flex flex-wrap items-center gap-1">
            <button
              type="button"
              :disabled="paginaActual === 1"
              @click="pagina = paginaActual - 1"
              :class="[botonPag, 'border-slate-300 text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-300 disabled:hover:bg-transparent']"
            >
              ‹ Anterior
            </button>

            <template v-for="(p, i) in botonesPagina" :key="i">
              <span v-if="p === '…'" class="px-1 text-xs text-slate-400">…</span>
              <button
                v-else
                type="button"
                @click="pagina = p"
                :class="[
                  botonPag,
                  p === paginaActual
                    ? 'border-[#1d4b44] bg-[#1d4b44] text-white'
                    : 'border-slate-300 text-slate-600 hover:bg-slate-50'
                ]"
              >
                {{ p }}
              </button>
            </template>

            <button
              type="button"
              :disabled="paginaActual === totalPaginas"
              @click="pagina = paginaActual + 1"
              :class="[botonPag, 'border-slate-300 text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-300 disabled:hover:bg-transparent']"
            >
              Siguiente ›
            </button>
          </div>
        </div>
      </section>
    </main>

    <!-- Modal Formulario -->
    <div v-if="showModal" class="fixed inset-0 bg-[#0e1729]/70 flex items-center justify-center p-4 z-50">
      <div class="bg-white max-w-lg w-full p-8 space-y-6 border-l-4 border-[#1d4b44] max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start">
          <h3 class="text-2xl font-medium text-[#0e1729]">
            {{ isEditing ? 'Editar evento' : 'Nuevo evento' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 transition-colors" aria-label="Cerrar">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <label class="block">
            <span :class="etiqueta">Nombre del evento</span>
            <input
              v-model="form.nombreEvento"
              type="text"
              placeholder="Ej: Feria de Emprendedores Locales"
              required
              :class="inputLinea"
            />
            <p v-if="form.errors.nombreEvento" class="mt-1 text-xs text-rose-600">{{ form.errors.nombreEvento }}</p>
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <label class="block">
              <span :class="etiqueta">Ciudad</span>
              <input v-model="form.ciudad" type="text" placeholder="Ej: Saladillo" required :class="inputLinea" />
              <p v-if="form.errors.ciudad" class="mt-1 text-xs text-rose-600">{{ form.errors.ciudad }}</p>
            </label>
            <label class="block">
              <span :class="etiqueta">Lugar</span>
              <input v-model="form.lugar" type="text" placeholder="Ej: Plaza Principal" required :class="inputLinea" />
              <p v-if="form.errors.lugar" class="mt-1 text-xs text-rose-600">{{ form.errors.lugar }}</p>
            </label>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <label class="block">
              <span :class="etiqueta">Tipo de evento</span>
              <select v-model="form.tipoDeEvento" required :class="inputLinea">
                <option value="" disabled>Seleccioná un tipo</option>
                <option v-for="tipo in listaTipos" :key="tipo" :value="tipo">{{ tipo }}</option>
              </select>
              <p v-if="form.errors.tipoDeEvento" class="mt-1 text-xs text-rose-600">{{ form.errors.tipoDeEvento }}</p>
            </label>
            <label class="block">
              <span :class="etiqueta">Fecha</span>
              <input v-model="form.fecha" type="date" required :class="inputLinea" />
              <p v-if="form.errors.fecha" class="mt-1 text-xs text-rose-600">{{ form.errors.fecha }}</p>
            </label>
          </div>

          <div class="flex justify-end items-center gap-4 pt-4">
            <button type="button" @click="showModal = false" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="rounded-md bg-[#1d4b44] px-6 py-2.5 text-sm font-medium text-white hover:bg-[#163a35] disabled:opacity-50 transition-colors"
            >
              {{ isEditing ? 'Guardar cambios' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>