<template>
    <div>
        <header class="envoltorio barra-superior">
            <div class="marca">Gestión Segura · Seguro de Viaje</div>
            <nav class="navegacion">
                <RouterLink :to="{ name: 'cotizar' }">Cotizar</RouterLink>
                <RouterLink :to="{ name: 'cotizaciones' }">Contrataciones</RouterLink>
            </nav>
        </header>

        <main class="envoltorio" style="padding-bottom: 3rem;">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router';
</script>
