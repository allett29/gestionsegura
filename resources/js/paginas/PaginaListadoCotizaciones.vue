<template>
    <section>
        <h1 class="titulo-pagina">Cotizaciones y contrataciones</h1>
        <p class="subtitulo-pagina">Consulta los seguros registrados, filtra por estado o busca por cliente.</p>

        <div class="panel">
            <form class="filtros" @submit.prevent="cargar">
                <input v-model="buscar" type="search" placeholder="Buscar por nombre, identificación o destino">
                <select v-model="estado">
                    <option value="">Todos los estados</option>
                    <option value="cotizado">Cotizado</option>
                    <option value="contratado">Contratado</option>
                </select>
                <button class="boton boton-primario" type="submit" :disabled="cargando">
                    {{ cargando ? 'Buscando...' : 'Filtrar' }}
                </button>
            </form>

            <AlertaApp :texto="error" />

            <div class="tabla-responsive">
                <table class="datos">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Identificación</th>
                            <th>Destino</th>
                            <th>Salida</th>
                            <th>Regreso</th>
                            <th>Valor</th>
                            <th>Estado</th>
                            <th>Creación</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!items.length">
                            <td colspan="9">No hay registros para mostrar.</td>
                        </tr>
                        <tr v-for="item in items" :key="item.uuid">
                            <td>{{ item.nombre_completo }}</td>
                            <td>{{ item.numero_identificacion }}</td>
                            <td>{{ item.pais_destino }}</td>
                            <td>{{ item.fecha_salida }}</td>
                            <td>{{ item.fecha_regreso }}</td>
                            <td>USD {{ Number(item.valor_total).toFixed(2) }}</td>
                            <td>
                                <span
                                    class="badge"
                                    :class="item.estado === 'contratado' ? 'badge-contratado' : 'badge-cotizado'"
                                >
                                    {{ item.estado_etiqueta }}
                                </span>
                            </td>
                            <td>{{ formatearFecha(item.fecha_creacion) }}</td>
                            <td>
                                <RouterLink :to="{ name: 'detalle-cotizacion', params: { uuid: item.uuid } }">
                                    Ver
                                </RouterLink>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="paginacion">
                <span>Página {{ meta.actual || 1 }} de {{ meta.ultima || 1 }} · {{ meta.total || 0 }} registros</span>
                <div class="acciones" style="margin-top:0;">
                    <button class="boton boton-secundario" type="button" :disabled="!puedeAnterior" @click="cambiarPagina(meta.actual - 1)">
                        Anterior
                    </button>
                    <button class="boton boton-secundario" type="button" :disabled="!puedeSiguiente" @click="cambiarPagina(meta.actual + 1)">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import AlertaApp from '../componentes/ui/AlertaApp.vue';
import { usarCotizaciones } from '../composables/usarCotizaciones';

const buscar = ref('');
const estado = ref('');
const pagina = ref(1);
const items = ref([]);
const meta = ref({});

const { cargando, error, listarCotizaciones } = usarCotizaciones();

const puedeAnterior = computed(() => (meta.value.actual || 1) > 1);
const puedeSiguiente = computed(() => (meta.value.actual || 1) < (meta.value.ultima || 1));

async function cargar() {
    try {
        const resultado = await listarCotizaciones({
            buscar: buscar.value || undefined,
            estado: estado.value || undefined,
            page: pagina.value,
            por_pagina: 10,
        });
        items.value = resultado.items;
        meta.value = resultado.meta;
    } catch {
        items.value = [];
    }
}

function cambiarPagina(nuevaPagina) {
    pagina.value = nuevaPagina;
    cargar();
}

function formatearFecha(valor) {
    if (!valor) return '-';
    return new Date(valor).toLocaleString('es-EC');
}

onMounted(cargar);
</script>
