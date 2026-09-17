<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  actividades: Object,
  filters: Object
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
  router.get('/actividades', { search: value }, { preserveState: true, replace: true });
});

const destroy = (id) => {
  if (confirm('¿Estás seguro de eliminar esta actividad?')) {
    router.delete(`/actividades/${id}`);
  }
};
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Gestión de Actividades</h1>

    <div class="mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar actividad..."
        class="border rounded-md px-4 py-2 w-full max-w-sm"
      />
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Actividad</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Emprendimiento</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Estado</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="actividad in actividades.data" :key="actividad.idActividad">
            <td class="p-4">
              <div class="font-bold">{{ actividad.nombreActividad }}</div>
              <div class="text-sm text-gray-500">{{ actividad.descripcion }}</div>
            </td>
            <td class="p-4">
              {{ actividad.emprendimiento ? actividad.emprendimiento.nombreEmprendimiento : '-' }}
            </td>
            <td class="p-4">
              <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                {{ actividad.estado }}
              </span>
            </td>
            <td class="p-4">
              <button @click="destroy(actividad.idActividad)" class="text-red-600 font-medium">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <div class="p-4 flex items-center justify-between border-t">
        <p class="text-sm text-gray-600">
          Mostrando {{ actividades.from }} a {{ actividades.to }} de {{ actividades.total }} resultados
        </p>
        <div class="flex gap-1">
          <Component
            :is="link.url ? Link : 'span'"
            v-for="(link, k) in actividades.links"
            :key="k"
            :href="link.url"
            v-html="link.label"
            class="px-3 py-1 border rounded text-sm"
            :class="{
              'bg-indigo-600 text-white': link.active,
              'text-gray-400': !link.url
            }"
          />
        </div>
      </div>
    </div>
  </div>
</template>