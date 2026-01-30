<template>
    <v-expansion-panels>
        <v-expansion-panel v-for="(item, index) in historial" :key="index">
            <v-expansion-panel-title>
                {{ item.proceso_estado?.nombre ?? 'Sin proceso' }}
            </v-expansion-panel-title>

            <v-expansion-panel-text>
                <div class="text-caption">
                    Responsable: {{ item.usuario.name }}
                </div>
                <div class="text-caption">
                    Inicio: {{ format(item.fecha_inicio) }}
                </div>
                <div v-if="item.fecha_fin" class="text-caption">
                    Fin: {{ format(item.fecha_fin) }}
                </div>
                <div v-if="item.observacion">
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
        }
    }
}
</script>