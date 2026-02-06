<template>
    <v-dialog :model-value="modelValue" max-width="420" @update:modelValue="$emit('update:modelValue', $event)">
        <v-card>
            <v-card-title class="text-h6">
                Confirmar finalización
            </v-card-title>

            <v-card-text>
                ¿Estás seguro de finalizar este proceso y enviar el producto
                al siguiente estado de producción?
            </v-card-text>

            <v-card-text>
                <v-textarea variant="outlined" density="compact" v-model="observacion" label="Observación (opcional)" rows="2" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />

                <v-btn variant="tonal" color="red" @click="cerrar">
                    Cancelar
                </v-btn>

                <v-btn color="success" variant="tonal" :loading="loading" @click="confirmar">
                    Sí, finalizar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios'

export default {
    name: 'ConfirmarFinalizar',

    props: {
        modelValue: {
            type: Boolean,
            required: true
        },
        tarea: {
            type: Object,
            required: false
        }
    },

    emits: ['update:modelValue', 'confirmado'],

    data() {
        return {
            observacion: '',
            loading: false
        }
    },

    methods: {
        cerrar() {
            this.$emit('update:modelValue', false)
            this.observacion = ''
        },

        async confirmar() {
            if (!this.tarea) return

            this.loading = true

            try {
                await axios.post(
                    `/produccion/detalle/${this.tarea.detalle_ventas_id}/finalizar`,
                    {
                        observacion: this.observacion
                    }
                )

                this.$emit('confirmado')
                this.cerrar()
            } finally {
                this.loading = false
            }
        }
    }
}
</script>
