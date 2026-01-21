<template>
    <v-dialog v-model="open" max-width="900">
        <v-card>
            <v-card-title>
                {{ producto ? 'Editar producto' : 'Nuevo producto' }}
            </v-card-title>

            <v-card-text>
                <ProductoForm :producto="producto" @saved="onSaved" @cancel="close" />
            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>
import ProductoForm from './ProductosForm.vue'

export default {
    name: 'ProductosDialog',

    components: { ProductoForm },

    props: {
        modelValue: Boolean,
        producto: null,
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
        open(val) {
            this.$emit('update:modelValue', val)
        }
    },

    methods: {
        close() {
            this.$emit('update:modelValue', false)
        },

        onSaved(producto) {
            this.$emit('saved', producto)
            this.close()
        },
    },
}
</script>
