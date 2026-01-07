<template>
    <v-container>
        <h2 class="mb-4">Nuevo cliente</h2>

        <ClienteForm @saved="onSaved" @cancel="$router.push('/clientes')" />
    </v-container>
</template>

<script>
import ClienteForm from './ClientesForm.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'cliente.create',

    components: { ClienteForm },

    methods: {
        onSaved() {
            this.$router.push({
                path: '/clientes',
                query: {
                    toast: 'saved'
                }
            })
        },
    },
}
</script>
