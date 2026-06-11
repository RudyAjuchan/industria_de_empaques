<template>
    <v-card-text>
        <v-row>
            <v-col cols="4">
                <v-text-field variant="outlined" density="compact" label="Fecha emisión" v-model="fecha" disabled
                    hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field variant="outlined" density="compact" label="Hora" v-model="hora" disabled
                    hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field type="date" variant="outlined" density="compact" label="Fecha de entrega"
                    v-model="form.fecha_entrega" hide-details="auto"
                    :error-messages="errors.fecha_entrega"></v-text-field>
            </v-col>
        </v-row>
        <v-row class="justify-center bg-teal-lighten-5">
            <v-col cols="4">
                <v-text-field label="Vendedor" v-model="form.nombre_vendedor" variant="outlined" density="compact"
                    disabled hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-autocomplete v-model="form.paginas_id" :items="paginas" item-title="nombre" item-value="id"
                    label="Página" variant="outlined" density="compact" :error-messages="errors.paginas_id">
                    <template #append-inner>
                        <v-btn icon size="small" variant="text" @click.stop="paginaDialog = true">
                            <v-icon size="18">mdi-plus</v-icon>
                        </v-btn>
                    </template>
                </v-autocomplete>
                <PaginaDialog v-model="paginaDialog" @saved="onPaginaSaved" />
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="6">
                <h2>Datos del cliente</h2>
                <v-autocomplete label="Cliente" v-model="cliente" v-model:search="searchCliente" :items="clientes"
                    item-title="nombre" variant="outlined" density="compact" :loading="loadingClientes" hide-no-data
                    no-filter return-object @update:model-value="setDataCliente" :error-messages="errors.clientes_id">
                    <template #append-inner>
                        <v-btn icon size="small" variant="text" @click.stop="clienteDialog = true">
                            <v-icon size="18">mdi-plus</v-icon>
                        </v-btn>
                    </template>
                </v-autocomplete>
                <ClientesDialog v-model="clienteDialog" @saved="onClienteSaved" @cancel="clienteDialog = false">
                </ClientesDialog>

                <v-text-field label="Nombre" variant="outlined" density="compact" disabled
                    v-model="cliente.nombre"></v-text-field>
                <v-text-field label="Teléfono" variant="outlined" density="compact" disabled
                    v-model="cliente.telefono"></v-text-field>
                <v-text-field label="Dirección" variant="outlined" density="compact" disabled
                    v-model="cliente.direccion"></v-text-field>
                <v-text-field label="Nit" variant="outlined" density="compact" disabled
                    v-model="cliente.nit"></v-text-field>
            </v-col>
            <v-col cols="6">
                <h2>Datos de pago</h2>
                <!-- PARA EL BANCO -->
                <v-select label="Banco" :items="bancos" item-title="nombre" item-value="id" v-model="form.bancos_id"
                    variant="outlined" density="compact" :disabled="!tieneDeposito" :error-messages="errors.bancos_id">
                    <template #append-inner>
                        <v-btn icon size="small" variant="text" @click.stop="openBancoDialog">
                            <v-icon size="18">mdi-plus</v-icon>
                        </v-btn>
                    </template>
                </v-select>
                <!-- Dialog -->
                <BancoDialog v-model="bancoDialog" :tipo="null" @saved="onBancoSaved" />
                <!-- FINAL PARA EL BANCO -->
                <v-select label="Tipo pago" :items="tipoPago" item-title="nombre" item-value="nombre"
                    v-model="form.tipo_pago" variant="outlined" density="compact" :disabled="!tieneDeposito"
                    :error-messages="errors.tipo_pago" />
                <v-text-field label="No. Depósito" density="compact" variant="outlined"
                    v-model="form.no_deposito" :disabled="!tieneDeposito"></v-text-field>
                <MoneyInput label="Cantidad depósito" density="compact" variant="outlined"
                    v-model="form.cantidad_deposito" :error-messages="errors.cantidad_deposito" />
                <v-file-input v-model="comprobantePago" label="Comprobante de pago"
                    accept="image/*,.pdf" prepend-icon="mdi-paperclip" variant="outlined" density="compact"
                    :disabled="!tieneDeposito" :error-messages="errors.comprobante_pago" />
                <v-alert v-if="!tieneDeposito" type="info" variant="tonal" density="compact" class="mb-4">
                    La venta quedará pendiente de pago y podrá abonarse desde el módulo de pagos.
                </v-alert>
                <MoneyInput label="Pendiente a pagar" density="compact" variant="outlined" :model-value="pendientePagar"
                    disabled />
            </v-col>
        </v-row>

        <VentasDetalleTable v-if="ready && form.detalle" :productos="productos" :tiposAgarrador="tiposAgarrador" :tiposPapel="tiposPapel"
            v-model="form.detalle" @producto-saved="onProductoSaved" @agarrador-saved="onAgarradorSaved"
            @papel-saved="onPapelSaved" @search-productos="buscarProductos" :errors="errors" :modo="modo"
            @retry-upload="retryUpload" :loading="loading" :productos-loading="loadingProductos"/>
        <v-row class="mt-5">
            <v-col cols="4" class="ga-2 d-flex align-end">
                <v-btn color="success" variant="tonal" @click="guardarVenta" :loading="loading">
                    {{ modo === 'editar' ? 'Confirmar para venta' : 'Guardar Venta' }}
                </v-btn>
                <v-btn color="error" variant="tonal" @click="handleCancel" :loading="loading">
                    Cancelar
                </v-btn>
            </v-col>
            <v-col cols="4">
                <MoneyInput label="Costo Logo" v-model="form.costo_logo" variant="outlined" density="compact"
                    :error-messages="errors.costo_logo" />
            </v-col>
            <v-col cols="4">
                <MoneyInput label="Subtotal" :model-value="subtotalCalculado" disabled variant="outlined"
                    density="compact" />
                <MoneyInput label="Descuento" v-model="form.descuento" variant="outlined" density="compact"
                    :error-messages="errors.descuento" />

                <v-card class="discount-summary-card mb-4" variant="tonal">
                    <div class="discount-summary-title">
                        <v-icon size="18">mdi-ticket-percent-outline</v-icon>
                        Descuentos aplicados
                    </div>

                    <div class="discount-row">
                        <span>Productos</span>
                        <strong>{{ formatQuetzales(descuentoProductosMonto) }}</strong>
                    </div>

                    <div v-if="promocionesProductoAplicadas.length" class="discount-product-list">
                        <v-chip v-for="promo in promocionesProductoAplicadas" :key="promo.key" size="x-small"
                            color="red" variant="tonal">
                            {{ promo.nombre }} -{{ promo.valor }}
                        </v-chip>
                    </div>

                    <div class="discount-row">
                        <span>Carrito</span>
                        <strong>{{ formatQuetzales(promocionCarritoMonto) }}</strong>
                    </div>

                    <div v-if="form.promociones" class="discount-caption">
                        {{ form.promociones.nombre }}
                        <span v-if="form.promociones.tipo === 'porcentaje'">
                            ({{ form.promociones.valor }}%)
                        </span>
                        <span v-else>
                            ({{ formatQuetzales(form.promociones.valor) }})
                        </span>
                    </div>

                    <MoneyInput v-else label="Promoción carrito" v-model="form.promociones_monto" variant="outlined"
                        density="compact" hide-details />

                    <div class="discount-row">
                        <span>Manual</span>
                        <strong>{{ formatQuetzales(descuentoManualMonto) }}</strong>
                    </div>

                    <v-divider class="my-2" />

                    <div class="discount-row discount-total">
                        <span>Total descuentos</span>
                        <strong>{{ formatQuetzales(totalDescuentosMonto) }}</strong>
                    </div>
                </v-card>

                <MoneyInput label="Costo Envío" v-model="form.costo_envio" variant="outlined" density="compact"
                    :error-messages="errors.costo_envio" />
                <MoneyInput label="Total" :model-value="totalCalculado" readonly variant="outlined" density="compact"
                    disabled />
            </v-col>
        </v-row>

    </v-card-text>
