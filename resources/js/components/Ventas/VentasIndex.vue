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

            <template v-slot:[`item.created_at`]="{ item }">
                {{ formatDate(item.created_at) }}
            </template>

            <template v-slot:[`item.estado`]="{ item }">
                <v-chip :color="item.estado === 'anulada' ? 'red' : 'green'" dark>
                    {{ item.estado }}
                </v-chip>
            </template>

            <template v-slot:[`item.acciones`]="{ item }">
                <div class="d-flex ga-1">
                    <v-btn icon size="small" @click="anularVenta(item)" v-if="item.estado !== 'anulada'" color="red" variant="tonal">
                        <v-tooltip activator="parent" location="top">Anular</v-tooltip>
                        <v-icon>mdi-cancel</v-icon>
                    </v-btn>
                    <v-btn icon size="small" @click="verDetalle(item)" color="primary" variant="tonal">
                        <v-tooltip activator="parent" location="top">Ver</v-tooltip>
                        <v-icon>mdi-eye</v-icon>
                    </v-btn>
                    <v-btn icon size="small" @click="imprimirVenta(item.id)" color="green" variant="tonal">
                        <v-tooltip activator="parent" location="top">Imprimir</v-tooltip>
                        <v-icon>mdi-printer</v-icon>
                    </v-btn>
                    <v-btn icon size="small" @click="verEstados(item.id)" color="orange" variant="tonal">
                        <v-tooltip activator="parent" location="top">Ver estados</v-tooltip>
                        <v-icon>mdi-list-status</v-icon>
                    </v-btn>
                </div>
            </template>

            <template v-slot:[`item.estado_produccion`]="{ item }">
                <v-chip v-if="item.estado_produccion=='sin_iniciar'">{{ format_estado(item.estado_produccion) }}</v-chip>
                <v-chip v-if="item.estado_produccion=='en_produccion'" color="red">{{ format_estado(item.estado_produccion) }}</v-chip>
                <v-chip v-if="item.estado_produccion=='finalizada'" color="green">{{ format_estado(item.estado_produccion) }}</v-chip>
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
                { title: 'Número', key: 'numero_completo' },
                { title: 'Cliente', key: 'cliente.nombre' },
                { title: 'Total', key: 'total' },
                { title: 'Vendedor', key: 'vendedor.name' },
                { title: 'Estado', key: 'estado' },
                { title: 'Estado Producción', key: 'estado_produccion' },
                { title: 'Fecha emitida', key: 'created_at' },
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
            const params = new URLSearchParams({
                search: this.search
            })

            window.location.href = `/venta/export/excel?${params.toString()}`
        },exportPdf(){
            const params = new URLSearchParams({
                search: this.search
            })
            window.open(`/venta/export/pdf?${params.toString()}`, '_blank')
        },
        verDetalle(item){
            this.$router.push(`/venta/${item.id}`)
        },
        formatDate(date) {
            if (!date) return ''
            return new Date(date).toLocaleString('es-GT', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            })
        },

        imprimirVenta(id) {
            window.open(`/venta/${id}/imprimir`, '_blank')
        },

        verEstados(id){
            this.$router.push(`/venta/${id}/tracking`)
        },

        format_estado(estado){
            return estado.replace('_', ' ')
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        }
    },

    mounted() {
        this.getVentas()
    }
}
</script>