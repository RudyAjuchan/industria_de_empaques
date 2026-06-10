<template>
    <v-dialog v-model="open" max-width="400">
        <v-card>
            <v-card-title>
                {{ form.id ? 'Editar página' : 'Nueva página' }}
            </v-card-title>

            <v-card-text>
                <v-text-field v-model="form.nombre" variant="outlined" density="compact" label="Nombre" autofocus
                    :error-messages="errors.nombre" />
                <v-text-field v-model="form.codigo" variant="outlined" density="compact" label="Código (opcional)"
                    hint="Ejemplo: JPL, MB" persistent-hint :error-messages="errors.codigo" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" @click="close" color="error">Cancelar</v-btn>
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
    name: 'PaginaDialog',

    props: {
        modelValue: Boolean,
        tipo: Object, // null o existente
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            open: this.modelValue,
            saving: false,
            errors: {},
            form: {
                id: null,
                nombre: '',
                codigo: '',
            },
        }
    },

    watch: {
        modelValue(val) {
            this.open = val
            if (val) this.load()
        },
    },

    methods: {
        load() {
            this.form = this.tipo
                ? { id: this.tipo.id, nombre: this.tipo.nombre, codigo: this.tipo.codigo || '' }
                : { id: null, nombre: '', codigo: '' }
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
                let res
                if (this.form.id) {
                    res = await axios.put(`/pagina/${this.form.id}`, this.form)
                } else {
                    res = await axios.post('/pagina', this.form)
                }

                this.$emit('saved', res.data)
                this.close()
            } catch (e) {
                this.errors = e.response?.data?.errors || {}
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
