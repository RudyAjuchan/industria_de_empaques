<template>
    <v-stepper alt-labels class="mt-4 shadow-none border-0" v-model="activeStep">
        <v-stepper-header>
            <template v-for="(estado, index) in estados" :key="estado.id">
                <v-stepper-item :value="index" :complete="estadosCompletados.includes(estado.id)"
                    :color="getColor(estado.id)" :title="estado.nombre">
                    <template #icon>
                        <v-icon size="22">
                            {{ getIcon(estado.id) }}
                        </v-icon>
                    </template>
                </v-stepper-item>

                <v-divider v-if="index !== estados.length - 1" :key="`divider-${estado.id}`"></v-divider>
            </template>
        </v-stepper-header>
    </v-stepper>
</template>
<script>
export default {
    name: 'EstadoStepper',

    props: {
        estados: {
            type: Array,
            required: true
        },
        historial: {
            type: Array,
            required: true
        }
    },

    computed: {
        estadoActualId() {

            const ultimaEntrada = [...this.historial]
                .filter(h => h.tipo_evento === 'entrada_estado')
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!ultimaEntrada) {
                return null
            }

            // ¿fue finalizado?
            const fueFinalizado = this.historial.some(h =>
                h.tipo_evento === 'finalizacion_estado' &&
                h.estado_produccions_id === ultimaEntrada.estado_produccions_id
            )

            // último estado del flujo
            const ultimoEstado = [...this.estados]
                .sort((a, b) => b.pivot.orden - a.pivot.orden)[0]

            // si finalizó el último → ya no hay estado activo
            if (
                fueFinalizado &&
                ultimaEntrada.estado_produccions_id === ultimoEstado?.id
            ) {
                return null
            }

            return ultimaEntrada.estado_produccions_id
        },

        estadosCompletados() {

            return this.historial
                .filter(h => h.tipo_evento === 'finalizacion_estado')
                .map(h => h.estado_produccions_id)
        },

        getIcon() {
            return (estadoId) => {

                // Si está finalizado, todos con check
                if (this.estaFinalizado) {
                    return 'mdi-check-circle'
                }

                if (estadoId === this.estadoActualId) {
                    return 'mdi-progress-clock'
                }

                if (this.estadosCompletados.includes(estadoId)) {
                    return 'mdi-check-circle'
                }

                return 'mdi-circle-outline'
            }
        },

        getColor() {
            return (estadoId) => {

                if (this.estaFinalizado) {
                    return 'green'
                }

                if (this.estadosCompletados.includes(estadoId)) {
                    return 'green'
                }

                if (estadoId === this.estadoActualId) {
                    return 'primary'
                }

                return 'grey'
            }
        },

        activeStep() {

            if (this.estaFinalizado) {
                return this.estados.length - 1
            }

            return this.estados.findIndex(
                e => e.id === this.estadoActualId
            )
        },

        estaFinalizado() {

            const ultimoEstado = [...this.estados]
                .sort((a, b) => b.pivot.orden - a.pivot.orden)[0]

            if (!ultimoEstado) {
                return false
            }

            return this.historial.some(h =>
                h.tipo_evento === 'finalizacion_estado' &&
                h.estado_produccions_id === ultimoEstado.id
            )
        }


    }
}
</script>