</template>

<script>
import axios from 'axios'
import VentasDetalleTable from './VentasDetalleTable.vue'
import BancoDialog from '../Bancos/BancoDialog.vue';
import ClientesDialog from '../Clientes/ClientesDialog.vue';
import PaginaDialog from '../Paginas/PaginaDialog.vue';
import MoneyInput from '../common/MoneyInput.vue'
import { formatQuetzales, parseMoney } from '../../utils/money'
import { toast } from 'vue3-toastify'

export default {
    components: { VentasDetalleTable, BancoDialog, ClientesDialog, PaginaDialog, MoneyInput },
    emits: ['saved', 'cancel'],
    data() {
        return {
            loading: false,
            searchCliente: '',
            loadingClientes: false,
            loadingProductos: false,
            debounceCliente: null,
            debounceProductos: null,
            clientes: [],
            cliente: {
                nombre: null,
                telefono: null,
                direccion: null,
                nit: null,
            },
            clienteDialog: false,
            productos: [],
            paginas: [],
            paginaDialog: false,
            bancos: [],
            bancoDialog: false,
            tiposAgarrador: [],
            tiposPapel: [],
            tipoPago: [
                { nombre: 'Efectivo' },
                { nombre: 'Pago con tarjeta' },
                { nombre: 'Depósito' },
                { nombre: 'Transferencia' },
                { nombre: 'Cheque' },
                { nombre: 'Transferencia Internacional' },
            ],

            form: {
                vendedor_id: AUTH_USER.id,
                nombre_vendedor: AUTH_USER.name,
                clientes_id: null,
                paginas_id: null,
                bancos_id: null,
                fecha_entrega: null,
                serie: '',
                numero: '',
                fecha_entrega: '',
                no_deposito: '',
                cantidad_deposito: 0,
                costo_logo: 0,
                descuento: 0,
                costo_envio: 0,
                proceso_estado_produccions_id: 1,
                detalle: [],
                tipo_pago: null,
                promociones: null,
                promociones_monto: 0,
            },
            fecha: '',
            hora: '',
            timer: null,
            user: window.AUTH_USER || {},
            errors: {},
            modo: '',
            venta: null,
            ready: false,
            comprobantePago: null,
        }
    },

    methods: {
        formatQuetzales,

        async loadCatalogos() {
            //this.clientes = (await axios.get('/cliente')).data
            this.bancos = (await axios.get('/banco')).data
            this.paginas = (await axios.get('/listar/paginas')).data
            this.tiposAgarrador = (await axios.get('/agarrador')).data
            this.tiposPapel = (await axios.get('/tipo-papel')).data
            await this.buscarProductos('')
        },

        async guardarVenta() {
            this.loading = true

            try {

                this.form.subtotal = this.subtotalCalculado
                this.form.total = this.totalCalculado
                this.form.pendiente_pagar = this.pendientePagar

                const formData = new FormData()

                Object.entries(this.form).forEach(([key, value]) => {
                    if (['detalle', 'promociones'].includes(key)) return;

                    if (value !== null && value !== undefined) {
                        formData.append(key, value);
                    }
                });

                if (this.form.promociones?.id) {
                    formData.append('promociones_id', this.form.promociones.id);
                }

                const comprobante = Array.isArray(this.comprobantePago)
                    ? this.comprobantePago[0]
                    : this.comprobantePago

                if (comprobante) {
                    formData.append('comprobante_pago', comprobante)
                }

                this.form.detalle.forEach((item, index) => {

                    Object.entries(item).forEach(([key, value]) => {

                        if ([
                            'producto',
                            'imagenes',
                            'archivo_diseno_file',
                            'upload_progress',
                            'upload_status',
                            'upload_error',
                            'alto',
                            'ancho',
                            'fuelle',
                            'tipo'
                        ].includes(key)) return

                        if (value !== null && value !== undefined) {
                            formData.append(`detalle[${index}][${key}]`, value)
                        }
                    })

                    // IMÁGENES
                    if (item.imagenes?.length) {
                        item.imagenes.forEach((img, i) => {
                            /*
                            |--------------------------------------------------------------------------
                            | EXISTENTE
                            |--------------------------------------------------------------------------
                            */
                            if (img.uploaded) {
                                formData.append(
                                    `detalle[${index}][imagenes][${i}][path]`,
                                    img.path
                                )
                                formData.append(
                                    `detalle[${index}][imagenes][${i}][uploaded]`,
                                    true
                                )

                                return
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | NUEVA
                            |--------------------------------------------------------------------------
                            */
                            if (img.file) {
                                formData.append(
                                    `detalle[${index}][imagenes][${i}]`,
                                    img.file,
                                    img.file.name
                                )
                            }
                        })
                    }
                })

                // GUARDAR VENTA
                let response

                if (this.modo === 'editar') {
                    response = await axios.post(`/venta/${this.form.id}?_method=PUT`, formData)
                } else {
                    response = await axios.post('/venta', formData)
                }

                const venta = response.data.venta
                venta.detalles.forEach((detalleBackend, i) => {
                    this.form.detalle[i].id = detalleBackend.id
                })
                // LOGICA PARA GUARDAR PSD
                for (let i = 0; i < venta.detalles.length; i++) {

                    const detalleBackend = venta.detalles[i]
                    const detalleFront = this.form.detalle[i]

                    if (detalleFront.archivo_diseno_file) {

                        await this.subirDiseno(
                            detalleBackend.id,
                            detalleFront.archivo_diseno_file,
                            detalleFront // PASAMOS EL ITEM
                        )
                    }
                }

                // SUCCESS
                toast.success(
                    this.modo === 'editar'
                        ? 'Cotización actualizada'
                        : 'Venta registrada'
                )

                this.$router.push('/ventas')

            } catch (e) {

                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors
                    toast.error('Revisa los campos marcados')
                } else {
                    console.error(e)
                    toast.error('Error inesperado')
                }

            } finally {
                this.loading = false
            }
        },
        actualizarReloj() {
            const ahora = new Date();

            this.fecha = ahora.toLocaleDateString('es-ES', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });

            this.hora = ahora.toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        },
        async buscarClientes(q) {
            this.loadingClientes = true
            try {
                const { data } = await axios.get('/client/search', {
                    params: { q }
                })
                this.clientes = data
            } finally {
                this.loadingClientes = false
            }
        },
        getProductosSeleccionados() {
            return this.form.detalle
                .map(d => d.productos_id)
                .filter(Boolean)
        },
        buscarProductos(q = '') {
            clearTimeout(this.debounceProductos)

            this.debounceProductos = setTimeout(async () => {
                this.loadingProductos = true

                try {
                    const { data } = await axios.post('/product/search', { q })
                    this.mergeProductos(data)
                } finally {
                    this.loadingProductos = false
                }
            }, q ? 350 : 0)
        },

        mergeProductos(nuevosProductos) {
            const seleccionados = this.form.detalle
                .map(d => d.producto)
                .filter(p => p)

            const productos = [
                ...nuevosProductos.filter(Boolean),
                ...seleccionados,
            ]

            this.productos = productos.filter((producto, index, arr) => (
                producto && arr.findIndex(p => p.id === producto.id) === index
            ))
        },

        openBancoDialog() {
            this.bancoSeleccionado = null
            this.bancoDialog = true
        },

        onBancoSaved(banco) {
            this.bancos.push(banco)
            this.form.bancos_id = banco.id
            toast.success('Banco guardado')
        },
        onPaginaSaved(pagina) {
            this.paginas.push(pagina)
            this.form.paginas_id = pagina.id
            toast.success('Página guardada')
        },
        onClienteSaved(cliente) {
            if (!this.clientes.find(c => c.id === cliente.id)) {
                this.clientes.push(cliente)
            }

            this.cliente = cliente

            this.setDataCliente(cliente)

            toast.success('Cliente guardado')
        },

        onProductoSaved(producto) {
            if (!this.productos.find(p => p.id === producto.id)) {
                this.productos.push(producto)
            }
        },
        onAgarradorSaved(agarrador) {
            if (!this.tiposAgarrador.find(p => p.id === agarrador.id)) {
                this.tiposAgarrador.push(agarrador)
            }
        },
        onPapelSaved(papel) {
            if (!this.tiposPapel.find(p => p.id === papel.id)) {
                this.tiposPapel.push(papel)
            }
        },

        setDataCliente(item) {
            if (!item) {
                this.form.clientes_id = null;
                this.cliente = {
                    nombre: null,
                    telefono: null,
                    direccion: null,
                    nit: null,
                };
                return;
            }

            this.form.clientes_id = item.id;

            const direccionParts = item.municipio
                ? [
                    item.direccion,
                    item.municipio?.nombre,
                    item.municipio?.departamento?.nombre
                ]
                : [
                    item.direccion,
                    item.ciudad_pais,
                    item.estado_pais
                ];

            this.cliente = {
                nombre: item.nombre,
                nit: item.nit,
                telefono: (item.telefonos || [])
                    .map(t => `${t.telefono_codigo_pais} ${t.telefono_numero}`)
                    .join(' / '),
                direccion: direccionParts
                    .filter(p => p && p.toString().trim() !== '')
                    .join(', ') || 'Sin dirección',
            };
        },

        async getVenta(id) {
            try {
                const { data } = await axios.get(`/ecommerce/${id}`)
                this.venta = data
                this.cargarVenta()
            } catch (error) {
                console.error(error)
                toast.error('Error cargando cotización')
            }
        },

        cargarVenta() {
            this.form = {
                ...this.form,
                ...this.venta,
                detalle: (this.venta.detalle || []).map(item => ({
                    ...item,
                    /*
                    |--------------------------------------------------------------------------
                    | IMÁGENES
                    |--------------------------------------------------------------------------
                    */
                    imagenes: (item.imagenes || []).map(img => ({
                        id: img.id,
                        path: img.path,
                        url: this.getFileUrl(img.path),
                        /*
                        |--------------------------------------------------------------------------
                        | FILEPOND
                        |--------------------------------------------------------------------------
                        */
                        source: img.path,
                        options: {
                            type: 'local'
                        },
                        uploaded: true,
                        file: null
                    }))
                }))
            }

            this.setDataCliente(this.venta.cliente)
            this.agregarProductosSeleccionados()
            this.setDatosProductos()
        },

        getFileUrl(path) {
            const baseUrl = import.meta.env.VITE_CDN_URL || ''
            return `${baseUrl.replace(/\/$/, '')}/${path}`
        },

        agregarProductosSeleccionados() {
            const productosDetalle = this.form.detalle
                .map(d => d.producto)
                .filter(p => p)
            productosDetalle.forEach(prod => {
                if (!this.productos.find(p => p.id === prod.id)) {
                    this.productos.push(prod)
                }
            })
        },

        setDatosProductos() {
            this.form.detalle.forEach(item => {

                const producto = this.productos.find(p => p.id === item.productos_id)

                if (producto) {
                    item.alto = producto.alto
                    item.ancho = producto.ancho
                    item.fuelle = producto.fuelle
                    item.tipo = producto.tipo
                }
            })
        },

        handleCancel() {
            if (this.modo === 'editar') {
                this.$router.push('/ecommerce')
            } else {
                this.$emit('cancel')
            }
        },

        calcularPromocionCarrito() {
            if (!this.form.promociones) {
                this.form.promociones_monto = 0
                return
            }

            const promo = this.form.promociones
            const subtotal = this.subtotalCalculado

            if (promo.tipo === 'porcentaje') {
                this.form.promociones_monto = subtotal * (promo.valor / 100)
            } else {
                this.form.promociones_monto = promo.valor
            }
        },
        async retryUpload(index) {
            const item = this.form.detalle[index]

            if (!item.archivo_diseno_file || !item.id) return

            await this.subirDiseno(
                item.id,
                item.archivo_diseno_file,
                item
            )
        },

        async subirDiseno(detalleId, file, item) {

            try {

                item.upload_status = 'subiendo'
                item.upload_progress = 0
                item.upload_error = null

                // 1. pedir URL firmada
                const { data } = await axios.post('/s3/presigned-url', {
                    filename: file.name,
                    content_type: file.type
                })

                // 2. subir con progreso
                await new Promise((resolve, reject) => {

                    const xhr = new XMLHttpRequest()

                    xhr.open('PUT', data.url)

                    xhr.setRequestHeader('Content-Type', file.type)

                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            item.upload_progress = Math.round((e.loaded / e.total) * 100)
                        }
                    }

                    xhr.onload = () => {
                        if (xhr.status === 200) {
                            resolve()
                        } else {
                            reject('Error al subir a S3')
                        }
                    }

                    xhr.onerror = () => reject('Error de red')

                    xhr.send(file)
                })

                // 3. guardar path
                await axios.post(`/detalle/${detalleId}/guardar-diseno`, {
                    path: data.path
                })

                item.upload_status = 'completado'

            } catch (err) {

                console.error(err)

                item.upload_status = 'error'
                item.upload_error = err

                throw err
            }
        },
    },

    watch: {
        subtotalCalculado() {
            this.calcularPromocionCarrito()
        },
        'form.promociones': {
            handler() {
                this.calcularPromocionCarrito()
            },
            deep: true
        },
        'form.cantidad_deposito'(value) {
            if ((parseMoney(value) ?? 0) > 0) return

            this.form.bancos_id = null
            this.form.tipo_pago = null
            this.form.no_deposito = ''
            this.comprobantePago = null
        },

        searchCliente(val) {

            if (!val || val.length < 1) {
                this.clientes = []
                return
            }

            clearTimeout(this.debounceCliente)

            this.debounceCliente = setTimeout(() => {
                this.buscarClientes(val)
            }, 800)
        },
    },

    computed: {
        subtotalCalculado() {
            if (!Array.isArray(this.form.detalle)) return 0

            return this.form.detalle.reduce((s, i) => s + parseFloat(i.total || 0), 0)
        },
        subtotalBrutoProductos() {
            if (!Array.isArray(this.form.detalle)) return 0

            return this.form.detalle.reduce((total, item) => {
                const precio = parseMoney(item.precio) ?? 0
                const cantidad = parseFloat(item.cantidad || 0)

                return total + (precio * cantidad)
            }, 0)
        },
        descuentoProductosMonto() {
            return Math.max(0, this.subtotalBrutoProductos - this.subtotalCalculado)
        },
        promocionCarritoMonto() {
            return parseMoney(this.form.promociones_monto) ?? 0
        },
        descuentoManualMonto() {
            return parseMoney(this.form.descuento) ?? 0
        },
        totalDescuentosMonto() {
            return this.descuentoProductosMonto + this.promocionCarritoMonto + this.descuentoManualMonto
        },
        promocionesProductoAplicadas() {
            if (!Array.isArray(this.form.detalle)) return []

            const promociones = new Map()

            this.form.detalle.forEach((item, index) => {
                const promo = item.promocion_aplicada

                if (!promo) return

                const key = promo.id || `${promo.nombre}-${promo.tipo}-${promo.valor}-${index}`
                const valor = promo.tipo === 'porcentaje'
                    ? `${promo.valor}%`
                    : this.formatQuetzales(promo.valor)

                promociones.set(key, {
                    key,
                    nombre: promo.nombre,
                    valor,
                })
            })

            return [...promociones.values()]
        },
        totalCalculado() {
            const sub = this.subtotalCalculado;
            const logo = parseMoney(this.form.costo_logo) ?? 0;
            const descuento = parseMoney(this.form.descuento) ?? 0;
            const promo = parseMoney(this.form.promociones_monto) ?? 0;
            const envio = parseMoney(this.form.costo_envio) ?? 0;

            return (sub + logo + envio) - (descuento + promo);
        },
        pendientePagar() {
            const total = parseMoney(this.totalCalculado) ?? 0;
            const totalDeposito = parseMoney(this.form.cantidad_deposito) ?? 0;
            return total - totalDeposito;
        },
        tieneDeposito() {
            return (parseMoney(this.form.cantidad_deposito) ?? 0) > 0
        }
    },

    async mounted() {
        await this.loadCatalogos();
        const id = this.$route.params.id
        if (id) {
            this.modo = 'editar'
            await this.getVenta(id) // esperar
        }
        this.ready = true
        // PARA HORA
        this.actualizarReloj();
        this.timer = setInterval(() => {
            this.actualizarReloj();
        }, 1000);

        //para promociones
        if (this.modo !== 'editar') {
            const { data } = await axios.get('/api/ecommerce/promociones-carrito')

            const promo = data[0] || null

            if (promo) {
                this.form.promociones = promo
                this.calcularPromocionCarrito()
            }
        }
    },

    beforeUnmount() {//SOLO PARA HORA
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
}
</script>

<style scoped>
.discount-summary-card {
    padding: 12px;
    border: 1px solid #dbe8e5;
    background: #f5fbfa;
}

.discount-summary-title {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    color: #0f5f56;
    font-size: 0.85rem;
    font-weight: 700;
}

.discount-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 3px 0;
    font-size: 0.85rem;
}

.discount-total {
    color: #0f5f56;
    font-size: 0.9rem;
}

.discount-caption {
    margin: -1px 0 5px;
    color: #5c6f6b;
    font-size: 0.75rem;
}

.discount-product-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin: 2px 0 6px;
}
</style>
