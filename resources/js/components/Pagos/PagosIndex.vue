<template>
    <v-container>
        <div class="d-flex align-center justify-space-between mb-4">
            <v-row>
                <v-col cols="6">
                    <div class="text-body-2 text-medium-emphasis">Gestiona los pagos</div>
                </v-col>
                <v-col cols="6" class="d-flex ga-2 align-center justify-end">
                    <v-text-field v-model="search" density="compact" hide-details variant="outlined" label="Buscar..."
                        prepend-inner-icon="mdi-magnify" style="max-width: 280px" />
                    <v-menu v-if="can('pago.reporte')">
                        <template #activator="{ props }">
                            <v-btn v-bind="props" variant="tonal" prepend-icon="mdi-export" color="teal">
                                Exportar
                            </v-btn>
                        </template>

                        <v-list density="compact">
                            <v-list-item prepend-icon="mdi-file-excel-outline" @click="exportExcel"
                                v-if="can('pago.reporte')">
                                <v-list-item-title>Excel</v-list-item-title>
                            </v-list-item>

                            <v-list-item prepend-icon="mdi-file-pdf-box" @click="exportPdf"
                                v-if="can('pago.reporte')">
                                <v-list-item-title>PDF</v-list-item-title>
                            </v-list-item>
                        </v-list>
                    </v-menu>
<!--                     <v-btn color="primary" prepend-icon="mdi-plus" @click="create" variant="tonal" :loading="loading"
                        v-if="can('pago.crear')">
                        Nuevo
                    </v-btn> -->
                </v-col>
            </v-row>
        </div>

        <v-data-table :headers="headers" :items="pagos" :loading="loading" fixed-header height="400px"
            :header-props="{ class: 'bg-teal-lighten-2' }" density="compact" :search="search"
            v-if="can('pago.ver')">
            <template v-slot:[`item.estado`]="{ item }">
                <v-chip :color="item.estado === 'anulada' ? 'red' : 'green'" dark>
                    {{ item.estado }}
                </v-chip>
            </template>
            <template v-slot:[`item.actions`]="{ item }">
                <v-btn icon color="success" variant="tonal" density="compact" @click="abrirPago(item)" v-if="can('pago.crear')">
                    <v-icon>mdi-cash-plus</v-icon>
                </v-btn>
            </template>

            <template v-slot:[`item.created_at`]="{ item }">
                {{ formatDate(item.created_at) }}
            </template>

            <template v-slot:[`item.updated_at`]="{ item }">
                {{ formatDate(item.updated_at) }}
            </template>
            <template v-slot:[`item.total`]="{ item }">
                {{ formatQuetzales(item.total) }}
            </template>
            <template v-slot:[`item.saldo_pendiente`]="{ item }">
                <v-chip color="red">{{ formatQuetzales(item.saldo_pendiente) }}</v-chip>
            </template>

            <template v-slot:[`item.estado_produccion`]="{ item }">
                <v-chip v-if="item.estado_produccion=='sin_iniciar'">{{ format_estado(item.estado_produccion) }}</v-chip>
                <v-chip v-if="item.estado_produccion=='en_produccion'" color="red">{{ format_estado(item.estado_produccion) }}</v-chip>
                <v-chip v-if="item.estado_produccion=='finalizada'" color="green">{{ format_estado(item.estado_produccion) }}</v-chip>
            </template>
        </v-data-table>

        <!-- DIALOG PARA GUARDAR -->
        <PagoDialog v-model="dialog" :venta="ventaSeleccionada" @saved="onSaved" :bancos="bancos"/>
    </v-container>
</template>

<script>
import axios from 'axios'
import PagoDialog from './PagoDialog.vue'
import { toast } from 'vue3-toastify'


export default {
    name: 'pago.index',
    components: {
        PagoDialog,
    },
    data() {
        return {
            pagos: [],
            loading: false,

            headers: [
                { title: 'Número', key: 'numero_completo' },
                { title: 'Cliente', key: 'cliente.nombre' },
                { title: 'Total Venta', key: 'total' },
                { title: 'Pago pendiente', key: 'saldo_pendiente' },
                { title: 'Vendedor', key: 'vendedor.name' },
                { title: 'Estado', key: 'estado' },
                { title: 'Estado Producción', key: 'estado_produccion' },
                { title: 'Fecha emitida', key: 'created_at' },
                { title: 'Acciones', key: 'actions', sortable: false },
            ],
            search: null,
            dialog: false,
            ventaSeleccionada: null,
            bancos: [],
        }
    },

    mounted() {
        this.fetchPagos()
        this.fetchBancos()
    },

    methods: {
        async fetchPagos() {
            this.loading = true
            try {
                const { data } = await axios.get('/pagos')
                this.pagos = data
            } catch (error) {
                toast.error('No se pudieron cargar los pagos')
            } finally {
                this.loading = false
            }
        },

        async fetchBancos(){
            this.loading = true
            try {
                const { data } = await axios.get('/banco')
                this.bancos = data
            } catch (error) {
                toast.error('No se pudieron cargar los bancos')
            } finally {
                this.loading = false
            }
        },

        exportExcel() {
            const params = new URLSearchParams({
                search: this.search
            })

            window.location.href = `/pagos/export/excel?${params.toString()}`
        },

        exportPdf() {
            const params = new URLSearchParams({
                search: this.search
            })
            window.open(`/pagos/export/pdf?${params.toString()}`, '_blank')
        },

        onSaved() {
            this.fetchPagos();
            toast.success('Pago registrado correctamente')
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

        format_estado(estado){
            return estado.replace('_', ' ')
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
        },

        abrirPago(venta) {
            this.ventaSeleccionada = venta
            this.dialog = true
        },

        formatQuetzales(value){
            if (value === null || value === undefined || isNaN(value)) {
                return 'Q 0.00';
            }

            return new Intl.NumberFormat('es-GT', {
                style: 'currency',
                currency: 'GTQ',
                minimumFractionDigits: 2
            }).format(value);
        },
    }
}
</script>
