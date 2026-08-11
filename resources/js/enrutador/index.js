import { createRouter, createWebHistory } from 'vue-router';
import PaginaCrearCotizacion from '../paginas/PaginaCrearCotizacion.vue';
import PaginaDetalleCotizacion from '../paginas/PaginaDetalleCotizacion.vue';
import PaginaListadoCotizaciones from '../paginas/PaginaListadoCotizaciones.vue';

const enrutador = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'cotizar',
            component: PaginaCrearCotizacion,
        },
        {
            path: '/cotizaciones',
            name: 'cotizaciones',
            component: PaginaListadoCotizaciones,
        },
        {
            path: '/cotizaciones/:uuid',
            name: 'detalle-cotizacion',
            component: PaginaDetalleCotizacion,
            props: true,
        },
    ],
    scrollBehavior() {
        return { top: 0 };
    },
});

export default enrutador;
