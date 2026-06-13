import './bootstrap'
import { createApp } from 'vue'
import RouterWeb from './router/index'
import App from './components/App.vue'
import './sidebar'

import axios from 'axios'
window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')

if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
}

// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import '@fortawesome/fontawesome-free/css/all.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import Toast from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import 'filepond/dist/filepond.min.css'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css'
import '../../public/css/global.css'


axios.interceptors.response.use(
    response => response,
    error => {
        const status = error.response?.status

        if (status === 419 || status === 401) {
            if (!window.IS_LOGGING_OUT && !window.SESSION_REDIRECTING) {
                window.SESSION_REDIRECTING = true
                alert('Tu sesión ha expirado')
            }

            window.location.replace('/login')
        }

        return Promise.reject(error)
    }
)


const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                dark: false,
                colors: {
                    primary: '#00432C',
                    secondary: '#019EB1',   // Turquesa
                    accent: '#46BD01',      // Verde brillante
                    success: '#6FB408',
                    info: '#019EB1',
                    warning: '#7DBA19',
                    error: '#B00020'
                }
            }
        }
    }
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
