<template>
    <form class="panel" @submit.prevent="enviar">
        <AlertaApp :texto="errorGeneral" />

        <h2 style="margin-top:0;">Datos del asegurado</h2>
        <div class="rejilla-formulario">
            <CampoEntrada
                id="nombres"
                v-model="formulario.nombres"
                etiqueta="Nombres"
                :error="errores.nombres"
                requerido
            />
            <CampoEntrada
                id="apellidos"
                v-model="formulario.apellidos"
                etiqueta="Apellidos"
                :error="errores.apellidos"
                requerido
            />
            <CampoEntrada
                id="numero_identificacion"
                v-model="formulario.numero_identificacion"
                etiqueta="Número de identificación"
                :error="errores.numero_identificacion"
                requerido
            />
            <CampoEntrada
                id="correo_electronico"
                v-model="formulario.correo_electronico"
                etiqueta="Correo electrónico"
                tipo="email"
                :error="errores.correo_electronico"
                requerido
            />
            <CampoEntrada
                id="fecha_nacimiento"
                v-model="formulario.fecha_nacimiento"
                etiqueta="Fecha de nacimiento"
                tipo="date"
                :max="fechaMaximaNacimiento"
                :error="errores.fecha_nacimiento"
                requerido
            />
        </div>

        <h2>Datos del viaje</h2>
        <div class="rejilla-formulario">
            <div class="campo completo">
                <label for="codigo_iso_destino">País de destino</label>
                <select
                    id="codigo_iso_destino"
                    v-model="formulario.codigo_iso_destino"
                    required
                >
                    <option value="">Seleccione un país</option>
                    <option
                        v-for="pais in paises"
                        :key="pais.codigo_iso"
                        :value="pais.codigo_iso"
                    >
                        {{ pais.nombre }} ({{ pais.region }})
                    </option>
                </select>
                <span v-if="errores.codigo_iso_destino" class="error">{{ errores.codigo_iso_destino }}</span>
            </div>

            <CampoEntrada
                id="fecha_salida"
                v-model="formulario.fecha_salida"
                etiqueta="Fecha de salida"
                tipo="date"
                :min="hoy"
                :error="errores.fecha_salida"
                requerido
            />
            <CampoEntrada
                id="fecha_regreso"
                v-model="formulario.fecha_regreso"
                etiqueta="Fecha de regreso"
                tipo="date"
                :min="formulario.fecha_salida || hoy"
                :error="errores.fecha_regreso"
                requerido
            />
        </div>

        <div class="acciones">
            <button class="boton boton-primario" type="submit" :disabled="cargando">
                {{ cargando ? 'Calculando...' : 'Obtener cotización' }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { reactive, computed } from 'vue';
import CampoEntrada from './ui/CampoEntrada.vue';
import AlertaApp from './ui/AlertaApp.vue';

const props = defineProps({
    paises: { type: Array, default: () => [] },
    cargando: { type: Boolean, default: false },
    errorGeneral: { type: String, default: '' },
});

const emitir = defineEmits(['enviar']);

const formulario = reactive({
    nombres: '',
    apellidos: '',
    numero_identificacion: '',
    correo_electronico: '',
    fecha_nacimiento: '',
    codigo_iso_destino: '',
    fecha_salida: '',
    fecha_regreso: '',
});

const errores = reactive({
    nombres: '',
    apellidos: '',
    numero_identificacion: '',
    correo_electronico: '',
    fecha_nacimiento: '',
    codigo_iso_destino: '',
    fecha_salida: '',
    fecha_regreso: '',
});

const hoy = new Date().toISOString().slice(0, 10);
const fechaMaximaNacimiento = computed(() => {
    const fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() - 18);
    return fecha.toISOString().slice(0, 10);
});

function limpiarErrores() {
    Object.keys(errores).forEach((clave) => {
        errores[clave] = '';
    });
}

function validar() {
    limpiarErrores();
    let valido = true;

    if (!formulario.nombres.trim()) {
        errores.nombres = 'Ingrese los nombres.';
        valido = false;
    }
    if (!formulario.apellidos.trim()) {
        errores.apellidos = 'Ingrese los apellidos.';
        valido = false;
    }
    if (!formulario.numero_identificacion.trim()) {
        errores.numero_identificacion = 'Ingrese la identificación.';
        valido = false;
    } else if (!/^[A-Za-z0-9\-]+$/.test(formulario.numero_identificacion)) {
        errores.numero_identificacion = 'Solo letras, números y guiones.';
        valido = false;
    }
    if (!formulario.correo_electronico.trim()) {
        errores.correo_electronico = 'Ingrese el correo.';
        valido = false;
    }
    if (!formulario.fecha_nacimiento) {
        errores.fecha_nacimiento = 'Ingrese la fecha de nacimiento.';
        valido = false;
    } else if (formulario.fecha_nacimiento > fechaMaximaNacimiento.value) {
        errores.fecha_nacimiento = 'Debe ser mayor de 18 años.';
        valido = false;
    }
    if (!formulario.codigo_iso_destino) {
        errores.codigo_iso_destino = 'Seleccione un país.';
        valido = false;
    }
    if (!formulario.fecha_salida) {
        errores.fecha_salida = 'Ingrese la fecha de salida.';
        valido = false;
    } else if (formulario.fecha_salida < hoy) {
        errores.fecha_salida = 'La salida no puede ser anterior a hoy.';
        valido = false;
    }
    if (!formulario.fecha_regreso) {
        errores.fecha_regreso = 'Ingrese la fecha de regreso.';
        valido = false;
    } else if (formulario.fecha_regreso < formulario.fecha_salida) {
        errores.fecha_regreso = 'El regreso debe ser igual o posterior a la salida.';
        valido = false;
    }

    return valido;
}

function enviar() {
    if (!validar()) {
        return;
    }

    emitir('enviar', { ...formulario });
}
</script>
