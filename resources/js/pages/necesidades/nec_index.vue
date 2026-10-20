<script setup>
import { ref, watch } from 'vue'
import { router, Link, useForm } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  necesidades: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const showModal = ref(false)
const editingId = ref(null)

const editForm = useForm({
  titulo: '',
})

let searchTimeout = null
watch(search, (value) => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    router.get(
      window.location.pathname,
      { search: value },
      { preserveState: true, replace: true }
    )
  }, 300)
})

const abrirEditar = (item) => {
  editingId.value = item.idNecesidad || item.id
  editForm.titulo = item.titulo || ''
  showModal.value = true
}

const guardarEdicion = () => {
  editForm.put(`/necesidades/${editingId.value}`, {
    onSuccess: () => {
      showModal.value = false
    },
  })
}

const destroy = (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta necesidad?')) {
    router.delete(`/necesidades/${id}`, {
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
          <h1 class="text-2xl font-bold text-slate-800">Gestión de Necesidades</h1>
          <p class="text-slate-500 text-sm">Listado general de necesidades registradas para los emprendimientos.</p>
        </div>
      </div>

      <!-- Buscador -->
      <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar necesidad..."
          class="w-full px-4 py-2 rounded-lg border border-slate-300 text-sm outline-none focus:ring-2 focus:ring-indigo-500"
        />
      </div>

      <!-- Tabla de Datos -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 uppercase font-semibold text-xs">
            <tr>
              <th class="px-6 py-4">Necesidad</th>
              <th class="px-6 py-4">Emprendimiento</th>
              <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr
              v-for="item in (necesidades.data || necesidades)"
              :key="item.idNecesidad || item.id"
              class="hover:bg-slate-50 transition"
            >
              <!-- NECESIDAD -->
              <td class="px-6 py-4 font-bold text-slate-900">
                {{ 
                  item.titulo?.toLowerCase().includes('requerimientos iniciales')
                    ? (item.descripcion || item.detalle || item.tipo || item.titulo)
                    : item.titulo 
                }}
              </td>

              <!-- EMPRENDIMIENTO -->
              <td class="px-6 py-4 text-slate-700 font-medium">
                {{ item.emprendimiento?.nombreEmprendimiento || 'N/A' }}
              </td>

              <!-- ACCIONES -->
              <td class="px-6 py-4 text-right space-x-2">
                <button
                  @click="abrirEditar(item)"
                  class="text-indigo-600 hover:text-indigo-800 text-xs font-semibold px-2 py-1 rounded hover:bg-indigo-50 transition"
                >
                  Editar
                </button>
                <button
                  @click="destroy(item.idNecesidad || item.id)"
                  class="text-rose-600 hover:text-rose-800 text-xs font-semibold px-2 py-1 rounded hover:bg-rose-50 transition"
                >
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="!(necesidades.data || necesidades)?.length">
              <td colspan="3" class="px-6 py-8 text-center text-slate-400">
                No se encontraron necesidades registradas.
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
          <span class="text-slate-500 text-xs sm:text-sm">
            Mostrando {{ necesidades.from || 0 }} a {{ necesidades.to || 0 }} de {{ necesidades.total || 0 }} resultados
          </span>
          
          <div class="flex flex-wrap gap-1">
            <Component
              :is="link.url ? Link : 'span'"
              v-for="(link, index) in necesidades.links"
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

    <!-- Modal Editar Necesidad -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 space-y-4">
        <div class="flex justify-between items-center border-b pb-3">
          <h3 class="text-lg font-bold text-slate-800">Editar Necesidad</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form @submit.prevent="guardarEdicion" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">
              Título de la Necesidad
            </label>
            <input 
              v-model="editForm.titulo" 
              type="text" 
              required 
              class="w-full p-2 border rounded-lg text-sm outline-none focus:ring-2 focus:ring-indigo-500" 
            />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button 
              type="button" 
              @click="showModal = false" 
              class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg transition"
            >
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="editForm.processing" 
              class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50 transition"
            >
              Guardar Cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>