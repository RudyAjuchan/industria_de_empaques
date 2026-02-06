<template>
    <v-card outlined>
        <v-card-title class="text-subtitle-1">
            {{ tarea.detalle_venta.producto.nombre }}
        </v-card-title>

        <v-card-subtitle>
            Venta {{ tarea.detalle_venta.venta.serie }}-{{ tarea.detalle_venta.venta.numero }}
        </v-card-subtitle>

        <v-card-text>
            <div class="mb-2">
                Cantidad: <strong>{{ tarea.detalle_venta.cantidad }}</strong>
            </div>
            <v-chip :color="tarea.proceso_estado ? 'primary' : 'grey'" variant="tonal" size="small">
                {{ tarea.proceso_estado?.nombre ?? 'Sin iniciar' }}
            </v-chip>
        </v-card-text>

        <v-card-actions class="d-flex flex-column ga-2">
            <!-- SIN INICIAR -->
            <v-btn v-if="!tarea.proceso_estado" color="green" variant="tonal" block @click="$emit('iniciar', tarea)">
                Iniciar proceso
            </v-btn>

            <!-- CON PROCESO -->
            <template v-else>
                <v-btn color="amber-darken-1" block variant="tonal" @click="$emit('iniciar', tarea)">
                    Cambiar proceso
                </v-btn>

                <v-btn color="deep-orange" variant="tonal" block @click="$emit('finalizar', tarea)">
                    Finalizar / Revisado
                </v-btn>
            </template>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: 'TareaProduccionCard',
    props: {
        tarea: {
            type: Object,
            required: true
        }
    }
}
</script>
