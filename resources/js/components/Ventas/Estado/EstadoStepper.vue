<template>
    <v-stepper alt-labels class="mt-4 shadow-none border-0">
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
            const actual = this.historial.find(h => !h.fecha_fin)
            return actual?.estado_produccions_id ?? null
        },

        estadosCompletados() {
            return this.historial
                .filter(h => h.fecha_fin)
                .map(h => h.estado_produccions_id)
        },

        getIcon() {
            return (estadoId) => {
                if (this.estadosCompletados.includes(estadoId)) {
                    return 'mdi-check-circle'
                }

                if (estadoId === this.estadoActualId) {
                    return 'mdi-progress-clock'
                }

                return 'mdi-circle-outline'
            }
        },

        getColor() {
            return (estadoId) => {
                if (this.estadosCompletados.includes(estadoId)) {
                    return 'green'
                }

                if (estadoId === this.estadoActualId) {
                    return 'primary'
                }

                return 'grey'
            }
        }
    }
}
</script>