<template>
    <v-expansion-panels>
        <v-expansion-panel v-for="(item, index) in historial" :key="index">
            <v-expansion-panel-title>
                <v-row class="justify-space-between">
                    <v-col cols="5">
                        {{ titulo(item) }}
                    </v-col>
                    <v-col cols="3">
                        <v-chip density="compact" :color="getChipColor(item.estado_produccion.orden)">{{ `${item.estado_produccion.orden}. ${item.estado_produccion.nombre}` }}</v-chip>
                    </v-col>
                </v-row>
            </v-expansion-panel-title>

            <v-expansion-panel-text>
                <div class="text-caption">
                    Responsable: {{ item.usuario?.name ?? 'Sistema' }}
                </div>

                <div class="text-caption">
                    {{ item.tipo_evento === 'finalizacion_estado' ? 'Finalizado:' : 'Inicio:' }}
                    {{ format(item.fecha_inicio) }}
                </div>

                <div v-if="item.fecha_fin" class="text-caption">
                    Fin: {{ format(item.fecha_fin) }}
                </div>

                <div v-if="item.observacion" class="mt-2">
                    {{ item.observacion }}
                </div>
            </v-expansion-panel-text>

        </v-expansion-panel>
    </v-expansion-panels>

</template>

<script>
export default {
    name: 'SubEstadosCola',

    props: {
        historial: {
            type: Array,
            required: true,
            default: () => []
        }
    },

    methods: {
        format(date) {
            if (!date) return ''
            return new Date(date).toLocaleString('es-GT')
        },

        titulo(item) {
    switch (item.tipo_evento) {

                case 'entrada_estado':
                    return `Entrada a ${item.estado_produccion?.nombre}`

                case 'inicio_proceso':
                    return `Inicio de proceso: ${item.proceso_estado?.nombre}`

                case 'cambio_proceso':
                    return `Cambio de proceso: ${item.proceso_estado?.nombre}`

                case 'finalizacion_estado':
                    return `Finalización de ${item.estado_produccion?.nombre}`

                case 'regreso_estado':
                    return `Regresado desde ${item.estado_produccion?.nombre}`

                default:
                    return item.proceso_estado?.nombre
                        ?? item.estado_produccion?.nombre
                        ?? 'Evento'
            }
        },

        getChipColor(id) {
            const colorMap = {
                1: 'primary',       // Azul Marca
                2: 'pink',          // Rosa Vibrante (Salto total de tono)
                3: 'indigo',        // Azul Profundo
                4: 'cyan',          // Turquesa Claro
                5: 'deep-purple',   // Morado Oscuro
                6: 'teal',          // Verde Azulado
                7: 'orange-darken-1',     // Gris Azulado Neutro
                8: 'purple-lighten-2', // Lavanda suave
                9: 'blue',          // Azul Rey
                10: 'brown',        // Tierra
                11: 'light-blue',   // Celeste
                12: 'grey-darken-3',// Gris Carbon
                13: 'purple',       // Morado Brillante
                14: 'light-blue-darken-3', // Azul Petróleo
                15: 'info'          // Cian Informativo
            };

            return colorMap[id] || 'grey';
        }
    },
}
</script>