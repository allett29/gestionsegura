<template>
    <section>
        <h1 class="titulo-pagina">Detalle de cotización</h1>
        <p class="subtitulo-pagina">Revisa el cálculo, descarga el PDF o confirma la contratación.</p>

        <AlertaApp :texto="error" />
        <AlertaApp :texto="mensaje" tipo="exito" />

        <div v-if="cotizacion" class="panel">
            <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem;">
                <div>
                    <strong>{{ cotizacion.nombre_completo }}</strong>
                    <div style="color:var(--color-suave);">{{ cotizacion.numero_identificacion }}</div>
                </div>
                <span
                    class="badge"
                    :class="cotizacion.estado === 'contratado' ? 'badge-contratado' : 'badge-cotizado'"
                >
                    {{ cotizacion.estado_etiqueta }}
                </span>
            </div>

            <dl class="resumen-grid">
                <div>
                    <dt>Destino</dt>
                    <dd>{{ cotizacion.pais_destino }} ({{ cotizacion.region_destino }})</dd>
                </div>
                <div>
                    <dt>Fechas</dt>
                    <dd>{{ cotizacion.fecha_salida }} → {{ cotizacion.fecha_regreso }}</dd>
                </div>
                <div>
                    <dt>Días</dt>
                    <dd>{{ cotizacion.cantidad_dias }}</dd>
                </div>
                <div>
                    <dt>Tarifa base</dt>
                    <dd>USD {{ formatear(cotizacion.tarifa_base) }}</dd>
                </div>
                <div>
                    <dt>Recargo</dt>
                    <dd>{{ formatear(cotizacion.porcentaje_recargo) }}%</dd>
                </div>
                <div>
                    <dt>Total</dt>
                    <dd>USD {{ formatear(cotizacion.valor_total) }}</dd>
                </div>
            </dl>

            <div class="acciones">
                <a class="boton boton-secundario" :href="urlPdf(uuid)" target="_blank" rel="noopener">
                    Descargar PDF
                </a>
                <button
                    v-if="cotizacion.puede_contratar"
                    class="boton boton-acento"
                    type="button"
                    :disabled="cargando"
                    @click="contratar"
                >
                    {{ cargando ? 'Contratando...' : 'Contratar seguro' }}
                </button>
                <RouterLink class="boton boton-primario" :to="{ name: 'cotizaciones' }">
                    Ver listado
                </RouterLink>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AlertaApp from '../componentes/ui/AlertaApp.vue';
import { usarCotizaciones } from '../composables/usarCotizaciones';

const props = defineProps({
    uuid: { type: String, required: true },
});

const cotizacion = ref(null);
const { cargando, error, mensaje, obtenerCotizacion, contratarCotizacion, urlPdf } = usarCotizaciones();

onMounted(async () => {
    try {
        cotizacion.value = await obtenerCotizacion(props.uuid);
    } catch {
        // Error expuesto.
    }
});

async function contratar() {
    try {
        cotizacion.value = await contratarCotizacion(props.uuid);
    } catch {
        // Error expuesto.
    }
}

function formatear(valor) {
    return Number(valor).toFixed(2);
}
</script>
