<template>
    <v-container v-if="venta">
        <VentaHeader :venta="venta" />

        <VentaClientePago :venta="venta" />

        <VentaDetalleProducto v-for="(detalle, index) in venta.detalles" :key="index" :item="detalle" />


        <v-row class="mt-4 ga-1">
            <v-btn color="primary" variant="tonal" @click="imprimir">
                Imprimir
            </v-btn>
            <v-btn color="red" variant="tonal" @click="$router.push('/ventas')">
                Regresar
            </v-btn>
        </v-row>
    </v-container>
</template>

<script>
import axios from 'axios'
import VentaHeader from './VentaHeader.vue'
import VentaClientePago from './VentaClientePago.vue';
import VentaDetalleProducto from './VentaDetalleProducto.vue';
export default {
    components:{
        VentaHeader,
        VentaClientePago,
        VentaDetalleProducto,
    },
    data() {
        return {
            venta: null,
        }
    },

    async mounted() {
        const { data } = await axios.get(`/venta/${this.$route.params.id}`)
        this.venta = data
    },

    methods: {
        imprimir() {
            window.open(`/venta/${this.venta.id}/imprimir`, '_blank')
        }
    }
}
</script>
