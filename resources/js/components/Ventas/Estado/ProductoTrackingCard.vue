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
                        <v-chip color="teal" variant="tonal">
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
            const actual = [...this.detalle.historial_estados]
                .filter(h => !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            return actual?.estado_produccion?.nombre ?? '—'
        }

    },

    created() {
        console.log(this.detalle);
    }
}

</script>