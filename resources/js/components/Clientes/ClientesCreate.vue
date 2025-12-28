<template>
    <v-container>
        <h2 class="mb-4">Nuevo cliente</h2>

        <ClienteForm @saved="onSaved" @cancel="$router.push('/clientes')" />
    </v-container>
</template>

<script>
import ClienteForm from './ClientesForm.vue'

export default {
    name: 'cliente.create',

    components: { ClienteForm },

    methods: {
        onSaved() {
            this.$router.push('/clientes')
        },
    },
}
</script>
