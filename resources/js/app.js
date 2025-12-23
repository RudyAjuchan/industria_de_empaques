import './bootstrap';

import { createApp } from 'vue';
import RouterWeb from './router/index';
import App from './components/App.vue'
import './sidebar';

/* CONFIGURACIÓN AXIOS */
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('ERROR AL OBTENER TOKEN!');
}
/* FIN CONFIGURACIÓN AXIOS */

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify = createVuetify({
    components,
    directives
})


createApp(App)
    .use(RouterWeb)
    .use(vuetify)
    .mount('#app')