<template>
    <section>
        <h1 class="titulo-pagina">Cotiza tu seguro de viaje</h1>
        <p class="subtitulo-pagina">
            Ingresa tus datos, selecciona el destino y obtén el valor del seguro al instante.
        </p>

        <AlertaApp :texto="errorPaises" />
        <AlertaApp v-if="cargandoPaises" texto="Cargando países..." tipo="exito" />

        <FormularioCotizacion
            :paises="paises"
            :cargando="cargando"
            :error-general="error"
            @enviar="alEnviar"
        />
    </section>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import FormularioCotizacion from '../componentes/FormularioCotizacion.vue';
import AlertaApp from '../componentes/ui/AlertaApp.vue';
import { usarPaises } from '../composables/usarPaises';
import { usarCotizaciones } from '../composables/usarCotizaciones';

const enrutador = useRouter();
const { paises, cargando: cargandoPaises, error: errorPaises, cargarPaises } = usarPaises();
const { cargando, error, crearCotizacion } = usarCotizaciones();

onMounted(() => {
    cargarPaises();
});

async function alEnviar(payload) {
    try {
        const cotizacion = await crearCotizacion(payload);
        enrutador.push({ name: 'detalle-cotizacion', params: { uuid: cotizacion.uuid } });
    } catch {
        // El error ya se expone en el composable.
    }
}
</script>
