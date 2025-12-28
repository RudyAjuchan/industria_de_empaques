<template>
    <v-dialog v-model="open" max-width="800">
        <v-card>
            <v-card-title>
                {{ cliente ? 'Editar cliente' : 'Nuevo cliente' }}
            </v-card-title>

            <v-card-text>
                <ClienteForm :cliente="cliente" @saved="onSaved" @cancel="close" />
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
import ClienteForm from './ClienteForm.vue'

export default {
    name: 'ClienteDialog',

    components: { ClienteForm },

    props: {
        modelValue: Boolean,
        cliente: Object,
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            open: this.modelValue,
        }
    },

    watch: {
        modelValue(val) {
            this.open = val
        },
    },

    methods: {
        close() {
            this.$emit('update:modelValue', false)
        },

        onSaved(cliente) {
            this.$emit('saved', cliente)
            this.close()
        },
    },
}
</script>
