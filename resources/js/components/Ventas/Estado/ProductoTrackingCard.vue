<template>
    <v-expansion-panels multiple>
        <v-expansion-panel>
            <v-expansion-panel-title>
                <v-row class="d-flex justify-space-between">
                    <v-col cols="3">
                        <strong>{{ detalle.producto.nombre }}</strong>
                        <div class="text-caption">
                            Cantidad: {{ detalle.cantidad }}
                        </div>
                    </v-col>
                    <v-col cols="2">
                        <v-chip :color="chipColor" variant="tonal">
                            {{ estadoActual }}
                        </v-chip>
                    </v-col>
                </v-row>

            </v-expansion-panel-title>

            <v-expansion-panel-text>
                <EstadoStepper :estados="estados" :historial="detalle.historial_estados" />

                <v-divider class="my-4" />

                <SubEstadosCola :historial="detalle.historial_estados" />
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels>

</template>
<script>
import EstadoStepper from './EstadoStepper.vue'
import SubEstadosCola from './SubEstadosCola.vue'

export default {
    props: {
        detalle: { type: Object, required: true },
        estados: { type: Object, required: true }
    },

    components: { EstadoStepper, SubEstadosCola },

    computed: {
        estadoActual() {

            const historial = this.detalle.historial_estados

            // Obtener la última entrada_estado registrada
            const ultimaEntrada = historial
                .filter(h => h.tipo_evento === 'entrada_estado')
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!ultimaEntrada) {
                return 'Sin iniciar'
            }

            // Verificar si existe una finalización para ese mismo estado
            const fueFinalizado = historial.some(h =>
                h.tipo_evento === 'finalizacion_estado' &&
                h.estado_produccions_id === ultimaEntrada.estado_produccions_id
            )

            // Obtener último estado del sistema
            const ultimoEstado = [...this.estados]
                .sort((a, b) => b.orden - a.orden)[0]

            if (
                fueFinalizado &&
                ultimaEntrada.estado_produccions_id === ultimoEstado.id
            ) {
                return 'Finalizado'
            }

            return ultimaEntrada.estado_produccion?.nombre
        },

        chipColor() {
            if (this.estadoActual === 'Finalizado') return 'green'
            if (this.estadoActual === 'Sin iniciar') return 'grey'
            return 'orange'
        }
    },

}

</script>