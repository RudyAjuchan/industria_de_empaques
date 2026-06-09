<template>
    <v-dialog v-model="model" max-width="980">
        <v-card rounded="xl">

            <!-- TITLE -->
            <v-card-title class="text-subtitle-1 font-weight-bold">
                {{ form.id ? 'Editar' : 'Nueva' }} Promoción
            </v-card-title>

            <!-- CONTENT -->
            <v-card-text>

                <!-- NOMBRE -->
                <v-text-field v-model="form.nombre" label="Nombre" density="compact" variant="outlined" :error-messages="errors.nombre"/>

                <!-- TITULO -->
                <v-text-field v-model="form.titulo" label="Título" density="compact" variant="outlined" :error-messages="errors.titulo"/>

                <!-- DESCRIPCION -->
                <v-textarea v-model="form.descripcion" label="Descripción" rows="3" auto-grow density="compact"
                    variant="outlined" :error-messages="errors.descripcion"/>

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
                            variant="outlined" :error-messages="errors.fecha_inicio" />
                    </v-col>

                    <v-col>
                        <v-text-field v-model="form.fecha_fin" type="date" label="Fecha fin" density="compact"
                            variant="outlined" :error-messages="errors.fecha_fin"/>
                    </v-col>
                </v-row>

                <!-- APLICA A -->
                <v-select v-model="form.aplica_a" :items="aplicaOptions" label="Aplica a" density="compact"
                    variant="outlined" />

                <!-- PRODUCTOS -->
                <div v-if="form.aplica_a === 'producto'">
                    <v-alert v-if="errors.productos" type="error" variant="tonal" density="compact" class="mb-3">
                        {{ Array.isArray(errors.productos) ? errors.productos[0] : errors.productos }}
                    </v-alert>

                    <div class="d-flex align-center justify-space-between mb-2">
                        <div class="text-subtitle-2">
                            Productos seleccionados: {{ form.productos.length }}
                        </div>

                        <div class="d-flex ga-2">
                            <v-btn size="small" variant="tonal" color="primary" @click="seleccionarPagina">
                                Seleccionar página
                            </v-btn>
                            <v-btn size="small" variant="tonal" color="teal" @click="seleccionarFiltrados">
                                Seleccionar filtrados
                            </v-btn>
                            <v-btn size="small" variant="tonal" color="error" @click="limpiarSeleccion">
                                Limpiar
                            </v-btn>
                        </div>
                    </div>

                    <v-row dense>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="productoSearch" label="Buscar producto" density="compact"
                                variant="outlined" prepend-inner-icon="mdi-magnify" hide-details />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-select v-model="paginaFiltro" :items="paginaOptions" label="Página" density="compact"
                                variant="outlined" hide-details clearable />
                        </v-col>
                        <v-col cols="12" md="3">
                            <v-select v-model="tipoFiltro" :items="tipoOptions" label="Tipo" density="compact"
                                variant="outlined" hide-details clearable />
                        </v-col>
                    </v-row>

                    <v-data-table v-model:page="productoPage" :headers="productoHeaders" :items="productosFiltrados"
                        :items-per-page="productosPorPagina" density="compact" fixed-header height="320" class="mt-3"
                        :loading="loadingProductos">
                        <template v-slot:[`item.selected`]="{ item }">
                            <v-checkbox-btn :model-value="productoSeleccionado(item.id)"
                                @update:model-value="toggleProducto(item.id, $event)" />
                        </template>

                        <template v-slot:[`item.paginas.nombre`]="{ item }">
                            {{ item.paginas?.nombre || '-' }}
                        </template>

                        <template v-slot:[`item.ecommerce`]="{ item }">
                            <v-chip size="x-small" :color="item.ecommerce ? 'success' : 'grey'">
                                {{ item.ecommerce ? 'Sí' : 'No' }}
                            </v-chip>
                        </template>
                    </v-data-table>

                    <div v-if="productosSeleccionados.length" class="mt-3">
                        <div class="text-caption text-medium-emphasis mb-1">Selección actual</div>
                        <div class="d-flex flex-wrap ga-1">
                            <v-chip v-for="producto in productosSeleccionados.slice(0, 12)" :key="producto.id"
                                size="small" closable @click:close="toggleProducto(producto.id, false)">
                                {{ producto.nombre }}
                            </v-chip>
                            <v-chip v-if="productosSeleccionados.length > 12" size="small" color="primary">
                                +{{ productosSeleccionados.length - 12 }} más
                            </v-chip>
                        </div>
                    </div>
                </div>

                <!-- ACTIVO -->
                <!-- <v-switch v-model="form.activo" label="Activo" color="green" inset /> -->

            </v-card-text>

            <!-- ACTIONS -->
            <v-card-actions class="px-4 pb-4">
                <v-spacer />

                <v-btn variant="tonal" color="error" @click="close">
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
import { toast } from 'vue3-toastify'

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

            productos: [],
            loadingProductos: false,
            productoSearch: '',
            paginaFiltro: null,
            tipoFiltro: null,
            productoPage: 1,
            productosPorPagina: 10,
            productoHeaders: [
                { title: '', key: 'selected', sortable: false, width: 48 },
                { title: 'ID', key: 'id', width: 80 },
                { title: 'Producto', key: 'nombre' },
                { title: 'Tipo', key: 'tipo' },
                { title: 'Página', key: 'paginas.nombre' },
                { title: 'Ecommerce', key: 'ecommerce', width: 110 },
            ],
            errors: {},
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
        },

        productosFiltrados() {
            const search = this.productoSearch.trim().toLowerCase()

            return this.productos.filter(producto => {
                const matchesSearch = !search ||
                    producto.nombre?.toLowerCase().includes(search) ||
                    String(producto.id).includes(search) ||
                    producto.tipo?.toLowerCase().includes(search) ||
                    producto.tipo_producto?.toLowerCase().includes(search) ||
                    producto.paginas?.nombre?.toLowerCase().includes(search)

                const matchesPagina = !this.paginaFiltro ||
                    producto.paginas_id === this.paginaFiltro

                const matchesTipo = !this.tipoFiltro ||
                    producto.tipo === this.tipoFiltro

                return matchesSearch && matchesPagina && matchesTipo
            })
        },

        productosPaginaActual() {
            const start = (this.productoPage - 1) * this.productosPorPagina
            return this.productosFiltrados.slice(start, start + this.productosPorPagina)
        },

        productosSeleccionados() {
            const seleccionados = new Set(this.form.productos)
            return this.productos.filter(producto => seleccionados.has(producto.id))
        },

        paginaOptions() {
            const paginas = new Map()

            this.productos.forEach(producto => {
                if (producto.paginas_id && producto.paginas?.nombre) {
                    paginas.set(producto.paginas_id, producto.paginas.nombre)
                }
            })

            return Array.from(paginas, ([value, title]) => ({ title, value }))
                .sort((a, b) => a.title.localeCompare(b.title))
        },

        tipoOptions() {
            return [...new Set(this.productos
                .map(producto => producto.tipo)
                .filter(Boolean))]
                .sort()
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
        },

        'form.aplica_a'(val) {
            if (val !== 'producto') {
                this.form.productos = []
            }
        },

        productoSearch() {
            this.productoPage = 1
        },

        paginaFiltro() {
            this.productoPage = 1
        },

        tipoFiltro() {
            this.productoPage = 1
        }
    },

    methods: {

        getDefaultForm() {
            return {
                id: null,
                nombre: '',
                titulo: '',
                descripcion: '',
                tipo: 'porcentaje',
                valor: 1,
                fecha_inicio: '',
                fecha_fin: '',
                aplica_a: 'producto',
                productos: [],
                activo: true,
                errors: {},
            }
        },

        initForm() {
            this.errors = {}

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

            this.productoSearch = ''
            this.paginaFiltro = null
            this.tipoFiltro = null
            this.productoPage = 1
            this.normalizarProductosSeleccionados()
        },

        async fetchProductos() {
            try {
                this.loadingProductos = true
                const res = await axios.get('/productoPromocion')
                this.productos = res.data.map(producto => ({
                    ...producto,
                    id: Number(producto.id),
                    paginas_id: producto.paginas_id ? Number(producto.paginas_id) : null,
                }))
                this.normalizarProductosSeleccionados()
            } catch (err) {
                toast.error('No se pudieron cargar los productos')
            } finally {
                this.loadingProductos = false
            }
        },

        productoSeleccionado(productoId) {
            return this.form.productos.includes(productoId)
        },

        toggleProducto(productoId, selected = null) {
            const productoIdNumber = Number(productoId)
            const shouldSelect = selected === null ? !this.productoSeleccionado(productoIdNumber) : selected

            if (shouldSelect) {
                this.agregarProductos([productoIdNumber])
            } else {
                this.form.productos = this.form.productos.filter(id => id !== productoIdNumber)
            }
        },

        seleccionarPagina() {
            const ids = this.productosPaginaActual.map(producto => producto.id)
            this.agregarProductos(ids)
        },

        seleccionarFiltrados() {
            const ids = this.productosFiltrados.map(producto => producto.id)
            this.agregarProductos(ids)
        },

        agregarProductos(ids) {
            this.form.productos = [...new Set([
                ...this.form.productos.map(Number),
                ...ids.map(Number),
            ])]
        },

        limpiarSeleccion() {
            this.form.productos = []
        },

        normalizarProductosSeleccionados() {
            this.form.productos = [...new Set((this.form.productos || []).map(Number))]
        },

        async guardar() {
            if (this.errorValor) return

            this.loading = true

            try {
                this.normalizarProductosSeleccionados()
                const payload = {
                    ...this.form,
                    productos: this.form.aplica_a === 'producto'
                        ? this.form.productos
                        : undefined,
                }

                if (this.form.id) {
                    await axios.put(`/promocion/${this.form.id}`, payload)
                } else {
                    await axios.post('/promocion', payload)
                }

                this.$emit('saved')
                this.close()

            } catch (err) {
                this.errors = err.response?.data?.errors || {}
                if (!Object.keys(this.errors).length) {
                    toast.error(err.response?.data?.message || 'No se pudo guardar la promoción')
                }
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
