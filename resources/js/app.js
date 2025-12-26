import './bootstrap'
import { createApp } from 'vue'
import RouterWeb from './router/index'
import App from './components/App.vue'
import './sidebar'

import axios from 'axios'
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import Toast from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

const vuetify = createVuetify({
    components,
    directives
})

const app = createApp(App)

app.config.globalProperties.can = (permission) => {
    return window.USER_PERMISSIONS?.includes(permission)
}

app
    .use(RouterWeb)
    .use(vuetify)
    .use(Toast, {
        autoClose: 3000,
        position: 'top-right',
        theme: 'auto',
        hideProgressBar: false,
        closeOnClick: true,
    })
    .mount('#app')