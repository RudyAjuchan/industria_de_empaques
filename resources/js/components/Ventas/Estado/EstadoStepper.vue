<template>
    <v-stepper alt-labels>
        <v-stepper-item
            v-for="(item, index) in historial"
            :key="index"
            :value="index"
            :complete="!!item.fecha_fin"
        >
            {{ item.estado_produccion?.nombre }}
        </v-stepper-item>
    </v-stepper>
</template>
<script>
export default {
    name: 'EstadoStepper',

    props: {
        historial: {
            type: Array,
            required: true,
            default: () => []
        }
    }
}
</script>