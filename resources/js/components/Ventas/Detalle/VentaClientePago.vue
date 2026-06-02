<template>
    <v-row class="mt-4">

        <v-col cols="12" md="6">
            <v-row>
                <!-- CLIENTE -->
                <v-col cols="12">
                    <v-card elevation="2" rounded="xl" class="pa-4">
                        <div class="text-subtitle-1 font-weight-bold mb-3">
                            Cliente
                        </div>

                        <div class="text-body-2"><b>Nombre:</b> {{ venta.cliente.nombre }}</div>
                        <div class="text-body-2"><b>Tel:</b> {{ telefonos }}</div>
                        <div class="text-body-2"><b>Email:</b> {{ emails }}</div>
                        <div class="text-body-2"><b>NIT:</b> {{ venta.cliente.nit || '-' }}</div>
                        <div class="text-body-2"><b>Dirección:</b> {{ direccion }}</div>
                    </v-card>
                </v-col>

                <!-- TOTALES -->
                <v-col cols="12">
                    <v-card elevation="2" rounded="xl" class="pa-4">

                        <div class="text-subtitle-1 font-weight-bold mb-3">
                            Totales
                        </div>

                        <div class="d-flex justify-space-between mb-2">
                            <span>Subtotal</span>
                            <v-chip size="small" color="primary">Q {{ venta.subtotal }}</v-chip>
                        </div>

                        <div class="d-flex justify-space-between mb-2">
                            <span>Descuento</span>
                            <v-chip size="small" color="red">Q {{ venta.descuento }}</v-chip>
                        </div>

                        <div class="d-flex justify-space-between mb-2">
                            <span>Logo</span>
                            <v-chip size="small" color="green">Q {{ venta.costo_logo }}</v-chip>
                        </div>

                        <div class="d-flex justify-space-between mb-2">
                            <span>Envío</span>
                            <v-chip size="small" color="green">Q {{ venta.costo_envio }}</v-chip>
                        </div>

                        <!-- PROMO -->
                        <div v-if="venta.promociones" class="mb-2">
                            <div class="d-flex justify-space-between">
                                <span>Promoción</span>
                                <v-chip size="small" color="purple">
                                    - Q {{ promocionMonto.toFixed(2) }}
                                </v-chip>
                            </div>

                            <div class="text-caption text-medium-emphasis">
                                {{ venta.promociones.nombre || 'Promo aplicada' }}
                            </div>
                        </div>

                        <v-divider class="my-3" />

                        <div class="d-flex justify-space-between align-center">
                            <span class="font-weight-bold">TOTAL</span>
                            <v-chip color="red" class="text-h6">
                                Q {{ venta.total }}
                            </v-chip>
                        </div>

                    </v-card>
                </v-col>
            </v-row>
        </v-col>

        <!-- PAGOS -->
        <v-col cols="12" md="6">
            <v-card elevation="2" rounded="xl" class="pa-4">

                <div class="d-flex justify-space-between align-center mb-3">
                    <div class="text-subtitle-1 font-weight-bold">
                        Pagos
                    </div>

                    <v-chip :color="saldoPendiente > 0 ? 'red' : 'green'" size="small">
                        {{ saldoPendiente > 0 ? 'Pendiente' : 'Pagado' }}
                    </v-chip>
                </div>

                <div class="text-body-2 mb-2">
                    <b>Total:</b> Q {{ venta.total }}
                </div>

                <div class="text-body-2 mb-2">
                    <b>Pagado:</b>
                    <v-chip size="small" color="green">
                        Q {{ totalPagado.toFixed(2) }}
                    </v-chip>
                </div>

                <div class="text-body-2 mb-3">
                    <b>Pendiente:</b>
                    <v-chip size="small" color="red">
                        Q {{ saldoPendiente.toFixed(2) }}
                    </v-chip>
                </div>

                <!-- HISTORIAL -->
                <div class="text-caption mb-2">Historial</div>

                <v-list density="compact" v-if="venta.pagos?.length">
                    <v-list-item v-for="(p,index) in venta.pagos" :key="p.id">
                        <v-list-item-title>
                            Q{{ p.monto }} - {{ p.metodo_pago || 'N/A' }} <v-btn v-if="index > 0 && can('pago.borrar')" :disabled="venta.estado_produccion == 'finalizada'" @click="deleteDialog = true, idEliminar = p.id" color="error" density="compact" icon="mdi-delete"></v-btn>
                        </v-list-item-title>
                        <v-list-item-subtitle>
                            {{ p.banco?.nombre }}
                        </v-list-item-subtitle>
                        <v-list-item-subtitle>
                            {{ formatDate(p.created_at) }}
                        </v-list-item-subtitle>
                    </v-list-item>
                </v-list>

                <div v-else class="text-caption">
                    Sin pagos registrados
                </div>

            </v-card>
        </v-col>

    </v-row>

    <v-divider class="my-4" />

    <!-- DIALOG PARA ELIMINAR -->
    <v-dialog v-model="deleteDialog" max-width="420">
        <v-card rounded="xl">
            <v-card-title class="text-subtitle-1 font-weight-bold">
                Eliminar pago
            </v-card-title>

            <v-card-text class="text-body-2 text-medium-emphasis">
                ¿Seguro que quieres eliminar? Esta acción no se puede deshacer.
            </v-card-text>

            <v-card-actions class="px-4 pb-4">
                <v-spacer />
                <v-btn variant="tonal" @click="deleteDialog = false, idEliminar = null">Cancelar</v-btn>
                <v-btn color="error" :loading="saving" @click="deletePago">Eliminar</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import { toast } from 'vue3-toastify';
export default {
    props: {
        venta: Object
    },

    data(){
        return{
            deleteDialog: false,
            idEliminar: null,
            saving: false,
        }
    },

    computed: {
        totalPagado() {
            return this.venta.pagos?.reduce((s, p) => s + Number(p.monto), 0) || 0
        },

        saldoPendiente() {
            return (this.venta.total || 0) - this.totalPagado
        },

        telefonos() {
            return this.venta.cliente.telefonos
                ?.map(t => `${t.telefono_codigo_pais} ${t.telefono_numero}`)
                .join(' / ') || '-'
        },

        emails() {
            return this.venta.cliente.emails
                ?.map(e => e.email)
                .join(' / ') || '-'
        },

        direccion() {
            const c = this.venta.cliente
            if (c.municipio) {
                return `${c.direccion}, ${c.municipio.nombre}`
            }
            return `${c.direccion}, ${c.ciudad_pais}`
        },

        promocionMonto() {
            if (!this.venta.promociones) return 0

            const promo = this.venta.promociones
            const subtotal = parseFloat(this.venta.subtotal || 0)

            return promo.tipo === 'porcentaje'
                ? subtotal * (promo.valor / 100)
                : promo.valor
        },
    },

    methods: {
        formatDate(date) {
            return new Date(date).toLocaleString('es-GT')
        },
        async deletePago(){
            this.saving = true
            try{
                await axios.post("/pagos/eliminar", {id: this.idEliminar})
                this.saving = false
                this.deleteDialog = false
                window.location.reload();
            }catch(e){
                this.saving = false
                this.deleteDialog = false
                toast.error(e.response?.data?.message || 'Hubo un error inesperado al eliminar')
            }

        }
    }
}
</script>
