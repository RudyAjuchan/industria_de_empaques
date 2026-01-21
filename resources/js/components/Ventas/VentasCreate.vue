<template>
    <v-card>
        <v-card-title>Nueva Venta</v-card-title>
        <VentasForm @cancel="$router.push('/ventas')" />
    </v-card>
</template>

<script>
import VentasForm from './VentasForm.vue'

export default {
    components: { VentasForm }
}
</script>

