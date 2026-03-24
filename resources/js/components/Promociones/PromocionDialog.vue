<template>
    <v-dialog v-model="model" max-width="600">
        <v-card rounded="xl">

            <!-- TITLE -->
            <v-card-title class="text-subtitle-1 font-weight-bold">
                {{ form.id ? 'Editar' : 'Nueva' }} Promoción
            </v-card-title>

            <!-- CONTENT -->
            <v-card-text>

                <!-- NOMBRE -->
                <v-text-field v-model="form.nombre" label="Nombre" density="compact" variant="outlined" />

                <!-- TIPO -->
                <v-select v-model="form.tipo" :items="tipos" label="Tipo" density="compact" variant="outlined" />

                <!-- VALOR -->
                <v-text-field v-model="form.valor" :label="form.tipo === 'porcentaje' ? 'Porcentaje (%)' : 'Monto (Q)'"
                    type="number" density="compact" variant="outlined" :min="form.tipo === 'porcentaje' ? 1 : 0"
                    :max="form.tipo === 'porcentaje' ? 100 : null" :error="errorValor"
                    :error-messages="errorValor ? 'Debe estar entre 1 y 100' : ''" />

                <!-- FECHAS -->
                <v-row>
                    <v-col>
                        <v-text-field v-model="form.fecha_inicio" type="date" label="Fecha inicio" density="compact"
                            variant="outlined" />
                    </v-col>

                    <v-col>
                        <v-text-field v-model="form.fecha_fin" type="date" label="Fecha fin" density="compact"
                            variant="outlined" />
                    </v-col>
                </v-row>

                <!-- APLICA A -->
                <v-select v-model="form.aplica_a" :items="aplicaOptions" label="Aplica a" density="compact"
                    variant="outlined" />

                <!-- PRODUCTOS -->
                <v-autocomplete v-if="form.aplica_a === 'producto'" v-model="form.productos" :items="productos"
                    item-title="nombre" item-value="id" label="Productos" multiple chips density="compact"
                    variant="outlined" />

                <!-- ACTIVO -->
                <v-switch v-model="form.activo" label="Activo" color="green" inset />

            </v-card-text>

            <!-- ACTIONS -->
            <v-card-actions class="px-4 pb-4">
                <v-spacer />

                <v-btn variant="tonal" color="red" @click="close">
                    Cancelar
                </v-btn>

                <v-btn color="success" variant="tonal" :loading="loading" @click="guardar">
                    Guardar
                </v-btn>

            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<script>
import axios from 'axios'

export default {
    name: 'PromocionDialog',

    props: {
        modelValue: Boolean,
        promocion: Object
    },

    emits: ['update:modelValue', 'saved'],

    data() {
        return {
            form: this.getDefaultForm(),
            loading: false,

            tipos: ['porcentaje', 'monto'],
            aplicaOptions: ['producto', 'carrito'],

            productos: []
        }
    },

    computed: {
        model: {
            get() {
                return this.modelValue
            },
            set(val) {
                this.$emit('update:modelValue', val)
            }
        },

        errorValor() {
            return this.form.tipo === 'porcentaje' &&
                (this.form.valor < 1 || this.form.valor > 100)
        }
    },

    watch: {
        modelValue(val) {
            if (val) {
                this.initForm()
                this.fetchProductos()
            }
        },

        promocion: {
            handler() {
                this.initForm()
            },
            deep: true
        },

        'form.tipo'(val) {
            if (val === 'porcentaje') {
                if (this.form.valor > 100) this.form.valor = 100
                if (this.form.valor < 1) this.form.valor = 1
            }
        }
    },

    methods: {

        getDefaultForm() {
            return {
                id: null,
                nombre: '',
                tipo: 'porcentaje',
                valor: 1,
                fecha_inicio: '',
                fecha_fin: '',
                aplica_a: 'producto',
                productos: [],
                activo: true
            }
        },

        initForm() {
            if (this.promocion) {
                this.form = {
                    ...this.promocion,

                    // FORMATEAR FECHAS
                    fecha_inicio: this.formatDate(this.promocion.fecha_inicio),
                    fecha_fin: this.formatDate(this.promocion.fecha_fin),

                    productos: this.promocion.productos?.map(p => p.id) || []
                }
            } else {
                this.form = this.getDefaultForm()
            }
        },

        async fetchProductos() {
            try {
                const res = await axios.get('/productoPromocion')
                this.productos = res.data
                console.log(this.productos);
            } catch (err) {
                console.error(err)
            }
        },

        async guardar() {
            if (this.errorValor) return

            this.loading = true

            try {
                if (this.form.id) {
                    await axios.put(`/promocion/${this.form.id}`, this.form)
                } else {
                    await axios.post('/promocion', this.form)
                }

                this.$emit('saved')
                this.close()

            } catch (err) {
                console.error(err)
            } finally {
                this.loading = false
            }
        },

        close() {
            this.$emit('update:modelValue', false)
        },

        formatDate(date) {
            if (!date) return ''
            return date.split('T')[0] || date.split(' ')[0]
        }
    }
}
</script>