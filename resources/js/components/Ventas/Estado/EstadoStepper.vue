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
            const activo = [...this.historial]
                .filter(h => !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            return activo?.estado_produccions_id ?? null
        },

        estadosCompletados() {
            // Si está finalizado → todos completos
            if (this.estaFinalizado) {
                return this.estados.map(e => e.id)
            }

            if (!this.estadoActualId) return []

            const estadoActual = this.estados.find(
                e => e.id === this.estadoActualId
            )

            if (!estadoActual) return []

            return this.estados
                .filter(e => e.orden < estadoActual.orden)
                .map(e => e.id)
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
            return this.estados.findIndex(
                e => e.id === this.estadoActualId
            )
        },

        estaFinalizado() {

            const activo = this.historial.some(h => !h.fecha_fin)

            return !activo
        }


    }
}
</script>