<template>
    <v-container>
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona las ventas</div>
                </v-col>
                <v-col cols="6" class="d-flex ga-2 align-center justify-end">
                    <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar..."
                        prepend-inner-icon="mdi-magnify" style="max-width: 280px" />
                    <v-menu>
                        <template #activator="{ props }">
                            <v-btn v-bind="props" variant="tonal" prepend-icon="mdi-export" color="teal">
                                Exportar
                            </v-btn>
                        </template>

                        <v-list density="compact">
                            <v-list-item prepend-icon="mdi-file-excel-outline" @click="exportExcel"
                                v-if="can('venta.reporte')">
                                <v-list-item-title>Excel</v-list-item-title>
                            </v-list-item>

                            <v-list-item prepend-icon="mdi-file-pdf-box" @click="exportPdf"
                                v-if="can('venta.reporte')">
                                <v-list-item-title>PDF</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
                    <v-btn color="primary" prepend-icon="mdi-plus" variant="tonal" :loading="loading" @click="$router.push('/venta/create')"
                        v-if="can('venta.crear')">
                        Nuevo
                    </v-btn>
                </v-col>
            </v-row>
        </div>
        <v-data-table :headers="headers" :items="ventas" :loading="loading" class="elevation-1" v-if="can('venta.ver')" fixed-header height="400px"
            :header-props="{ class: 'bg-green-darken-2' }" density="compact" :search="search">

            <template v-slot:[`item.estado`]="{ item }">
                <v-chip :color="item.estado === 'anulada' ? 'red' : 'green'" dark>
                    {{ item.estado }}
                </v-chip>
            </template>

            <template v-slot:[`item.acciones`]="{ item }">
                <v-btn icon size="small" @click="anularVenta(item)" v-if="item.estado !== 'anulada'">
                    <v-icon>mdi-cancel</v-icon>
                </v-btn>
            </template>

        </v-data-table>
    </v-container>
</template>


<script>
import axios from 'axios'

export default {
    name: 'ventas.index',
    data() {
        return {
            ventas: [],
            loading: false,
            headers: [
                { title: 'Serie', key: 'serie' },
                { title: 'Número', key: 'numero' },
                { title: 'Cliente', key: 'cliente.nombre' },
                { title: 'Total', key: 'total' },
                { title: 'Estado', key: 'estado' },
                { title: 'Acciones', key: 'acciones', sortable: false },
            ],
            search: null,
        }
    },

    methods: {
        async getVentas() {
            this.loading = true
            try {
                const { data } = await axios.get('/venta')
                this.ventas = data
            } finally {
                this.loading = false
            }
        },

        async anularVenta(item) {
            if (!confirm('¿Deseas anular esta venta?')) return

            await axios.delete(`/venta/${item.id}`)
            this.getVentas()
        },
        exportExcel(){

        },exportPdf(){

        }
    },

    mounted() {
        this.getVentas()
    }
}
</script>