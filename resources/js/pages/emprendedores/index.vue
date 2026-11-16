<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  emprendimientos: {
    type: Array,
    default: () => []
  }
})

// Lista de rubros principales
const listaRubros = [
  'Artesanías / Manualidades',
  'Gastronomía / Alimenticio',
  'Textil e Indumentaria',
  'Viveril / Botánica',
  'Carpintería / Herrería / Metalúrgica',
  'Servicios',
  'Producción Agropecuaria / Frutihortícola',
  'Comercio / Gráfica / Diseño',
  'Otro'
]

// Opciones del filtro de rubro: los mismos del formulario de alta
const rubrosFiltro = listaRubros.filter((r) => r !== 'Otro')

// ---------- Filtros y paginación (todo en el navegador) ----------
const filtros = ref({ nombre: '', rubro: '', estado: '', emprendedor: '' })
const porPagina = 10
const pagina = ref(1)

// Ignora mayúsculas y tildes ("gastronomia" encuentra "Gastronomía")
const normalizar = (texto) =>
  (texto ?? '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

// Contacto: teléfono y email, uno por línea. Acepta item.contacto (texto o lista)
// o los campos del emprendedor / del propio registro.
const contactoDe = (item) => {
  const c = item?.contacto
  if (Array.isArray(c)) return c.filter(Boolean).map(String)
  if (c) return [String(c)]
  const tel = item?.emprendedor?.telefono ?? item?.telefono
  const mail = item?.emprendedor?.email ?? item?.email
  return [tel, mail].filter(Boolean).map(String)
}

const filtrados = computed(() => {
  const nombre = normalizar(filtros.value.nombre)
  const rubro = filtros.value.rubro
  const estado = filtros.value.estado
  const responsable = normalizar(filtros.value.emprendedor)

  return props.emprendimientos.filter((item) => {
    if (nombre && !normalizar(item.nombreEmprendimiento).includes(nombre)) return false
    if (rubro === 'Otro') {
      // "Otro": los que tienen un rubro escrito a mano (fuera de la lista fija)
      if (!item.rubro || rubrosFiltro.includes(item.rubro)) return false
    } else if (rubro && item.rubro !== rubro) {
      return false
    }
    if (estado && (item.estado || 'Activo') !== estado) return false
    if (responsable) {
      const nombreResponsable = normalizar(
        `${item.emprendedor?.nombreEmprendedor || ''} ${item.emprendedor?.apellido || ''} ${contactoDe(item).join(' ')}`
      )
      if (!nombreResponsable.includes(responsable)) return false
    }
    return true
  })
})

const totalPaginas = computed(() => Math.max(1, Math.ceil(filtrados.value.length / porPagina)))
const paginaActual = computed(() => Math.min(pagina.value, totalPaginas.value))

const visibles = computed(() => {
  const inicio = (paginaActual.value - 1) * porPagina
  return filtrados.value.slice(inicio, inicio + porPagina)
})

const desde = computed(() => (filtrados.value.length === 0 ? 0 : (paginaActual.value - 1) * porPagina + 1))
const hasta = computed(() => Math.min(paginaActual.value * porPagina, filtrados.value.length))

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
  filtros.value = { nombre: '', rubro: '', estado: '', emprendedor: '' }
}

// ---------- Formulario ----------
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

// Opciones de estado permitidas
const listaEstados = ['Activo', 'Inactivo', 'Suspendido']

const rubroSeleccionado = ref('')
const rubroPersonalizado = ref('')

const form = useForm({
  nombreEmprendedor: '',
  telefono: '',
  email: '',
  nombreEmprendimiento: '',
  rubro: '',
  estado: 'Activo',
  actividades: '',
  necesidades: ''
})

const abrirCrear = () => {
  isEditing.value = false
  editingId.value = null
  rubroSeleccionado.value = ''
  rubroPersonalizado.value = ''
  form.reset()
  form.estado = 'Activo'
  form.clearErrors()
  showModal.value = true
}

