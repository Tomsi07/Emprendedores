<script setup>
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  emprendimientos: {
    type: Object,
    required: true
  },
  emprendedores: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({ nombre: '', rubro: '', estado: '', emprendedor: '' })
  }
})

const filtros = ref({
  nombre: props.filters.nombre || '',
  rubro: props.filters.rubro || '',
  estado: props.filters.estado || '',
  emprendedor: props.filters.emprendedor || ''
})
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

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

let timer = null
const buscar = () => {
  clearTimeout(timer)
  timer = setTimeout(() => {
    router.get('/emprendimientos', filtros.value, {
      preserveState: true,
      replace: true
    })
  }, 300)
}

const limpiar = () => {
  filtros.value = { nombre: '', rubro: '', estado: '', emprendedor: '' }
  buscar()
}

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
  
  form.necesidades = item.necesidades && item.necesidades.length > 0
    ? item.necesidades.map(n => n.titulo || n.tituloNecesidad || '').filter(Boolean).join(', ')
    : ''

  form.actividades = item.actividades && item.actividades.length > 0
    ? item.actividades.map(a => a.nombreActividad || a.nombre || '').filter(Boolean).join(', ')
    : ''
  
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
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  } else {
    form.post('/emprendimientos', {
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

// Función auxiliar para el estilo visual del badge según el estado
const getBadgeClass = (estado) => {
  switch (estado) {
    case 'Activo':
      return 'bg-emerald-100 text-emerald-800 border-emerald-200'
    case 'Inactivo':
      return 'bg-slate-100 text-slate-700 border-slate-200'
    case 'Suspendido':
      return 'bg-rose-100 text-rose-800 border-rose-200'
    default:
      return 'bg-emerald-100 text-emerald-800 border-emerald-200'
  }
}

// Clases reutilizables con la estética del login
const inputLinea =
  'w-full bg-transparent border-0 border-b border-slate-300 px-0 py-2 text-sm text-[#0e1729] placeholder-slate-400 focus:border-[#1d4b44] focus:ring-0 outline-none transition-colors'
const etiqueta = 'block text-xs text-slate-500'
</script>

<template>
<<<<<<< HEAD
  <div class="flex min-h-screen bg-white text-[#0e1729]">
    <!-- Navegación Lateral -->
    <SidebarNav />

    <!-- Contenido Principal con Margen a la Izquierda -->
    <main class="flex-1 ml-64 px-10 py-8 space-y-10">

      <!-- Encabezado de la Sección -->
      <header class="space-y-6">
        <!-- Barra tricolor (igual a la del login) -->
        <div class="flex h-1 w-24" aria-hidden="true">
          <span class="flex-1 bg-[#f2c230]"></span>
          <span class="flex-1 bg-[#2b9a82]"></span>
          <span class="flex-1 bg-[#1d4b44]"></span>
        </div>

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div class="border-l-4 border-[#1d4b44] pl-4">
            <h1 class="text-3xl font-medium text-[#0e1729]">Emprendimientos</h1>
            <p class="text-sm text-slate-500 mt-1">Proyectos e iniciativas registradas en Saladillo.</p>
          </div>
          <button
            @click="showModal = true"
            class="px-5 py-3 bg-[#1d4b44] hover:bg-[#163b36] text-white text-sm font-medium transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#2b9a82] focus-visible:ring-offset-2"
          >
            + Nuevo emprendimiento
          </button>
        </div>
      </header>

      <!-- Buscadores -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_1.4fr_auto] gap-x-8 gap-y-5 items-end">
        <label class="block">
          <span :class="etiqueta">Emprendimiento</span>
          <input
            v-model="filtros.nombre"
            @input="buscar"
            type="text"
            placeholder="Buscar por nombre"
            :class="inputLinea"
          />
        </label>

        <label class="block">
          <span :class="etiqueta">Rubro</span>
          <input
            v-model="filtros.rubro"
            @input="buscar"
            type="text"
            placeholder="Ej: Gastronomía"
            :class="inputLinea"
          />
        </label>

        <label class="block">
          <span :class="etiqueta">Estado</span>
          <select
            v-model="filtros.estado"
            @change="buscar"
            :class="inputLinea"
          >
            <option value="">Todos</option>
            <option value="Activo">Activo</option>
            <option value="En desarrollo">En desarrollo</option>
            <option value="Inactivo">Inactivo</option>
          </select>
        </label>

        <label class="block">
          <span :class="etiqueta">Emprendedor responsable</span>
          <input
            v-model="filtros.emprendedor"
            @input="buscar"
            type="text"
            placeholder="Nombre o apellido"
            :class="inputLinea"
          />
        </label>

        <button
          type="button"
          @click="limpiar"
          class="py-2 text-sm text-slate-500 hover:text-[#1d4b44] underline-offset-4 hover:underline transition-colors"
        >
          Limpiar filtros
        </button>
      </section>
=======
  <div class="flex min-h-screen bg-slate-100">
    <SidebarNav />

    <main class="flex-1 ml-64 p-6 space-y-6">
      
      <!-- Encabezado -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">Gestión de Emprendimientos</h1>
          <p class="text-slate-500 text-sm">Listado general de proyectos e iniciativas registradas.</p>
        </div>
        <button 
          @click="abrirCrear"
          class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition flex items-center justify-center gap-2"
        >
          <span>+</span> Nuevo Emprendimiento
        </button>
      </div>

      <!-- Buscador -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <input
          v-model="search"
          @input="buscar"
          type="text"
          placeholder="Buscar por emprendimiento o rubro..."
          class="w-full px-4 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>
>>>>>>> origin/main

      <!-- Tabla de Datos -->
      <section>
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b-2 border-[#1d4b44] text-slate-500 text-xs font-medium">
              <th class="py-3 pr-6 font-medium">Emprendimiento</th>
              <th class="py-3 pr-6 font-medium">Rubro</th>
              <th class="py-3 pr-6 font-medium">Estado</th>
              <th class="py-3 pr-6 font-medium">Emprendedor responsable</th>
              <th class="py-3 text-right font-medium">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
<<<<<<< HEAD
            <tr
              v-for="item in emprendimientos.data"
              :key="item.idEmprendimiento"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="py-4 pr-6 font-semibold text-[#0e1729]">
=======
            <tr v-for="item in emprendimientos.data" :key="item.idEmprendimiento || item.id" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-bold text-slate-900">
>>>>>>> origin/main
                {{ item.nombreEmprendimiento }}
              </td>
              <td class="py-4 pr-6 text-slate-600">
                {{ item.rubro || '-' }}
              </td>
<<<<<<< HEAD
              <td class="py-4 pr-6">
                <span
                  class="inline-flex items-center gap-2 text-xs font-medium"
                  :class="{
                    'text-[#1d4b44]': item.estado === 'Activo' || !item.estado,
                    'text-amber-700': item.estado === 'En desarrollo',
                    'text-slate-500': item.estado === 'Inactivo'
                  }"
=======
              <td class="px-6 py-4">
                <span 
                  class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                  :class="getBadgeClass(item.estado || 'Activo')"
>>>>>>> origin/main
                >
                  <span
                    class="h-2 w-2 rounded-full"
                    :class="{
                      'bg-[#2b9a82]': item.estado === 'Activo' || !item.estado,
                      'bg-[#f2c230]': item.estado === 'En desarrollo',
                      'bg-slate-400': item.estado === 'Inactivo'
                    }"
                  ></span>
                  {{ item.estado || 'Activo' }}
                </span>
              </td>
              <td class="py-4 pr-6 text-slate-700">
                <span v-if="item.emprendedor">
                  {{ item.emprendedor.nombreEmprendedor }} {{ item.emprendedor.apellido || '' }}
                </span>
                <span v-else class="text-slate-400 italic">Sin asignar</span>
              </td>
<<<<<<< HEAD
              <td class="py-4 text-right">
                <button
                  @click="eliminar(item.idEmprendimiento)"
                  class="text-xs font-medium text-rose-700 hover:text-rose-900 hover:underline underline-offset-4 transition-colors"
=======
              <td class="px-6 py-4 text-right space-x-2">
                <button 
                  @click="abrirEditar(item)" 
                  class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold px-2 py-1 rounded hover:bg-indigo-50 transition"
                >
                  Editar
                </button>
                <button 
                  @click="eliminar(item.idEmprendimiento || item.id)" 
                  class="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 transition"
>>>>>>> origin/main
                >
                  Eliminar
                </button>
              </td>
            </tr>
<<<<<<< HEAD
            <tr v-if="emprendimientos.data.length === 0">
              <td colspan="5" class="py-10 text-center text-slate-400">
                No hay emprendimientos que coincidan con los filtros.
=======
            <tr v-if="!emprendimientos.data || emprendimientos.data.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                No se encontraron emprendimientos registrados.
>>>>>>> origin/main
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
          <span class="text-slate-500 text-xs sm:text-sm">
            Mostrando {{ emprendimientos.from || 0 }} a {{ emprendimientos.to || 0 }} de {{ emprendimientos.total || 0 }} resultados
          </span>

          <div class="flex flex-wrap gap-1">
            <Component
              :is="link.url ? Link : 'span'"
              v-for="(link, index) in emprendimientos.links"
              :key="index"
              :href="link.url"
              v-html="link.label"
              class="px-3 py-1.5 text-xs font-medium transition-colors"
              :class="{
                'bg-[#1d4b44] text-white': link.active,
                'text-slate-600 hover:bg-slate-100': link.url && !link.active,
                'text-slate-300 cursor-not-allowed': !link.url
              }"
            />
          </div>
        </div>
      </section>

    </main>

    <!-- Modal Formulario -->
