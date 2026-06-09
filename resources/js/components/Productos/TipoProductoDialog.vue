<template>
    <v-dialog v-model="open" max-width="420">
        <v-card>
            <v-card-title>
                Nuevo tipo de producto
            </v-card-title>

            <v-card-text>
                <v-text-field v-model="form.nombre" variant="outlined" density="compact" label="Nombre" autofocus
                    :error-messages="errors.nombre" />
                <v-text-field v-model="form.codigo" variant="outlined" density="compact" label="Código"
                    hint="Ejemplo: T3XD, CBM, BOL" persistent-hint :error-messages="errors.codigo" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" color="error" @click="close">Cancelar</v-btn>
                <v-btn color="success" variant="tonal" :loading="saving" @click="save">
                    Guardar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios'

export default {
    name: 'TipoProductoDialog',

    props: {
        modelValue: Boolean,
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            open: this.modelValue,
            saving: false,
            errors: {},
            form: {
                nombre: '',
                codigo: '',
            },
        }
    },

    watch: {
        modelValue(val) {
            this.open = val
            if (val) this.reset()
        },
    },

    methods: {
        reset() {
            this.form = { nombre: '', codigo: '' }
            this.errors = {}
        },

        close() {
            this.$emit('update:modelValue', false)
        },

        async save() {
            this.errors = {}
            this.saving = true
            this.form.codigo = this.normalizeCodigo(this.form.codigo)

            try {
                const { data } = await axios.post('/producto/tipos', this.form)
                this.$emit('saved', data)
                this.close()
            } catch (err) {
                this.errors = err.response?.data?.errors || {}
            } finally {
                this.saving = false
            }
        },

        normalizeCodigo(value) {
            return String(value || '').toUpperCase().replace(/[^A-Z0-9]/g, '')
        },
    },
}
</script>