const abrirEditar = (item) => {
  isEditing.value = true
  editingId.value = item.idEmprendimiento || item.id
  form.clearErrors()

  const responsable = item.emprendedor
    ? `${item.emprendedor.nombreEmprendedor} ${item.emprendedor.apellido || ''}`.trim()
    : ''

  form.nombreEmprendedor = responsable
  form.telefono = item.emprendedor?.telefono || ''
  form.email = item.emprendedor?.email || ''
  form.nombreEmprendimiento = item.nombreEmprendimiento || ''
  form.estado = item.estado || 'Activo'

  const rubroActual = item.rubro || ''
  if (listaRubros.includes(rubroActual)) {
    rubroSeleccionado.value = rubroActual
    rubroPersonalizado.value = ''
  } else if (rubroActual !== '') {
    rubroSeleccionado.value = 'Otro'
    rubroPersonalizado.value = rubroActual
  } else {
    rubroSeleccionado.value = ''
    rubroPersonalizado.value = ''
  }

  form.necesidades = item.necesidades || ''
  form.actividades = item.productosServicios || ''

  showModal.value = true
}

const submit = () => {
  if (rubroSeleccionado.value === 'Otro') {
    form.rubro = rubroPersonalizado.value
  } else {
    form.rubro = rubroSeleccionado.value
  }

  if (isEditing.value) {
    form.put(`/emprendimientos/${editingId.value}`, {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  } else {
    form.post('/emprendimientos', {
      preserveScroll: true,
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  }
}

const eliminar = (id) => {
  if (!id) {
    alert('Error: No se encontró el ID del emprendimiento.')
    return
  }

  if (confirm('¿Estás seguro de que querés eliminar este emprendimiento?')) {
    router.delete(`/emprendimientos/${id}`, {
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
            <h1 class="text-3xl font-medium text-[#0e1729]">EMPRENDIMIENTOS</h1>
            <p class="text-sm text-slate-500 mt-1">Proyectos e iniciativas registradas en Saladillo.</p>
          </div>
          <button
            @click="abrirCrear"
            class="rounded-md bg-[#1d4b44] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#163a35] focus:outline-none focus:ring-2 focus:ring-[#2b9a82] focus:ring-offset-2 transition-colors"
          >
            + Nuevo emprendimiento
          </button>
        </div>
      </header>

      <!-- Buscadores -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1.4fr_auto] gap-x-4 gap-y-4 items-end">
        <label class="block space-y-1">
          <span :class="etiqueta">Emprendimiento</span>
          <input v-model="filtros.nombre" type="search" placeholder="Buscar por nombre" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Rubro</span>
          <select v-model="filtros.rubro" :class="inputCaja">
            <option value="">Todos</option>
            <option v-for="r in rubrosFiltro" :key="r" :value="r">{{ r }}</option>
            <option value="Otro">Otro</option>
          </select>
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Estado</span>
          <select v-model="filtros.estado" :class="inputCaja">
            <option value="">Todos</option>
            <option v-for="est in listaEstados" :key="est" :value="est">{{ est }}</option>
          </select>
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Emprendedor responsable</span>
          <input v-model="filtros.emprendedor" type="search" placeholder="Nombre, apellido o contacto" :class="inputCaja" />
        </label>

        <button
          type="button"
          @click="limpiar"
          class="py-2 text-sm text-slate-500 hover:text-[#1d4b44] underline-offset-4 hover:underline transition-colors"
        >
          Limpiar filtros
        </button>
      </section>

      <!-- Tabla -->
      <section class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-slate-200">
          <table class="w-full text-left text-sm">
            <thead>
              <tr class="border-b border-slate-300 bg-slate-100 text-xs text-slate-700">
                <th :class="th">Emprendimiento</th>
                <th :class="th">Rubro</th>
                <th :class="th">Estado</th>
                <th :class="th">Emprendedor responsable</th>
                <th :class="th">Contacto</th>
                <th :class="[th, 'text-right whitespace-nowrap']">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in visibles"
                :key="item.idEmprendimiento || item.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition-colors"
              >
                <td :class="[td, 'font-semibold']">{{ item.nombreEmprendimiento }}</td>
                <td :class="[td, 'text-slate-600']">{{ item.rubro || '-' }}</td>
                <td :class="td">
                  <span
                    class="inline-flex items-center gap-2 text-xs font-medium"
                    :class="{
                      'text-[#1d4b44]': item.estado === 'Activo' || !item.estado,
                      'text-amber-700': item.estado === 'Suspendido',
                      'text-slate-500': item.estado === 'Inactivo'
                    }"
                  >
                    <span
                      class="h-2 w-2 rounded-full"
                      :class="{
                        'bg-[#2b9a82]': item.estado === 'Activo' || !item.estado,
                        'bg-[#f2c230]': item.estado === 'Suspendido',
                        'bg-slate-400': item.estado === 'Inactivo'
                      }"
                    ></span>
                    {{ item.estado || 'Activo' }}
                  </span>
                </td>
                <td :class="[td, 'text-slate-700']">
                  <span v-if="item.emprendedor">
                    {{ item.emprendedor.nombreEmprendedor }} {{ item.emprendedor.apellido || '' }}
                  </span>
                  <span v-else class="text-slate-400 italic">Sin asignar</span>
                </td>
                <td :class="[td, 'text-slate-600']">
                  <template v-if="contactoDe(item).length">
                    <span v-for="(c, i) in contactoDe(item)" :key="i" class="block">{{ c }}</span>
                  </template>
                  <span v-else class="text-slate-400">-</span>
                </td>
                <td :class="[td, 'w-px whitespace-nowrap']">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="abrirEditar(item)"
                      class="rounded-md border border-[#1d4b44] px-3 py-1.5 text-xs font-medium text-[#1d4b44] hover:bg-[#1d4b44] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#2b9a82] transition-colors"
                    >
                      Editar
                    </button>
                    <button
                      @click="eliminar(item.idEmprendimiento || item.id)"
                      class="rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 transition-colors"
                    >
                      Eliminar
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="visibles.length === 0">
                <td colspan="6" class="py-10 text-center text-slate-400">
                  No hay emprendimientos que coincidan con los filtros.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
          <span class="text-xs text-slate-500">
            Mostrando {{ desde }}-{{ hasta }} de {{ filtrados.length }}
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
            {{ isEditing ? 'Editar emprendimiento' : 'Nuevo emprendimiento' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 transition-colors" aria-label="Cerrar">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <label class="block">
            <span :class="etiqueta">Emprendedor responsable</span>
            <input v-model="form.nombreEmprendedor" type="text" placeholder="Nombre y apellido" required :class="inputLinea" />
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <label class="block">
              <span :class="etiqueta">Teléfono</span>
              <input v-model="form.telefono" type="text" placeholder="Ej: 2344123456" :class="inputLinea" />
            </label>
            <label class="block">
              <span :class="etiqueta">Email</span>
              <input v-model="form.email" type="email" placeholder="ejemplo@correo.com" :class="inputLinea" />
            </label>
          </div>

          <label class="block">
            <span :class="etiqueta">Nombre del emprendimiento</span>
            <input v-model="form.nombreEmprendimiento" type="text" required :class="inputLinea" />
          </label>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <label class="block">
              <span :class="etiqueta">Rubro</span>
              <select v-model="rubroSeleccionado" required :class="inputLinea">
                <option value="" disabled>Seleccioná un rubro</option>
                <option v-for="rubro in listaRubros" :key="rubro" :value="rubro">{{ rubro }}</option>
              </select>
            </label>
            <label class="block">
              <span :class="etiqueta">Estado</span>
              <select v-model="form.estado" :class="inputLinea">
                <option v-for="est in listaEstados" :key="est" :value="est">{{ est }}</option>
              </select>
            </label>
          </div>

          <label v-if="rubroSeleccionado === 'Otro'" class="block">
            <span :class="etiqueta">Escribí el rubro</span>
            <input
              v-model="rubroPersonalizado"
              type="text"
              placeholder="Especificá el rubro del emprendimiento"
              required
              :class="inputLinea"
            />
          </label>

          <label class="block">
            <span :class="etiqueta">Productos y/o servicios específicos</span>
            <textarea v-model="form.actividades" rows="2" placeholder="Ej: Tortas, servicios de peloteros, etc." :class="inputLinea"></textarea>
          </label>

          <label class="block">
            <span :class="etiqueta">Necesidades</span>
            <textarea v-model="form.necesidades" rows="2" placeholder="Ej: Financiamiento, maquinaria nueva" :class="inputLinea"></textarea>
          </label>

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