<template>
    <ProductosForm v-if="producto" :producto="producto" @saved="onSaved" @cancel="$router.push('/productos')" />
</template>

<script>
import axios from 'axios'
import ProductosForm from './ProductosForm.vue'

export default {
    name: 'producto.edit',

    components: { ProductosForm },

    data() {
        return {
            producto: null
        }
    },

    async mounted() {
        const { data } = await axios.get(`/producto/${this.$route.params.id}`)
        this.producto = data
    },

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
