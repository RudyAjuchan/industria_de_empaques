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
        try {
            const { data } = await axios.get(`/cliente/${id}`)
            this.cliente = data
        } catch (err) {
            toast.error('No se pudo cargar el cliente')
            this.$router.push('/clientes')
        }
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
