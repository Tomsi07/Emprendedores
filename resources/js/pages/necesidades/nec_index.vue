<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
  necesidades: Object,
  filters: Object
});

const search = ref(props.filters.search || '');

watch(search, (value) => {
  router.get('/necesidades', { search: value }, { preserveState: true, replace: true });
});

const destroy = (id) => {
  if (confirm('¿Estás seguro de eliminar esta necesidad?')) {
    router.delete(`/necesidades/${id}`);
  }
};
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Gestión de Necesidades</h1>

    <div class="mb-4">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar necesidad..."
        class="border rounded-md px-4 py-2 w-full max-w-sm"
      />
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Necesidad</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Emprendimiento</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Estado</th>
            <th class="p-4 text-xs font-medium text-gray-500 uppercase">Acciones</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="necesidad in necesidades.data" :key="necesidad.idNecesidad">
            <td class="p-4">
              <div class="font-bold">{{ necesidad.titulo }}</div>
              <div class="text-sm text-gray-500">{{ necesidad.descripcion }}</div>
            </td>
            <td class="p-4">
              {{ necesidad.emprendimiento ? necesidad.emprendimiento.nombreEmprendimiento : '-' }}
            </td>
            <td class="p-4">
              <span class="px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">
                {{ necesidad.estado }}
              </span>
            </td>
            <td class="p-4">
              <button @click="destroy(necesidad.idNecesidad)" class="text-red-600 font-medium">
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <div class="p-4 flex items-center justify-between border-t">
        <p class="text-sm text-gray-600">
          Mostrando {{ necesidades.from }} a {{ necesidades.to }} de {{ necesidades.total }} resultados
        </p>
        <div class="flex gap-1">
          <Component
            :is="link.url ? Link : 'span'"
            v-for="(link, k) in necesidades.links"
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