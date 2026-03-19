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
                <div v-if="camposDefinidos.length">
                    <div class="text-subtitle-2 mb-2">
                        Datos adicionales
                    </div>
    
                    <div v-for="campo in camposDefinidos" :key="campo.id" class="mb-3">
    
                        <!-- STRING -->
                        <v-text-field v-if="campo.tipo === 'texto'" v-model="valoresCampos[campo.id]"
                            :label="campo.nombre" density="compact" variant="outlined"
                            :error="!!errores[`campos.${campo.id}`]" :error-messages="errores[`campos.${campo.id}`]" />

                        <!-- INTEGER -->
                        <v-text-field v-else-if="campo.tipo === 'entero'" v-model="valoresCampos[campo.id]"
                            :label="campo.nombre" density="compact" variant="outlined"
                            :error="!!errores[`campos.${campo.id}`]" :error-messages="errores[`campos.${campo.id}`]" />

                        <!-- DOUBLE -->
                        <v-text-field v-else-if="campo.tipo === 'decimal'" step="0.01" v-model="valoresCampos[campo.id]"
                            :label="campo.nombre" density="compact" variant="outlined"
                            :error="!!errores[`campos.${campo.id}`]" :error-messages="errores[`campos.${campo.id}`]" />

                        <!-- DATE -->
                        <v-text-field v-else-if="campo.tipo === 'fecha'" type="date" v-model="valoresCampos[campo.id]"
                            :label="campo.nombre" density="compact" variant="outlined" />
                    </div>
                </div>
                <v-textarea variant="outlined" density="compact" v-model="observacion" label="Observación (opcional)"
                    rows="2"/>
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
            loading: false,
            loading: false,
            camposDefinidos: [],
            valoresCampos: {},
            errores: {},
        }
    },

    methods: {
        cerrar() {
            this.$emit('update:modelValue', false)
            this.observacion = ''
            this.camposDefinidos = []
            this.valoresCampos = {}
        },

        async confirmar() {
            if (!this.tarea) return

            this.loading = true

            try {
                await axios.post(
                    `/produccion/detalle/${this.tarea.detalle_ventas_id}/finalizar`,
                    {
                        observacion: this.observacion,
                        campos: this.valoresCampos
                    }
                )

                this.$emit('confirmado')
                this.cerrar()
            }catch (error) {

                if (error.response?.status === 422) {
                    this.errores = error.response.data.errors
                }

            } finally {
                this.loading = false
            }
        },

        async cargarCampos() {
            const { data } = await axios.get(
                `/produccion/${this.tarea.detalle_ventas_id}/campos-finalizacion`
            )

            this.camposDefinidos = data.campos || []

            this.valoresCampos = {}

            this.camposDefinidos.forEach(campo => {
                this.valoresCampos[campo.id] = null
            })
        }
    },

    watch: {
        async modelValue(val) {
            if (val && this.tarea) {
                await this.cargarCampos()
            }
        }
    },
}
</script>
