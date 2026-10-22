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

const form = useForm({
  idEmprendedor: props.emprendedores[0]?.idEmprendedor || '',
  nombreEmprendimiento: '',
  rubro: '',
  estado: 'Activo',
  descripcion: ''
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

// Clases reutilizables con la estética del login
const inputLinea =
  'w-full bg-transparent border-0 border-b border-slate-300 px-0 py-2 text-sm text-[#0e1729] placeholder-slate-400 focus:border-[#1d4b44] focus:ring-0 outline-none transition-colors'
const etiqueta = 'block text-xs text-slate-500'
</script>

<template>
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
            <tr
              v-for="item in emprendimientos.data"
              :key="item.idEmprendimiento"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="py-4 pr-6 font-semibold text-[#0e1729]">
                {{ item.nombreEmprendimiento }}
              </td>
              <td class="py-4 pr-6 text-slate-600">
                {{ item.rubro || '-' }}
              </td>
              <td class="py-4 pr-6">
                <span
                  class="inline-flex items-center gap-2 text-xs font-medium"
                  :class="{
                    'text-[#1d4b44]': item.estado === 'Activo' || !item.estado,
                    'text-amber-700': item.estado === 'En desarrollo',
                    'text-slate-500': item.estado === 'Inactivo'
                  }"
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
                  {{ item.emprendedor.nombreEmprendedor }} {{ item.emprendedor.apellido }}
                </span>
                <span v-else class="text-slate-400 italic">Sin asignar</span>
              </td>
              <td class="py-4 text-right">
                <button
                  @click="eliminar(item.idEmprendimiento)"
                  class="text-xs font-medium text-rose-700 hover:text-rose-900 hover:underline underline-offset-4 transition-colors"
                >
                  Eliminar
                </button>
              </td>
            </tr>
            <tr v-if="emprendimientos.data.length === 0">
              <td colspan="5" class="py-10 text-center text-slate-400">
                No hay emprendimientos que coincidan con los filtros.
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

          <label class="block">
            <span :class="etiqueta">Nombre del emprendimiento</span>
            <input v-model="form.nombreEmprendimiento" type="text" required :class="inputLinea" />
          </label>

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
              </select>
            </label>
          </div>

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
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>