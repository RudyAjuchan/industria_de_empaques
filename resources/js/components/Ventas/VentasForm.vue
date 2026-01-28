<template>
    <v-card-text>
        <v-row>
            <v-col cols="4">
                <v-text-field variant="outlined" density="compact" label="Fecha emisión" v-model="fecha" disabled hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field variant="outlined" density="compact" label="Hora" v-model="hora" disabled hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field type="date" variant="outlined" density="compact" label="Fecha de entrega" v-model="form.fecha_entrega" hide-details="auto" 
                :error-messages="errors.fecha_entrega"></v-text-field>
            </v-col>
        </v-row>
        <v-row class="justify-center bg-teal-lighten-5">
            <v-col cols="4">
                <v-text-field label="Vendedor" v-model="form.nombre_vendedor" variant="outlined" density="compact" disabled hide-details="auto"></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-autocomplete variant="outlined" density="compact" :items="paginas" item-title="nombre" item-value="id" 
                hide-details="auto" label="Página" @update:model-value="listProducts"></v-autocomplete>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="6">
                <h2>Datos del cliente</h2>
                <v-autocomplete label="Cliente" v-model="cliente" v-model:search="searchCliente"
                    :items="clientes" item-title="nombre" variant="outlined" density="compact"
                    :loading="loadingClientes" hide-no-data no-filter return-object @update:model-value="setDataCliente" :error-messages="errors.clientes_id">
                    <template #append-inner>
                        <v-btn icon size="small" variant="text" @click.stop="clienteDialog = true">
                            <v-icon size="18">mdi-plus</v-icon>
                        </v-btn>
                    </template>
                </v-autocomplete>
                <ClientesDialog v-model="clienteDialog" @saved="onClienteSaved" @cancel="clienteDialog = false"></ClientesDialog>

                <v-text-field label="Nombre" variant="outlined" density="compact" disabled v-model="cliente.nombre"></v-text-field>
                <v-text-field label="Teléfono" variant="outlined" density="compact" disabled v-model="cliente.telefono"></v-text-field>
                <v-text-field label="Dirección" variant="outlined" density="compact" disabled v-model="cliente.direccion"></v-text-field>
                <v-text-field label="Nit" variant="outlined" density="compact" disabled v-model="cliente.nit"></v-text-field>
            </v-col>
            <v-col cols="6">
                <h2>Datos de pago</h2>
                <!-- PARA EL BANCO -->
                <v-select label="Banco" :items="bancos" item-title="nombre" item-value="id" v-model="form.bancos_id"
                    variant="outlined" density="compact" :error-messages="errors.bancos_id">
                    <template #append-inner>
                        <v-btn icon size="small" variant="text" @click.stop="openBancoDialog">
                            <v-icon size="18">mdi-plus</v-icon>
                        </v-btn>
                    </template>
                </v-select>
                <!-- Dialog -->
                <BancoDialog v-model="bancoDialog" :tipo="null" @saved="onBancoSaved" />
                <!-- FINAL PARA EL BANCO -->
                <v-select label="Tipo pago" :items="tipoPago" item-title="nombre" item-value="nombre" v-model="form.tipo_pago" variant="outlined" density="compact" :error-messages="errors.tipo_pago"/>
                <v-text-field label="No. Depósito" density="compact" variant="outlined" v-model="form.no_deposito"></v-text-field>
                <v-text-field label="Cantidad depósito" density="compact" variant="outlined" v-model="form.cantidad_deposito" :error-messages="errors.cantidad_deposito"></v-text-field>
                <v-text-field label="Pendiente a pagar" density="compact" variant="outlined" v-model="pendientePagar" disabled></v-text-field>
            </v-col>
        </v-row>

        <VentasDetalleTable :productos="productos" :tiposAgarrador="tiposAgarrador" :tiposPapel="tiposPapel"
            v-model="form.detalle" @producto-saved="onProductoSaved" @agarrador-saved="onAgarradorSaved"
            @papel-saved="onPapelSaved" :errors="errors"/>
        <v-row class="mt-5">
            <v-col cols="4" class="ga-2 d-flex align-end">
                <v-btn color="green" variant="tonal" @click="guardarVenta" :loading="loading">
                    Guardar Venta
                </v-btn>
                <v-btn color="red" variant="tonal" @click="$emit('cancel')" :loading="loading">
                    Cancelar
                </v-btn>
            </v-col>
            <v-col cols="4">
                <v-text-field label="Costo Logo" v-model="form.costo_logo" variant="outlined" density="compact" :error-messages="errors.costo_logo"/>
            </v-col>
            <v-col cols="4">
                <v-text-field label="Subtotal" :model-value="subtotalCalculado" disabled variant="outlined"
                    density="compact" />
                <v-text-field label="Descuento" v-model.number="form.descuento" variant="outlined" density="compact" :error-messages="errors.descuento"/>
                <v-text-field label="Promociones" v-model.number="form.promociones" variant="outlined"
                    density="compact" :error-messages="errors.promociones"/>
                <v-text-field label="Costo Envío" v-model.number="form.costo_envio" variant="outlined"
                    density="compact" :error-messages="errors.costo_envio"/>
                <v-text-field label="Total" :model-value="totalCalculado" readonly variant="outlined"
                    density="compact" disabled />
            </v-col>
        </v-row>

    </v-card-text>
