<template>
    <v-dialog v-model="dialog" max-width="500">
        <v-card>
            <v-card-title>
                Regresar estado
            </v-card-title>

            <v-card-text>

                <v-select v-model="estadoDestino" variant="outlined" density="compact" :items="estadosDisponibles" item-title="nombre" item-value="id"
                    label="Seleccionar estado" />

                <v-textarea v-model="observacion" variant="outlined" density="compact" label="Observación" rows="3" />

            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" color="red" @click="cerrar">
                    Cancelar
                </v-btn>

                <v-btn color="success" variant="tonal" :disabled="!estadoDestino" @click="confirmar">
                    Confirmar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>

export default {
    name: 'ModalRegresar',

    props: {
        modelValue: Boolean,
        tarea: Object
    },

    emits: ['update:modelValue', 'confirmado'],

    data() {
        return {
            dialog: this.modelValue,
            estadosDisponibles: [],
            estadoDestino: null,
            observacion: ''
        }
    },

    watch: {
        modelValue(val) {
            this.dialog = val
            if (val) this.cargarEstados()
        },
        dialog(val) {
            this.$emit('update:modelValue', val)
        }
    },

    methods: {

        async cargarEstados() {
            const { data } = await axios.get(
                `/produccion/estados-anteriores/${this.tarea.id}`
            )

            this.estadosDisponibles = data
        },

        async confirmar() {

            await axios.post(
                `/produccion/detalle/${this.tarea.detalle_ventas_id}/regresar`,
                {
                    estado_destino_id: this.estadoDestino,
                    observacion: this.observacion
                }
            )

            this.$emit('confirmado')
            this.cerrar()
        },

        cerrar() {
            this.dialog = false
            this.estadoDestino = null
            this.observacion = ''
        }
    }
}

</script>