<template>
    <v-dialog v-model="open" max-width="500">
        <v-card>
            <v-card-title>
                Nuevo pago
            </v-card-title>

            <v-card-text>

                <div class="text-subtitle-2 mb-2">Historial de pagos</div>

                <v-list density="compact" v-if="venta.pagos?.length">
                    <v-list-item v-for="(pago, index) in venta.pagos" :key="pago.id">
                        <v-list-item-title>
                            {{ formatQuetzales(pago.monto) }} - {{ pago.metodo_pago }} <v-btn v-if="index > 0 && can('pago.borrar')" @click="deleteDialog = true, idEliminar = pago.id" color="error" density="compact" icon="mdi-delete"></v-btn>
                        </v-list-item-title>

                        <v-list-item-subtitle>
                            {{ pago.banco?.nombre }}
                        </v-list-item-subtitle>
                        <v-list-item-subtitle v-if="pago.comprobante_path" class="mt-1">
                            <v-btn :href="`/pagos/${pago.id}/comprobante`" target="_blank" rel="noopener"
                                size="x-small" color="primary" variant="tonal" prepend-icon="mdi-file-eye-outline">
                                Ver comprobante
                            </v-btn>
                        </v-list-item-subtitle>
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
                <MoneyInput v-model="form.monto" label="Monto" variant="outlined" density="compact"
                    :error-messages="errores.monto" />

                <v-select v-model="form.metodo_pago" :items="tipoPago" item-title="nombre" item-value="nombre"
                    label="Método de pago" variant="outlined" density="compact" :error-messages="errores.metodo_pago" />

                <v-autocomplete :items="bancos" item-title="nombre" item-value="id" density="compact" variant="outlined" label="Banco" v-model="form.banco_id"></v-autocomplete>

                <v-text-field v-model="form.referencia" label="No depósito" variant="outlined" density="compact"
                    :error-messages="errores.referencia" />

                <v-file-input v-model="form.comprobante" label="Comprobante de pago"
                    accept="image/*,.pdf" prepend-icon="mdi-paperclip" variant="outlined" density="compact"
                    :error-messages="errores.comprobante" />

            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" color="error" @click="close">
                    Cancelar
                </v-btn>

                <v-btn color="success" variant="tonal" :loading="saving" @click="save">
                    Guardar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>

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
import MoneyInput from '../common/MoneyInput.vue';
import { formatQuetzales } from '../../utils/money';


export default {
    name: 'PagoDialog',
    components: {
        MoneyInput,
    },

    props: {
        modelValue: Boolean,
        venta: Object, // null o existente
        bancos: Object, // null o existente
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
                banco_id: '',
                referencia: '',
                comprobante: null,
            },
            tipoPago: [
                { nombre: 'Efectivo' },
                { nombre: 'Pago con tarjeta' },
                { nombre: 'Depósito' },
                { nombre: 'Transferencia' },
                { nombre: 'Cheque' },
                { nombre: 'Transferencia Internacional' },
            ],
            deleteDialog: false,
            idEliminar: null,
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
                banco_id: '',
                comprobante: null,
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
                const formData = new FormData()
                formData.append('ventas_id', this.venta.id)
                formData.append('monto', this.form.monto)
                formData.append('metodo_pago', this.form.metodo_pago || '')
                formData.append('referencia', this.form.referencia || '')
                formData.append('banco_id', this.form.banco_id || '')

                const comprobante = Array.isArray(this.form.comprobante)
                    ? this.form.comprobante[0]
                    : this.form.comprobante

                if (comprobante) {
                    formData.append('comprobante', comprobante)
                }

                await axios.post('/pagos', formData)

                this.close()
                this.$emit('saved', false)

            } catch (e) {
                if (e.response?.status === 422) {
                    this.errores = e.response.data.errors || {}
                    if (!Object.keys(this.errores).length && e.response.data.message) {
                        toast.error(e.response.data.message)
                    }
                } else {
                    toast.error('Hubo un error inesperado al guardar el pago')
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

        formatQuetzales,

        async deletePago(){
            this.saving = true
            try{
                await axios.post("/pagos/eliminar", {id: this.idEliminar})
                this.saving = false
                this.deleteDialog = false
                this.close()
                this.$emit('saved', false)
            }catch(e){
                this.saving = false
                this.deleteDialog = false
                toast.error(e.response?.data?.message || 'Hubo un error inesperado al eliminar')
            }

        }
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
