<template>
    <v-dialog :model-value="modelValue" max-width="500" @update:modelValue="$emit('update:modelValue', $event)">
        <v-card>
            <v-card-title>
                {{ tarea?.proceso_estado ? 'Cambiar proceso' : 'Iniciar proceso' }}
            </v-card-title>

            <v-card-text>
                <v-select v-model="proceso" :items="procesos" item-title="nombre" item-value="id" label="Proceso"
                    required variant="outlined" density="compact" />

                <v-textarea v-model="observacion" label="Observación" rows="3" variant="outlined" density="compact" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" @click="cerrar" color="red">
                    Cancelar
                </v-btn>
                <v-btn color="green" variant="tonal" :disabled="!proceso || loading" :loading="loading" @click="guardar">
                    Guardar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
import axios from 'axios'

export default {
    name: 'ModalProceso',

    props: {
        modelValue: Boolean,
        tarea: Object
    },

    emits: ['update:modelValue', 'guardado'],

    data() {
        return {
            procesos: [],
            proceso: null,
            observacion: '',
            loading: false
        }
    },

    watch: {
        modelValue(val) {
            if (val) {
                this.cargarProcesos()
            }
        }
    },

    methods: {
        cerrar() {
            this.$emit('update:modelValue', false)
            this.reset()
        },

        reset() {
            this.proceso = null
            this.observacion = ''
        },

        async cargarProcesos() {
            if (!this.tarea) return

            // Procesos del estado actual
            const estadoId = this.tarea.estado_produccions_id

            const { data } = await axios.get(
                `/produccion/estado/${estadoId}/procesos`
            )

            this.procesos = data
        },

        async guardar() {
            this.loading = true

            try {
                await axios.post(
                    `/produccion/detalle/${this.tarea.detalle_ventas_id}/proceso`,
                    {
                        proceso_estado_produccions_id: this.proceso,
                        observacion: this.observacion
                    }
                )

                this.$emit('guardado')
                this.cerrar()
            } finally {
                this.loading = false
            }
        }
    }
}
</script>
