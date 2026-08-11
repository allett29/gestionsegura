<template>
    <div class="campo" :class="{ completo }">
        <label :for="id">{{ etiqueta }}</label>
        <input
            :id="id"
            :type="tipo"
            :value="modelValue"
            :min="min"
            :max="max"
            :required="requerido"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', $event.target.value)"
        >
        <span v-if="error" class="error">{{ error }}</span>
    </div>
</template>

<script setup>
defineProps({
    id: { type: String, required: true },
    etiqueta: { type: String, required: true },
    modelValue: { type: [String, Number], default: '' },
    tipo: { type: String, default: 'text' },
    error: { type: String, default: '' },
    min: { type: String, default: undefined },
    max: { type: String, default: undefined },
    requerido: { type: Boolean, default: false },
    placeholder: { type: String, default: '' },
    completo: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>
