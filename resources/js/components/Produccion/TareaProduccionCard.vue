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
            <v-chip v-if="ultimoRegreso" color="red" variant="tonal" class="mt-2">
                <v-icon>mdi-arrow-u-left-top</v-icon> Regresado desde {{ ultimoRegreso.estado_produccion?.nombre }}
            </v-chip>

        </v-card-text>

        <v-card-actions class="d-flex flex-column ga-2">
            <!-- INICIAR -->
            <v-btn v-if="!tarea.proceso_estado" color="green" variant="tonal" block @click="$emit('iniciar', tarea)">
                Iniciar proceso
            </v-btn>

            <!-- CAMBIAR -->
            <v-btn v-if="tarea.proceso_estado" color="amber-darken-1" variant="tonal" block
                @click="$emit('iniciar', tarea)">
                Cambiar proceso
            </v-btn>

            <!-- FINALIZAR -->
            <v-btn v-if="tarea.proceso_estado" color="deep-orange" variant="tonal" block
                @click="$emit('finalizar', tarea)">
                Finalizar / Revisado
            </v-btn>

            <!-- REGRESAR (SIEMPRE QUE NO ESTÉ FINALIZADO) -->
            <v-btn color="purple-darken-1" variant="tonal" block @click="$emit('regresar', tarea)">
                Regresar
            </v-btn>

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
    },

    computed: {
        ultimoRegreso() {

            const historial = this.tarea.detalle_venta?.historial_estados
            if (!historial) return null

            // Último regreso registrado
            const regreso = [...historial]
                .filter(h => h.tipo_evento === 'regreso_estado')
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!regreso) return null

            // Estado actual activo
            const estadoActivo = historial
                .filter(h => h.tipo_evento === 'entrada_estado' && !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!estadoActivo) return null

            // Necesitamos comparar orden
            const ordenActual = estadoActivo.estado_produccion?.orden
            const ordenDesde = regreso.estado_produccion?.orden

            if (!ordenActual || !ordenDesde) return null

            // Mostrar solo si aún no supera el estado desde donde regresó
            if (ordenActual <= ordenDesde) {
                return regreso
            }

            return null
        }

        /* ultimoRegreso() {

            const historial = this.tarea.detalle_venta?.historial_estados
            if (!historial) return null

            // Estado actual activo
            const estadoActivo = historial
                .filter(h => h.tipo_evento === 'entrada_estado' && !h.fecha_fin)
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (!estadoActivo) return null

            // Buscar si justo antes de esta entrada hubo un regreso
            const ultimoEventoAntes = historial
                .filter(h => new Date(h.fecha_inicio) < new Date(estadoActivo.fecha_inicio))
                .sort((a, b) => new Date(b.fecha_inicio) - new Date(a.fecha_inicio))[0]

            if (ultimoEventoAntes?.tipo_evento === 'regreso_estado') {
                return ultimoEventoAntes
            }

            return null
        } */


    }

}
</script>
