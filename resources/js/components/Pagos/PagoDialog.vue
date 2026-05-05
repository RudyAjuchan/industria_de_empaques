<template>
    <v-dialog v-model="open" max-width="420">
        <v-card>
            <v-card-title>
                Nuevo pago
            </v-card-title>

            <v-card-text>

                <div class="text-subtitle-2 mb-2">Historial de pagos</div>

                <v-list density="compact" v-if="venta.pagos?.length">
                    <v-list-item v-for="pago in venta.pagos" :key="pago.id">
                        <v-list-item-title>
                            Q{{ pago.monto }} - {{ pago.metodo_pago }}
                        </v-list-item-title>

                        <v-list-item-subtitle>
                            {{ formatDate(pago.created_at) }}
                        </v-list-item-subtitle>
                    </v-list-item>
                </v-list>

                <div v-else class="text-caption mb-3">
                    No hay pagos registrados
                </div>
                
                <v-divider class="my-2"></v-divider>

                <div class="text-caption mb-2">
                    Total venta: {{ formatQuetzales(venta.total) }}
                </div>

                <div class="text-caption mb-2">
                    Total Pagado: {{ formatQuetzales(totalPagado) }}
                </div>

                <div class="text-caption mb-4 text-red">
                    Pendiente: {{ formatQuetzales(saldoPendiente) }}
                </div>

                <div class="text-subtitle-2 mb-2">Datos para el nuevo pago</div>
                <v-text-field v-model="form.monto" label="Monto" type="number" variant="outlined" density="compact"
                    :error-messages="errores.monto" />

                <v-select v-model="form.metodo_pago" :items="tipoPago" item-title="nombre" item-value="nombre"
                    label="Método de pago" variant="outlined" density="compact" :error-messages="errores.metodo_pago" />

                <v-text-field v-model="form.referencia" label="No depósito" variant="outlined" density="compact"
                    :error-messages="errores.referencia" />

            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" color="red" @click="close">
                    Cancelar
                </v-btn>

                <v-btn color="green" variant="tonal" :loading="saving" @click="save">
                    Guardar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>

export default {
    name: 'PagoDialog',

    props: {
        modelValue: Boolean,
        venta: Object, // null o existente
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            open: this.modelValue,
            saving: false,
            errores: {},
            form: {
                id: null,
                ventas_id: '',
                monto: '',
                metodo_pago: '',
                referencia: '',
            },
            tipoPago: [
                { nombre: 'Efectivo' },
                { nombre: 'Pago con tarjeta' },
                { nombre: 'Depósito' },
                { nombre: 'Transferencia' },
                { nombre: 'Cheque' },
                { nombre: 'Transferencia Internacional' },
            ],
        }
    },

    watch: {
        modelValue(val) {
            this.open = val
            if (val) this.load()
        },
    },

    methods: {
        load() {
            this.form = {
                monto: '',
                metodo_pago: '',
                referencia: '',
            }

            this.errores = {}
        },

        close() {
            this.$emit('update:modelValue', false)
        },

        async save() {
            this.errores = {}
            this.saving = true

            try {
                let res

                await axios.post('/pagos', {
                    ventas_id: this.venta.id,
                    monto: this.form.monto,
                    metodo_pago: this.form.metodo_pago,
                    referencia: this.form.referencia
                })

                this.close()
                this.$emit('saved', false)

            } catch (e) {
                if (e.response?.status === 422) {
                    this.errores = e.response.data.errors || {}
                }
            } finally {
                this.saving = false
            }
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
    },

    computed: {
        totalPagado() {
            if (!this.venta?.pagos) return 0
            return this.venta.pagos.reduce((sum, p) => sum + Number(p.monto), 0)
        },
        saldoPendiente() {
            return (this.venta?.total || 0) - this.totalPagado
        }
    }
}
</script>