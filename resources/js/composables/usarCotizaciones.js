import { ref } from 'vue';
import api from '../servicios/api';

/**
 * Composable para operaciones de cotizaciones.
 */
export function usarCotizaciones() {
    const cargando = ref(false);
    const error = ref('');
    const mensaje = ref('');

    async function crearCotizacion(payload) {
        cargando.value = true;
        error.value = '';
        mensaje.value = '';

        try {
            const { data } = await api.post('/cotizaciones', payload);
            mensaje.value = data.mensaje;
            return data.data;
        } catch (e) {
            if (e.response?.status === 422) {
                const errores = e.response.data.errors || {};
                error.value = Object.values(errores).flat().join(' ') || 'Datos inválidos.';
            } else {
                error.value = e.response?.data?.mensaje || 'No se pudo generar la cotización.';
            }
            throw e;
        } finally {
            cargando.value = false;
        }
    }

    async function obtenerCotizacion(uuid) {
        cargando.value = true;
        error.value = '';

        try {
            const { data } = await api.get(`/cotizaciones/${uuid}`);
            return data.data;
        } catch (e) {
            error.value = e.response?.data?.mensaje || 'No se encontró la cotización.';
            throw e;
        } finally {
            cargando.value = false;
        }
    }

    async function contratarCotizacion(uuid) {
        cargando.value = true;
        error.value = '';
        mensaje.value = '';

        try {
            const { data } = await api.post(`/cotizaciones/${uuid}/contratar`);
            mensaje.value = data.mensaje;
            return data.data;
        } catch (e) {
            error.value = e.response?.data?.mensaje || 'No se pudo contratar el seguro.';
            throw e;
        } finally {
            cargando.value = false;
        }
    }

    async function listarCotizaciones(params = {}) {
        cargando.value = true;
        error.value = '';

        try {
            const { data } = await api.get('/cotizaciones', { params });
            return {
                items: data.data ?? [],
                meta: data.meta ?? {},
            };
        } catch (e) {
            error.value = e.response?.data?.mensaje || 'No se pudo cargar el listado.';
            throw e;
        } finally {
            cargando.value = false;
        }
    }

    function urlPdf(uuid) {
        return `/api/cotizaciones/${uuid}/pdf`;
    }

    return {
        cargando,
        error,
        mensaje,
        crearCotizacion,
        obtenerCotizacion,
        contratarCotizacion,
        listarCotizaciones,
        urlPdf,
    };
}
