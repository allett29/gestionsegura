import axios from 'axios';

/**
 * Instancia HTTP para consumir la API Laravel.
 */
const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

export default api;
