<template>
    <v-form @submit.prevent="submit">
        <v-row>
            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.nombre" label="Nombre" required :error-messages="errors.nombre" />
            </v-col>

            <v-col cols="12" md="6">
                <v-select variant="outlined" density="compact" v-model="form.genero" :items="['Masculino', 'Femenino']" label="Género" required
                    :error-messages="errors.genero" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.email" label="Email" type="email" required :error-messages="errors.email" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.telefono" label="Teléfono" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.dpi" label="DPI" :error-messages="errors.dpi"/>
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.nit" label="NIT" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.departamento" label="Departamento" />
            </v-col>

            <v-col cols="12" md="6">
                <v-text-field variant="outlined" density="compact" v-model="form.municipio" label="Municipio" />
            </v-col>

            <v-col cols="12">
                <v-textarea variant="outlined" density="compact" v-model="form.direccion" label="Dirección" rows="2" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12" class="d-flex justify-end ga-2">
                <v-btn variant="tonal" @click="$emit('cancel')" color="red">
                    Cancelar
                </v-btn>
                <v-btn color="success" variant="tonal" type="submit" :loading="saving">
                    Guardar
                </v-btn>
            </v-col>
        </v-row>
    </v-form>
</template>

<script>
import { toast } from 'vue3-toastify'

export default {
    name: 'ClienteForm',

    props: {
        cliente: {
            type: Object,
            default: null,
        },
    },

    emits: ['saved', 'cancel'],

    data() {
        return {
            saving: false,
            form: {
                id: null,
                nombre: '',
                genero: '',
                telefono: '',
                dpi: '',
                email: '',
                departamento: '',
                municipio: '',
                direccion: '',
                nit: '',
            },
            errors: {},
        }
    },

    watch: {
        cliente: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.form = { ...val }
                } else {
                    this.reset()
                }
            },
        },
    },

    methods: {
        reset() {
            this.form = {
                id: null,
                nombre: '',
                genero: '',
                telefono: '',
                dpi: '',
                email: '',
                departamento: '',
                municipio: '',
                direccion: '',
                nit: '',
            }
            this.errors = {}
        },

        async submit() {
            this.errors = {}
            this.saving = true

            try {
                let res
                if (this.form.id) {
                    res = await axios.put(`/cliente/${this.form.id}`, this.form)
                    toast.success('Cliente actualizado correctamente')
                } else {
                    res = await axios.post('/cliente', this.form)
                    toast.success('Cliente creado correctamente')
                }

                this.$emit('saved', res.data)
                this.reset()
            } catch (e) {
                this.errors = e.response?.data?.errors || {}
                toast.error('Error al guardar el cliente')
            } finally {
                this.saving = false
            }
        },
    },
}
</script>
