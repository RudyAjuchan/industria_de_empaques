<template>
    <div class="pa-4">

        <!-- FILTROS -->
        <v-card class="mb-4 pa-4">
            <v-row>
                <v-col cols="12" md="3">
                    <v-text-field variant="outlined" density="compact" v-model="filtros.fecha_inicio" label="Fecha inicio" type="date" dense />
                </v-col>

                <v-col cols="12" md="3">
                    <v-text-field variant="outlined" density="compact" v-model="filtros.fecha_fin" label="Fecha fin" type="date" dense />
                </v-col>

                <v-col cols="12" md="3" class="d-flex align-center">
                    <v-btn color="primary" @click="getData">
                        Buscar
                    </v-btn>
                </v-col>

                <v-col cols="12" md="3" class="d-flex align-center">
                    <v-btn color="success" @click="exportarExcel" :disabled="items.length == 0">
                        Exportar Excel
                    </v-btn>
                </v-col>
            </v-row>
        </v-card>

        <!-- TABLA -->
        <v-data-table :headers="headers" :items="items" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-green-darken-2' }" />

    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'ContabilidadVue',

    data() {
        return {
            loading: false,

            filtros: {
                fecha_inicio: null,
                fecha_fin: null
            },

            items: [],

            headers: [
                { title: 'Fecha', key: 'fecha' },
                { title: 'Cliente', key: 'cliente' },
                { title: 'Producto', key: 'producto' },
                { title: 'Cantidad', key: 'cantidad' },
                { title: 'Total', key: 'total' },
                { title: 'Estado', key: 'estado' },
            ]
        }
    },

    methods: {

        async getData() {
            this.loading = true

            try {
                const { data } = await axios.get('/ventas/contabilidad', {
                    params: this.filtros
                })

                this.items = data
            } catch (error) {
                console.error(error)
            } finally {
                this.loading = false
            }
        },

        exportarExcel() {

            const params = new URLSearchParams(this.filtros).toString()

            const url = `/ventas/export/contabilidad?${params}`

            window.open(url, '_blank')
        }

    },

    mounted() {
        //this.getData()
    }
}
</script>