<template>
    <v-container>
        <h2 class="mb-4">Editar producto</h2>
    <ProductosForm v-if="producto" :producto="producto" @saved="onSaved" @cancel="$router.push('/productos')" />
    </v-container>
</template>

<script>
import axios from 'axios'
import ProductosForm from './ProductosForm.vue'
import { toast } from 'vue3-toastify'

export default {
    name: 'producto.edit',

    components: { ProductosForm },

    data() {
        return {
            producto: null
        }
    },

    async mounted() {
        try {
            const { data } = await axios.get(`/producto/${this.$route.params.id}`)
            this.producto = data
        } catch (err) {
            toast.error('No se pudo cargar el producto')
            this.$router.push('/productos')
        }
    },

    methods: {
        onSaved() {
            this.$router.push({
                path: '/productos',
                query: { toast: 'updated' }
            })
        }
    }
}
</script>
