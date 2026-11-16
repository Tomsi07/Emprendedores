<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import SidebarNav from '@/Components/SidebarNav.vue'

const props = defineProps({
  necesidades: {
    type: Array,
    default: () => []
  }
})

// ---------- Filtros y paginación (todo en el navegador) ----------
const filtros = ref({ descripcion: '', emprendimiento: '' })
const porPagina = 15
const pagina = ref(1)

// Ignora mayúsculas y tildes ("financiacion" encuentra "Financiación")
const normalizar = (texto) =>
  (texto ?? '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()

const filtrados = computed(() => {
  const descripcion = normalizar(filtros.value.descripcion)
  const emprendimiento = normalizar(filtros.value.emprendimiento)

  return props.necesidades.filter((item) => {
    if (descripcion && !normalizar(item.descripcion).includes(descripcion)) return false
    if (emprendimiento && !normalizar(item.nombreEmprendimiento).includes(emprendimiento)) return false
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
  filtros.value = { descripcion: '', emprendimiento: '' }
}

// ---------- Edición ----------
const showModal = ref(false)
const editingId = ref(null)

const editForm = useForm({
  descripcion: ''
})

const abrirEditar = (item) => {
  editingId.value = item.idNecesidad || item.id
  editForm.clearErrors()
  editForm.descripcion = item.descripcion || ''
  showModal.value = true
}

const guardarEdicion = () => {
  editForm.put(`/necesidades/${editingId.value}`, {
    preserveScroll: true,
    onSuccess: () => {
      showModal.value = false
    }
  })
}

const destroy = (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar esta necesidad?')) {
    router.delete(`/necesidades/${id}`, {
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
        <div class="border-l-4 border-[#1d4b44] pl-4">
          <h1 class="text-3xl font-medium text-[#0e1729]">NECESIDADES</h1>
          <p class="text-sm text-slate-500 mt-1">
            Listado general de necesidades registradas para los emprendimientos.
          </p>
        </div>
      </header>

      <!-- Buscadores -->
      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[1.4fr_1.4fr_auto] gap-x-4 gap-y-4 items-end">
        <label class="block space-y-1">
          <span :class="etiqueta">Necesidad</span>
          <input v-model="filtros.descripcion" type="search" placeholder="Buscar por descripción" :class="inputCaja" />
        </label>

        <label class="block space-y-1">
          <span :class="etiqueta">Emprendimiento</span>
          <input v-model="filtros.emprendimiento" type="search" placeholder="Buscar por nombre del emprendimiento" :class="inputCaja" />
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
                <th :class="th">Necesidad</th>
                <th :class="th">Emprendimiento</th>
                <th :class="[th, 'text-right']">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in visibles"
                :key="item.idNecesidad || item.id"
                class="border-b border-slate-200 hover:bg-slate-50 transition-colors"
              >
                <td :class="[td, 'font-semibold']">{{ item.descripcion }}</td>
                <td :class="[td, 'text-slate-600']">{{ item.nombreEmprendimiento || 'N/A' }}</td>
                <td :class="[td, 'text-right space-x-2']">
                  <button
                    @click="abrirEditar(item)"
                    class="rounded-md border border-[#1d4b44] px-3 py-1.5 text-xs font-medium text-[#1d4b44] hover:bg-[#1d4b44] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#2b9a82] transition-colors"
                  >
                    Editar
                  </button>
                  <button
                    @click="destroy(item.idNecesidad || item.id)"
                    class="rounded-md border border-rose-300 px-3 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300 transition-colors"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
              <tr v-if="visibles.length === 0">
                <td colspan="3" class="py-10 text-center text-slate-400">
                  No hay necesidades que coincidan con los filtros.
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

    <!-- Modal Editar Necesidad -->
    <div v-if="showModal" class="fixed inset-0 bg-[#0e1729]/70 flex items-center justify-center p-4 z-50">
      <div class="bg-white max-w-md w-full p-8 space-y-6 border-l-4 border-[#1d4b44]">
        <div class="flex justify-between items-start">
          <h3 class="text-2xl font-medium text-[#0e1729]">Editar necesidad</h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 transition-colors" aria-label="Cerrar">✕</button>
        </div>

        <form @submit.prevent="guardarEdicion" class="space-y-5">
          <label class="block">
            <span :class="etiqueta">Descripción de la necesidad</span>
            <input v-model="editForm.descripcion" type="text" required :class="inputLinea" />
            <p v-if="editForm.errors.descripcion" class="mt-1 text-xs text-rose-600">{{ editForm.errors.descripcion }}</p>
          </label>

          <div class="flex justify-end items-center gap-4 pt-4">
            <button type="button" @click="showModal = false" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="editForm.processing"
              class="rounded-md bg-[#1d4b44] px-6 py-2.5 text-sm font-medium text-white hover:bg-[#163a35] disabled:opacity-50 transition-colors"
            >
              Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>