</template>

<script>
import axios from 'axios'
import VentasDetalleTable from './VentasDetalleTable.vue'
import BancoDialog from '../Bancos/BancoDialog.vue';
import ClientesDialog from '../Clientes/ClientesDialog.vue';
import { toast } from 'vue3-toastify'

export default {
    components: { VentasDetalleTable, BancoDialog, ClientesDialog },
    emits: ['saved', 'cancel'],

    data() {
        return {
            loading: false,
            searchCliente: '',
            loadingClientes: false,
            debounceCliente: null,
            clientes: [],
            cliente:{
                nombre: null,
                telefono: null,
                direccion: null,
                nit: null,
            },
            clienteDialog: false,
            productos: [],
            bancos: [],
            bancoDialog: false,
            tiposAgarrador: [],
            tiposPapel: [],
            tipoPago:[
                { nombre: 'Efectivo'},
                { nombre: 'Pago con tarjeta'},
                { nombre: 'Depósito'},
                { nombre: 'Transferencia'},
            ],

            form: {
                vendedor_id: AUTH_USER.id,
                nombre_vendedor: AUTH_USER.name,
                clientes_id: null,
                bancos_id: null,
                fecha_entrega: null,
                serie: '',
                numero: '',
                fecha_entrega: '',
                no_deposito: '',
                cantidad_deposito: 0,
                costo_logo: 0,
                descuento: 0,
                promociones: 0,
                costo_envio: 0,
                proceso_estado_produccions_id: 1,
                detalle: [],
                tipo_pago: null,
            },
            fecha: '',
            hora: '',
            timer: null,
            user: window.AUTH_USER || {},
            paginas_id: 1,
            paginas: [],
            errors: {},
        }
    },

    methods: {

        async loadCatalogos() {
            this.paginas = (await axios.get('/pagina')).data
            //this.clientes = (await axios.get('/cliente')).data
            this.bancos = (await axios.get('/banco')).data
            //this.productos = (await axios.get('/product/search')).data.data
            this.tiposAgarrador = (await axios.get('/agarrador')).data
            this.tiposPapel = (await axios.get('/tipo-papel')).data
        },

        async guardarVenta() {
            this.loading = true
            try {
                this.form.subtotal = this.subtotalCalculado;
                this.form.total = this.totalCalculado;
                this.form.pendiente_pagar = this.pendientePagar;
                await axios.post('/venta', this.form)

                alert('Venta registrada')
                this.$router.push('/ventas')

            } catch (e) {
                if (e.response?.status === 422) {
                    this.errors = e.response.data.errors
                    toast.error('Revisa los campos marcados')
                } else {
                    toast.error('Error inesperado al guardar')
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
        async listProducts(paginaId) {
            const { data: productosPagina } = await axios.post('/product/search', { id: paginaId })

            const seleccionados = this.form.detalle
                .map(d => d.productos_id)
                .filter(Boolean)

            const productosExtra = this.productos
                .filter(p => p && seleccionados.includes(p.id))

            this.productos = [
                ...productosPagina.filter(Boolean),
                ...productosExtra.filter(
                    p => !productosPagina.find(pp => pp.id === p.id)
                )
            ]
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
        onClienteSaved(cliente) {
            this.clientes.push(cliente)
            this.form.clientes_id = cliente.id
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
            if (!item) return;

            this.form.clientes_id = item.id;

            this.cliente = {
                nombre: item.nombre,
                nit: item.nit,
                telefono: item.telefonos
                    .map(t => `${t.telefono_codigo_pais} ${t.telefono_numero}`)
                    .join(' / '),
                direccion: item.municipio
                    ? `${item.direccion}, ${item.municipio.nombre}, ${item.municipio.departamento.nombre}`
                    : `${item.direccion}, ${item.ciudad_pais}, ${item.estado_pais}`,
            };
        }

    },

    watch: {
        searchCliente(val) {

            if (!val || val.length < 2) {
                this.clientes = []
                return
            }

            clearTimeout(this.debounceCliente)

            this.debounceCliente = setTimeout(() => {
                this.buscarClientes(val)
            }, 800)
        }
    },

    computed: {
        subtotalCalculado() {
            return this.form.detalle.reduce((s, i) => s + parseFloat(i.total || 0), 0);
        },
        totalCalculado() {
            const sub = this.subtotalCalculado;
            const logo = parseFloat(this.form.costo_logo || 0);
            const descuento = parseFloat(this.form.descuento || 0);
            const promo = parseFloat(this.form.promociones || 0);
            const envio = parseFloat(this.form.costo_envio || 0);

            return (sub + logo + envio) - (descuento + promo);
        },
        pendientePagar(){
            const total = parseFloat(this.totalCalculado || 0);
            const totalDeposito = parseFloat(this.form.cantidad_deposito || 0);
            return total - totalDeposito;
        }
    },

    mounted() {
        this.loadCatalogos();
        // PARA HORA
        this.actualizarReloj();
        this.timer = setInterval(() => {
            this.actualizarReloj();
        }, 1000);
    },

    beforeUnmount() {//SOLO PARA HORA
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
}
</script>
