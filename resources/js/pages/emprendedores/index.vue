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
    default: () => ({ search: '' })
  }
})

const search = ref(props.filters.search || '')
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

const buscar = () => {
  router.get('/emprendimientos', { search: search.value }, { 
    preserveState: true, 
    replace: true 
  })
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
</script>

<template>
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

      <!-- Tabla de Datos -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-semibold text-xs">
            <tr>
              <th class="px-6 py-4">Emprendimiento</th>
              <th class="px-6 py-4">Rubro</th>
              <th class="px-6 py-4">Estado</th>
              <th class="px-6 py-4">Emprendedor Responsable</th>
              <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="item in emprendimientos.data" :key="item.idEmprendimiento || item.id" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-bold text-slate-900">
                {{ item.nombreEmprendimiento }}
              </td>
              <td class="px-6 py-4">
                {{ item.rubro || '-' }}
              </td>
              <td class="px-6 py-4">
                <span 
                  class="px-2.5 py-1 text-xs font-semibold rounded-full border"
                  :class="getBadgeClass(item.estado || 'Activo')"
                >
                  {{ item.estado || 'Activo' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-700">
                <span v-if="item.emprendedor">
                  {{ item.emprendedor.nombreEmprendedor }} {{ item.emprendedor.apellido || '' }}
                </span>
                <span v-else class="text-slate-400 italic">Sin asignar</span>
              </td>
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
                >
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!emprendimientos.data || emprendimientos.data.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                No se encontraron emprendimientos registrados.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
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
              class="px-3 py-1.5 rounded text-xs font-medium transition"
              :class="{
                'bg-indigo-600 text-white': link.active,
                'text-slate-600 hover:bg-slate-100': link.url && !link.active,
                'text-slate-300 cursor-not-allowed': !link.url
              }"
            />
          </div>
        </div>
      </div>

    </main>

    <!-- Modal Formulario -->
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

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nombre del Emprendimiento</label>
            <input v-model="form.nombreEmprendimiento" type="text" required class="w-full p-2 border rounded-lg text-sm" />
          </div>

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
              </select>
            </div>
          </div>

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
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>