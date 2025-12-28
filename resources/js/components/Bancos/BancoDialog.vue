<template>
    <v-dialog v-model="open" max-width="400">
        <v-card>
            <v-card-title>
                {{ form.id ? 'Editar banco' : 'Nuevo banco' }}
            </v-card-title>

            <v-card-text>
                <v-text-field v-model="form.nombre" variant="outlined" density="compact" label="Nombre" autofocus :error-messages="error" />
            </v-card-text>

            <v-card-actions>
                <v-spacer />
                <v-btn variant="tonal" @click="close" color="red">Cancelar</v-btn>
                <v-btn color="green" variant="tonal" :loading="saving" @click="save">
                    Guardar
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios'

export default {
    name: 'BancoDialog',

    props: {
        modelValue: Boolean,
        tipo: Object, // null o existente
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            open: this.modelValue,
            saving: false,
            error: null,
            form: {
                id: null,
                nombre: '',
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
                ? { id: this.tipo.id, nombre: this.tipo.nombre }
                : { id: null, nombre: '' }
            this.error = null
        },

        close() {
            this.$emit('update:modelValue', false)
        },

        async save() {
            this.error = null
            this.saving = true

            try {
                let res
                if (this.form.id) {
                    res = await axios.put(`/banco/${this.form.id}`, this.form)
                } else {
                    res = await axios.post('/banco', this.form)
                }

                this.$emit('saved', res.data)
                this.close()
            } catch (e) {
                this.error = e.response?.data?.errors?.nombre?.[0] ?? null
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
