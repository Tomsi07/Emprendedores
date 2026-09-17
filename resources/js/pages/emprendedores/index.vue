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

const form = useForm({
  idEmprendedor: props.emprendedores[0]?.idEmprendedor || '',
  nombreEmprendimiento: '',
  rubro: '',
  estado: 'Activo',
  descripcion: ''
})

const buscar = () => {
  router.get('/emprendimientos', { search: search.value }, { 
    preserveState: true, 
    replace: true 
  })
}

const submit = () => {
  form.post('/emprendimientos', {
    onSuccess: () => {
      form.reset()
      showModal.value = false
    }
  })
}

const eliminar = (id) => {
  if (confirm('¿Estás seguro de que querés eliminar este emprendimiento?')) {
    router.delete(`/emprendimientos/${id}`)
  }
}
</script>

<template>
  <div class="flex min-h-screen bg-slate-100">
    <!-- Navegación Lateral -->
    <SidebarNav />

    <!-- Contenido Principal con Margen a la Izquierda -->
    <main class="flex-1 ml-64 p-6 space-y-6">
      
      <!-- Encabezado de la Sección -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">Gestión de Emprendimientos</h1>
          <p class="text-slate-500 text-sm">Listado general de proyectos e iniciativas registradas.</p>
        </div>
        <button 
          @click="showModal = true"
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
          placeholder="Buscar por nombre de emprendimiento o rubro..."
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
            <tr v-for="item in emprendimientos.data" :key="item.idEmprendimiento" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-bold text-slate-900">
                {{ item.nombreEmprendimiento }}
              </td>
              <td class="px-6 py-4">
                {{ item.rubro || '-' }}
              </td>
              <td class="px-6 py-4">
                <span 
                  class="px-2.5 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-emerald-100 text-emerald-800': item.estado === 'Activo',
                    'bg-amber-100 text-amber-800': item.estado === 'En desarrollo',
                    'bg-rose-100 text-rose-800': item.estado === 'Inactivo'
                  }"
                >
                  {{ item.estado || 'Activo' }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-700">
                <span v-if="item.emprendedor">
                  {{ item.emprendedor.nombreEmprendedor }} {{ item.emprendedor.apellido }}
                </span>
                <span v-else class="text-slate-400 italic">Sin asignar</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button 
                  @click="eliminar(item.idEmprendimiento)" 
                  class="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 transition"
                >
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="emprendimientos.data.length === 0">
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
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
          <h3 class="text-lg font-bold text-slate-800">Registrar Nuevo Emprendimiento</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Emprendedor Responsable</label>
            <select v-model="form.idEmprendedor" required class="w-full p-2 border rounded-lg text-sm bg-white">
              <option v-for="emp in emprendedores" :key="emp.idEmprendedor" :value="emp.idEmprendedor">
                {{ emp.nombreEmprendedor }} {{ emp.apellido }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nombre del Emprendimiento</label>
            <input v-model="form.nombreEmprendimiento" type="text" required class="w-full p-2 border rounded-lg text-sm" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Rubro</label>
              <input v-model="form.rubro" type="text" placeholder="Ej: Gastronomía, Textil" class="w-full p-2 border rounded-lg text-sm" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Estado</label>
              <select v-model="form.estado" class="w-full p-2 border rounded-lg text-sm bg-white">
                <option value="Activo">Activo</option>
                <option value="En desarrollo">En desarrollo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Descripción (Opcional)</label>
            <textarea v-model="form.descripcion" rows="3" class="w-full p-2 border rounded-lg text-sm"></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">
              Cancelar
            </button>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>