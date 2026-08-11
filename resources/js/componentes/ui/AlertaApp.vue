<template>
    <div v-if="texto" class="alerta" :class="tipo === 'exito' ? 'alerta-exito' : 'alerta-error'">
        {{ texto }}
    </div>
</template>

<script setup>
defineProps({
    texto: { type: String, default: '' },
    tipo: { type: String, default: 'error' },
});
</script>
