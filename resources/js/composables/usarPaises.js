import { ref } from 'vue';
import api from '../servicios/api';

/**
 * Composable para cargar países desde la API.
 */
export function usarPaises() {
    const paises = ref([]);
    const cargando = ref(false);
    const error = ref('');

    async function cargarPaises() {
        cargando.value = true;
        error.value = '';

        try {
            const { data } = await api.get('/paises');
            paises.value = data.data ?? [];

            if (paises.value.length === 0) {
                error.value = data.mensaje || 'No se pudieron cargar los países.';
            }
        } catch (e) {
            error.value = e.response?.data?.mensaje || 'No se pudieron cargar los países.';
            paises.value = [];
        } finally {
            cargando.value = false;
        }
    }

    return {
        paises,
        cargando,
        error,
        cargarPaises,
    };
}
