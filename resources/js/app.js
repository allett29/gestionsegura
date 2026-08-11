import '../css/app.css';
import './bootstrap';
import { createApp } from 'vue';
import Aplicacion from './Aplicacion.vue';
import enrutador from './enrutador';

createApp(Aplicacion).use(enrutador).mount('#aplicacion');
