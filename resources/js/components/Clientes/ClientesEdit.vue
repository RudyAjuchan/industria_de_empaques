<template>
    <v-container>
        <h2 class="mb-4">Editar cliente</h2>

        <ClienteForm :cliente="cliente" @saved="onSaved" @cancel="$router.push('/clientes')" />
    </v-container>
</template>

<script>
import ClienteForm from './ClientesForm.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'cliente.edit',

    components: { ClienteForm },

    data() {
        return {
            cliente: null,
        }
    },

    async mounted() {
        const { id } = this.$route.params
        const { data } = await axios.get(`/cliente/${id}`)
        this.cliente = data
    },

    methods: {
        onSaved() {
            this.$router.push({
                path: '/clientes',
                query: {
                    toast: 'updated'
                }
            })
        },
    },
}
</script>
