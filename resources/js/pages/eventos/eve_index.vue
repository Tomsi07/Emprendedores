<script setup>
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  eventos: {
    type: Object,
    required: true
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

const form = useForm({
  nombreEvento: '',
  lugar: '',
  fecha: ''
})

const buscar = () => {
  router.get('/eventos', { search: search.value }, { 
    preserveState: true, 
    replace: true 
  })
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
  form.lugar = item.lugar || ''
  form.fecha = item.fecha || ''
  
  showModal.value = true
}

const submit = () => {
  if (isEditing.value) {
    form.put(`/eventos/${editingId.value}`, {
      onSuccess: () => {
        form.reset()
        showModal.value = false
      }
    })
  } else {
    form.post('/eventos', {
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
</script>

<template>
  <div class="flex min-h-screen bg-slate-100">
    <SidebarNav />

    <main class="flex-1 ml-64 p-6 space-y-6">
      
      <!-- Encabezado -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-xl shadow-sm border border-slate-200">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">Gestión de Eventos</h1>
          <p class="text-slate-500 text-sm">Listado general de ferias, muestras y encuentros programados.</p>
        </div>
        <button 
          @click="abrirCrear"
          class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition flex items-center justify-center gap-2"
        >
          <span>+</span> Nuevo Evento
        </button>
      </div>

      <!-- Buscador -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <input
          v-model="search"
          @input="buscar"
          type="text"
          placeholder="Buscar por nombre de evento o lugar..."
          class="w-full px-4 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <!-- Tabla de Datos -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-semibold text-xs">
            <tr>
              <th class="px-6 py-4">Evento</th>
              <th class="px-6 py-4">Lugar</th>
              <th class="px-6 py-4">Fecha</th>
              <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="item in eventos.data" :key="item.idEvento || item.id" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-bold text-slate-900">
                {{ item.nombreEvento }}
              </td>
              <td class="px-6 py-4 text-slate-700">
                {{ item.lugar || '-' }}
              </td>
              <td class="px-6 py-4 text-slate-700">
                {{ item.fecha || '-' }}
              </td>
              <td class="px-6 py-4 text-right space-x-2">
                <button 
                  @click="abrirEditar(item)" 
                  class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold px-2 py-1 rounded hover:bg-indigo-50 transition"
                >
                  Editar
                </button>
                <button 
                  @click="eliminar(item.idEvento || item.id)" 
                  class="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 transition"
                >
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!eventos.data || eventos.data.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                No se encontraron eventos registrados.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
          <span class="text-slate-500 text-xs sm:text-sm">
            Mostrando {{ eventos.from || 0 }} a {{ eventos.to || 0 }} de {{ eventos.total || 0 }} resultados
          </span>
          
          <div class="flex flex-wrap gap-1">
            <Component
              :is="link.url ? Link : 'span'"
              v-for="(link, index) in eventos.links"
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
            {{ isEditing ? 'Editar Evento' : 'Registrar Nuevo Evento' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nombre del Evento</label>
            <input 
              type="text" 
              v-model="form.nombreEvento" 
              placeholder="Ej: Feria de Emprendedores Locales" 
              required 
              class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Lugar</label>
            <input 
              type="text" 
              v-model="form.lugar" 
              placeholder="Ej: Plaza Principal" 
              required 
              class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Fecha</label>
            <input 
              type="date" 
              v-model="form.fecha" 
              required 
              class="w-full p-2 border rounded-lg text-sm bg-white outline-none focus:ring-2 focus:ring-indigo-500"
            />
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