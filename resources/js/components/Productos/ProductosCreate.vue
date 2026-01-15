<template>
    <v-container>
        <h2 class="mb-4">Nuevo producto</h2>

        <ProductosForm @saved="onSaved" @cancel="$router.push('/productos')" />
    </v-container>
</template>

<script>
import ProductosForm from './ProductosForm.vue'

export default {
    name: 'producto.create',
    components: { ProductosForm },

    methods: {
        onSaved() {
            this.$router.push({
                path: '/productos',
                query: { toast: 'saved' }
            })
        }
    }
}
</script>
