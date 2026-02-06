<template>
    <v-expansion-panels>
        <v-expansion-panel v-for="(item, index) in historial" :key="index">
            <v-expansion-panel-title>
                {{ titulo(item) }}
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
                    return `Entrada a ${item.estado_produccion.nombre}`

                case 'inicio_proceso':
                    return `Inicio de proceso: ${item.proceso_estado.nombre}`

                case 'cambio_proceso':
                    return `Cambio de proceso: ${item.proceso_estado.nombre}`

                case 'finalizacion_estado':
                    return `Finalización de ${item.estado_produccion.nombre}`

                default:
                    return item.proceso_estado?.nombre
                        ?? item.estado_produccion?.nombre
                        ?? 'Evento'
            }
        }
    },
}
</script>