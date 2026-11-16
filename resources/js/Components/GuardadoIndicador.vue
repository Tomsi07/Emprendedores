<script setup>
defineProps({
  // 'idle' | 'guardando' | 'guardado' | 'error'
  estado: { type: String, default: 'idle' }
})
</script>

<template>
  <span
    class="indicador"
    role="status"
    aria-live="polite"
    :title="estado === 'error' ? 'No se pudo guardar. Probá de nuevo.' : ''"
  >
    <span v-if="estado === 'guardando'" class="spin"></span>

    <svg v-else-if="estado === 'guardado'" viewBox="0 0 20 20" class="icono ok">
      <path
        d="M4 10.5l4 4 8-9"
        fill="none"
        stroke="currentColor"
        stroke-width="2.4"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>

    <svg v-else-if="estado === 'error'" viewBox="0 0 20 20" class="icono error">
      <circle cx="10" cy="10" r="8.5" fill="currentColor" />
      <path d="M10 5.5v5.5M10 13.8v.4" stroke="#fff" stroke-width="2" stroke-linecap="round" />
    </svg>
  </span>
</template>

<style scoped>
/* Lugar fijo: ocupa siempre el mismo espacio, aunque no se vea nada */
.indicador {
  display: inline-block;
  width: 16px;
  height: 16px;
  vertical-align: middle;
  flex-shrink: 0;
}
.spin {
  display: block;
  width: 14px;
  height: 14px;
  margin: 1px;
  border: 2px solid #cfe3de;
  border-top-color: #1d4b44;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}
.icono {
  width: 16px;
  height: 16px;
  display: block;
}
.ok {
  color: #1d4b44;
}
.error {
  color: #b91c1c;
}
@keyframes girar {
  to {
    transform: rotate(360deg);
  }
}
</style>