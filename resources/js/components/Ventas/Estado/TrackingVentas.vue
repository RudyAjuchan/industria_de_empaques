<template>
    <v-container>
        <v-btn icon variant="text" @click="$router.back()">
            <v-icon>mdi-arrow-left</v-icon>
        </v-btn>

        <h2 class="mb-4">Tracking de la venta <v-chip color="">{{ venta.numero_completo }}</v-chip></h2>

        <v-row v-if="loading">
            <v-col cols="12">
                <v-skeleton-loader type="card" />
            </v-col>
        </v-row>

        <v-row v-else>
            <v-col cols="12" v-for="detalle in detalles" :key="detalle.id">
                <ProductoTrackingCard :detalle="detalle" :estados="estados" />
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import axios from 'axios'
import ProductoTrackingCard from './ProductoTrackingCard.vue'

export default {
    name: 'venta.tracking',
    components: { ProductoTrackingCard },

    data() {
        return {
            venta: {},
            detalles: [],
            estados: [],
            loading: false,
        }
    },

    async mounted() {
        this.loading = true
        try {
            const { data } = await axios.get(`/venta/${this.$route.params.id}/tracking`)
            this.detalles = data.detalles
            this.estados = data.estados
            this.venta = data.venta
        } finally {
            this.loading = false
        }
    }
}
</script>