<<<<<<< HEAD
    <div v-if="showModal" class="fixed inset-0 bg-[#0e1729]/70 flex items-center justify-center p-4 z-50">
      <div class="bg-white max-w-lg w-full p-8 space-y-6 border-l-4 border-[#1d4b44]">
        <div class="flex justify-between items-start">
          <h3 class="text-2xl font-medium text-[#0e1729]">Nuevo emprendimiento</h3>
          <button
            @click="showModal = false"
            class="text-slate-400 hover:text-slate-700 transition-colors"
            aria-label="Cerrar"
          >✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <label class="block">
            <span :class="etiqueta">Emprendedor responsable</span>
            <select v-model="form.idEmprendedor" required :class="inputLinea">
              <option v-for="emp in emprendedores" :key="emp.idEmprendedor" :value="emp.idEmprendedor">
                {{ emp.nombreEmprendedor }} {{ emp.apellido }}
              </option>
            </select>
          </label>
=======
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b pb-3">
          <h3 class="text-lg font-bold text-slate-800">
            {{ isEditing ? 'Editar Emprendimiento' : 'Registrar Nuevo Emprendimiento' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">
              Emprendedor Responsable
            </label>
            <input 
              type="text" 
              v-model="form.nombreEmprendedor" 
              placeholder="Nombre y Apellido" 
              required 
              class="w-full p-2 border rounded-lg text-sm bg-white"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Teléfono</label>
              <input 
                type="text" 
                v-model="form.telefono" 
                placeholder="Ej: 2344123456" 
                class="w-full p-2 border rounded-lg text-sm bg-white"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email</label>
              <input 
                type="email" 
                v-model="form.email" 
                placeholder="ejemplo@correo.com" 
                class="w-full p-2 border rounded-lg text-sm bg-white"
              />
            </div>
          </div>
>>>>>>> origin/main

          <label class="block">
            <span :class="etiqueta">Nombre del emprendimiento</span>
            <input v-model="form.nombreEmprendimiento" type="text" required :class="inputLinea" />
          </label>

<<<<<<< HEAD
          <div class="grid grid-cols-2 gap-6">
            <label class="block">
              <span :class="etiqueta">Rubro</span>
              <input v-model="form.rubro" type="text" placeholder="Ej: Gastronomía, Textil" :class="inputLinea" />
            </label>
            <label class="block">
              <span :class="etiqueta">Estado</span>
              <select v-model="form.estado" :class="inputLinea">
                <option value="Activo">Activo</option>
                <option value="En desarrollo">En desarrollo</option>
                <option value="Inactivo">Inactivo</option>
=======
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Seleccionable de Rubro -->
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Rubro</label>
              <select 
                v-model="rubroSeleccionado" 
                class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="" disabled>Seleccioná un rubro...</option>
                <option v-for="rubro in listaRubros" :key="rubro" :value="rubro">
                  {{ rubro }}
                </option>
              </select>
            </div>

            <!-- Seleccionable de Estado -->
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Estado</label>
              <select 
                v-model="form.estado" 
                class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option v-for="est in listaEstados" :key="est" :value="est">
                  {{ est }}
                </option>
>>>>>>> origin/main
              </select>
            </label>
          </div>

<<<<<<< HEAD
          <label class="block">
            <span :class="etiqueta">Descripción (opcional)</span>
            <textarea v-model="form.descripcion" rows="3" :class="inputLinea"></textarea>
          </label>

          <div class="flex justify-end items-center gap-4 pt-4">
            <button
              type="button"
              @click="showModal = false"
              class="text-sm text-slate-500 hover:text-slate-800 transition-colors"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-6 py-3 text-sm bg-[#1d4b44] hover:bg-[#163b36] text-white font-medium disabled:opacity-50 transition-colors"
            >
              Guardar
=======
          <!-- Campo dinámico para "Otro" -->
          <div v-if="rubroSeleccionado === 'Otro'">
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Escribí el Rubro</label>
            <input 
              v-model="rubroPersonalizado" 
              type="text" 
              placeholder="Especificá el rubro del emprendimiento" 
              required
              class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500" 
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Productos y/o servicios especificos</label>
            <textarea 
              v-model="form.actividades" 
              rows="2" 
              placeholder="Ej: Tortas, sevicios de peloteros, etc" 
              class="w-full p-2 border rounded-lg text-sm bg-white"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Necesidades</label>
            <textarea 
              v-model="form.necesidades" 
              rows="2" 
              placeholder="Ej: Financiamiento, Maquinaria nueva" 
              class="w-full p-2 border rounded-lg text-sm bg-white"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50 transition">
              {{ isEditing ? 'Guardar Cambios' : 'Guardar' }}
>>>>>>> origin/main
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>