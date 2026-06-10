<template>
    <div class="pa-10">
        <LoginWelcome />

        <v-card v-if="puedeVerGeneral || puedeVerCorporativo" class="dashboard-filter-card mb-8 elevation-1">
            <div class="filter-card-header">
                <div>
                    <div class="text-h6 font-weight-bold">Filtros del dashboard</div>
                    <div class="text-caption text-grey-darken-1">
                        Periodo afecta todo. Tipo de cliente solo afecta indicadores corporativos.
                    </div>
                </div>

                <div class="filter-actions">
                    <v-btn color="primary" @click="cargarEstadisticas">
                        Aplicar
                    </v-btn>

                    <v-btn v-if="corporativo && can('dashboard.corporativo.reporte')" color="error" variant="tonal"
                        @click="exportPDF">
                        PDF
                    </v-btn>

                    <v-btn v-if="corporativo && can('dashboard.corporativo.reporte')" color="success" variant="tonal"
                        @click="exportExcel">
                        Excel
                    </v-btn>
                </div>
            </div>

            <v-row class="mt-1">
                <v-col cols="12" md="7">
                    <div class="filter-group">
                        <div class="filter-group-title">
                            <span>Periodo operativo y corporativo</span>
                            <v-chip size="x-small" color="primary" variant="tonal">Producción</v-chip>
                            <v-chip size="x-small" color="primary" variant="tonal">Ventas</v-chip>
                        </div>

                        <v-row>
                            <v-col cols="12" sm="4">
                                <v-select v-model="filtros.periodo" :items="periodos" label="Periodo" density="compact"
                                    variant="outlined" hide-details />
                            </v-col>

                            <v-col cols="12" sm="4">
                                <v-select v-model="filtros.year" :items="years" label="Año" density="compact"
                                    variant="outlined" hide-details />
                            </v-col>

                            <v-col cols="12" sm="4" v-if="filtros.periodo === 'mes'">
                                <v-select v-model="filtros.month" :items="meses" label="Mes" density="compact"
                                    variant="outlined" hide-details />
                            </v-col>

                            <v-col cols="12" sm="4" v-if="filtros.periodo === 'dia'">
                                <v-text-field v-model="filtros.fecha" type="date" label="Fecha" density="compact"
                                    variant="outlined" hide-details />
                            </v-col>
                        </v-row>
                    </div>
                </v-col>

                <v-col v-if="puedeVerCorporativo" cols="12" md="5">
                    <div class="filter-group filter-group-corporate">
                        <div class="filter-group-title">
                            <span>Segmentación corporativa</span>
                            <v-chip size="x-small" color="teal" variant="tonal">Solo ventas</v-chip>
                        </div>

                        <v-select v-model="filtros.tipo_cliente" :items="tiposClienteFiltro" label="Tipo de cliente"
                            density="compact" variant="outlined" hide-details />
                    </div>
                </v-col>
            </v-row>
        </v-card>

        <!-- ESTADISTICAS GENERALES (unidades) -->
        <div v-if="puedeVerGeneral" class="dashboard-section">
            <div class="section-heading">
                <div>
                    <h2>Dashboard general</h2>
                    <div class="text-caption text-grey-darken-1">
                        Indicadores operativos filtrados únicamente por periodo
                    </div>
                </div>
                <div class="section-filter-chips">
                    <v-chip size="small" color="primary" variant="tonal">Periodo</v-chip>
                </div>
            </div>

        <v-row class="mb-5">
            <v-col cols="12">
                <div class="subsection-title">Estado de pedidos del periodo</div>
            </v-col>
            <v-col cols="12" sm="6" md="2">
                <v-card class="metric-card metric-primary">
                    <div class="text-caption text-grey">Pedido</div>
                    <div class="text-h5 font-weight-bold text-primary">
                        {{ formatNumber(estadisticas.pedido) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="metric-card metric-success">
                    <div class="text-caption text-grey">Producción</div>
                    <div class="text-h5 font-weight-bold text-green">
                        {{ formatNumber(estadisticas.finalizadas) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="metric-card metric-purple">
                    <div class="text-caption text-grey">Extras</div>
                    <div class="text-h5 font-weight-bold text-purple">
                        +{{ formatNumber(estadisticas.extras) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="metric-card metric-danger">
                    <div class="text-caption text-grey">Desechadas</div>
                    <div class="text-h5 font-weight-bold text-red">
                        {{ formatNumber(estadisticas.desechadas) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="metric-card metric-muted">
                    <div class="text-caption text-grey">Pendiente de pedidos</div>
                    <div class="text-h5 font-weight-bold text-grey-darken-1">
                        {{ formatNumber(estadisticas.pendiente) }}
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- ESTADÍSTICAS POR ESTADOS -->
        <v-row class="mb-5">
            <v-col cols="12">
                <div class="subsection-title">Estadísticas por área</div>
                <div class="text-caption text-grey mb-4">
                    Mostrando el avance de los pedidos creados en el periodo seleccionado
                </div>
            </v-col>
            <v-col v-for="(estado, index) in porEstado" :key="index" cols="12" sm="6" md="4" lg="3">
                <v-card class="area-card">

                    <!-- TÍTULO -->
                    <div class="text-subtitle-1 font-weight-bold mb-3">
                        {{ estado.estado }}
                    </div>

                    <!-- PEDIDO -->
                    <div class="d-flex justify-space-between text-caption mb-1">
                        <span class="text-grey">Pedido</span>
                        <span class="text-primary font-weight-bold">
                            {{ formatNumber(estado.pedido) }}
                        </span>
                    </div>

                    <!-- PRODUCCIÓN -->
                    <div class="d-flex justify-space-between text-caption mb-1">
                        <span class="text-grey">Producción</span>
                        <span class="text-green font-weight-bold">
                            {{ formatNumber(estado.finalizadas) }}
                        </span>
                    </div>

                    <!-- EXTRAS -->
                    <div class="d-flex justify-space-between text-caption mb-1">
                        <span class="text-grey">Extras</span>
                        <span class="text-purple font-weight-bold">
                            +{{ formatNumber(estado.extras) }}
                        </span>
                    </div>

                    <!-- DESECHADAS -->
                    <div class="d-flex justify-space-between text-caption mb-1">
                        <span class="text-grey">Desechadas</span>
                        <span class="text-red font-weight-bold">
                            {{ formatNumber(estado.desechadas) }}
                        </span>
                    </div>

                    <!-- PENDIENTE -->
                    <div class="d-flex justify-space-between text-caption">
                        <span class="text-grey">Pendiente</span>
                        <span class="text-grey-darken-1 font-weight-bold">
                            {{ formatNumber(estado.pendiente) }}
                        </span>
                    </div>

                </v-card>
            </v-col>

        </v-row>

        <!-- GRÁFICA POR ESTADOS -->
        <v-row>
            <v-col cols="12">
                <v-card class="pa-4 elevation-2">
                    <div class="text-h6 mb-3">Producción por estado</div>
                    <v-row>
                        <v-col v-for="(estado, index) in porEstado" :key="'chart-' + index" cols="12" sm="6" md="4"
                            lg="3">
                            <v-card class="pa-3" style="position: relative; height: 250px;">
                                <div class="text-subtitle-2 font-weight-bold mb-2">
                                    {{ estado.estado }}
                                </div>

                                <canvas :id="'chart-' + index"></canvas>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">
                <v-card class="mt-5 elevation-2">
                    <v-card-title>
                        Detalle por estado
                    </v-card-title>

                    <v-data-table :headers="headers" :items="porEstado" density="compact" class="elevation-0">
                        <!-- PEDIDOS -->
                        <template v-slot:[`item.pedido`]="{ item }">
                            {{ formatNumber(item.pedido) }}
                        </template>

                        <!-- PRODUCCIÓN -->
                        <template v-slot:[`item.finalizadas`]="{ item }">
                            <span class="text-green font-weight-bold">
                                {{ formatNumber(item.finalizadas) }}
                            </span>
                        </template>

                        <!-- EXTRAS -->
                        <template v-slot:[`item.extras`]="{ item }">
                            <span class="text-purple font-weight-bold">
                                +{{ formatNumber(item.extras) }}
                            </span>
                        </template>

                        <!-- DESECHADAS -->
                        <template v-slot:[`item.desechadas`]="{ item }">
                            <span class="text-red font-weight-bold">
                                {{ formatNumber(item.desechadas) }}
                            </span>
                        </template>

                        <!-- PENDIENTE -->
                        <template v-slot:[`item.pendiente`]="{ item }">
                            <span class="text-grey-darken-1 font-weight-bold">
                                {{ formatNumber(item.pendiente) }}
                            </span>
                        </template>

                    </v-data-table>
                </v-card>
            </v-col>
        </v-row>
        </div>

        <div v-if="puedeVerCorporativo" class="dashboard-section dashboard-section-corporate mt-8">
            <div class="section-heading">
                <div>
                    <h2>Dashboard corporativo</h2>
                    <div class="text-caption text-grey-darken-1">
                        Indicadores comerciales filtrados por periodo y tipo de cliente
                    </div>
                </div>
                <div class="section-filter-chips">
                    <v-chip size="small" color="primary" variant="tonal">Periodo</v-chip>
                    <v-chip size="small" color="teal" variant="tonal">Tipo de cliente</v-chip>
                </div>
            </div>

        <v-row class="mt-5">

            <!-- TABLA -->
            <v-col cols="12" md="6">
                <v-card class="pa-4 elevation-2">
                    <v-card-title>
                        Venta por página
                    </v-card-title>

                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Página</th>
                                <th class="text-right">Venta sin envío</th>
                                <th class="text-right">Costo envío</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in ventasPorPagina" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.nombre }}</td>
                                <td class="text-right">{{ formatQuetzales(item.venta) }}</td>
                                <td class="text-right">{{ formatQuetzales(item.envio) }}</td>
                                <td class="text-right font-weight-bold">
                                    {{ formatQuetzales(item.total) }}
                                </td>
                            </tr>
                        </tbody>

                        <!-- TOTALES -->
                        <tfoot>
                            <tr class="bg-grey-lighten-3 font-weight-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-right">
                                    {{ formatQuetzales(totalesVentaPagina.venta) }}
                                </td>
                                <td class="text-right">
                                    {{ formatQuetzales(totalesVentaPagina.envio) }}
                                </td>
                                <td class="text-right">
                                    {{ formatQuetzales(totalesVentaPagina.total) }}
                                </td>
                            </tr>
                        </tfoot>
                    </v-table>
                </v-card>
            </v-col>

            <!-- GRÁFICA -->
            <v-col cols="12" md="6">
                <v-card class="pa-4 elevation-2" style="height: 400px;">
                    <div class="text-h6 mb-3 text-center">
                        Gráfica de ventas por página
                    </div>

                    <div class="ventas-chart-wrap">
                        <canvas id="graficaVentas"></canvas>
                    </div>
                </v-card>
            </v-col>

        </v-row>

        <v-row class="mt-6">

            <!-- TABLA -->
            <v-col cols="12" md="5">
                <v-card class="pa-4 elevation-2">
                    <v-card-title>Tipo de producto</v-card-title>

                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tipo</th>
                                <th class="text-right">Unidades</th>
                                <th class="text-right">Ventas</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in tiposProducto" :key="index">
                                <td>{{ index + 1 }}</td>
                                <td>{{ item.tipo }}</td>
                                <td class="text-right">{{ formatNumber(item.unidades) }}</td>
                                <td class="text-right">{{ item.ventas }}</td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="bg-grey-lighten-3 font-weight-bold">
                                <td colspan="2">TOTAL</td>
                                <td class="text-right">{{ formatNumber(totalesTipos.unidades) }}</td>
                                <td class="text-right">{{ totalesTipos.ventas }}</td>
                            </tr>
                        </tfoot>
                    </v-table>
                </v-card>
            </v-col>

            <!-- GRÁFICA -->
            <v-col cols="12" md="7">
                <v-card class="pa-4 elevation-2" style="height: 350px;">
                    <div class="text-subtitle-1 font-weight-bold mb-2 text-center">
                        Unidades por tipo
                    </div>
                    <canvas id="graficaTipos"></canvas>
                </v-card>
            </v-col>

        </v-row>

        <v-row class="mt-6">
            <v-col cols="12">
                <div class="subsection-title">Análisis comercial</div>
                <div class="text-caption text-grey mb-4">
                    Indicadores de venta según los pedidos confirmados del periodo seleccionado
                </div>
            </v-col>

            <v-col cols="12" md="6">
                <v-card class="pa-4 elevation-2 commercial-card">
                    <v-card-title>Tamaños más comercializados</v-card-title>

                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Tamaño</th>
                                <th class="text-right">Unidades</th>
                                <th class="text-right">Ventas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in comerciales.tamanos" :key="`tamano-${item.no}`">
                                <td>{{ item.no }}</td>
                                <td>{{ item.tamano }}</td>
                                <td class="text-right">{{ formatNumber(item.unidades) }}</td>
                                <td class="text-right">{{ formatNumber(item.ventas) }}</td>
                            </tr>
                        </tbody>
                    </v-table>

                    <div class="commercial-chart-wrap">
                        <canvas id="graficaTamanos"></canvas>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="6">
                <v-card class="pa-4 elevation-2 commercial-card">
                    <v-card-title>Compras por género</v-card-title>

                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Género</th>
                                <th class="text-right">Ventas</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in comerciales.generos" :key="`genero-${item.no}`">
                                <td>{{ item.no }}</td>
                                <td>{{ item.genero }}</td>
                                <td class="text-right">{{ formatNumber(item.ventas) }}</td>
                                <td class="text-right">{{ formatQuetzales(item.total) }}</td>
                            </tr>
                        </tbody>
                    </v-table>

                    <div class="commercial-chart-wrap">
                        <canvas id="graficaGeneros"></canvas>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" md="6">
                <v-card class="pa-4 elevation-2 commercial-card">
                    <v-card-title>Ventas por departamento</v-card-title>

                    <v-table density="compact">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Departamento</th>
                                <th class="text-right">Ventas</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in comerciales.departamentos" :key="`departamento-${item.no}`">
                                <td>{{ item.no }}</td>
                                <td>{{ item.departamento }}</td>
                                <td class="text-right">{{ formatNumber(item.ventas) }}</td>
                                <td class="text-right">{{ formatQuetzales(item.total) }}</td>
                            </tr>
                        </tbody>
                    </v-table>

                    <div class="commercial-chart-wrap">
                        <canvas id="graficaDepartamentos"></canvas>
                    </div>
                </v-card>
            </v-col>
        </v-row>
        </div>

        <DashboardSinAsignar v-if="!puedeVerGeneral && !puedeVerCorporativo" />

        <v-overlay :model-value="loading" class="align-center justify-center">
            <v-progress-circular indeterminate size="64" />
        </v-overlay>
    </div>
</template>
<script>
import LoginWelcome from './LoginWelcome.vue';
import DashboardSinAsignar from './DashboardSinAsignar.vue';
import ChartDataLabels from 'chartjs-plugin-datalabels'
import { toast } from 'vue3-toastify'
import { formatQuetzales } from '../utils/money'

const centerTextPlugin = {
    id: 'centerText',
    afterDraw(chart) {
        const { ctx, chartArea: { top, bottom, left, right } } = chart;
        const text = chart.config.options.plugins.centerText?.text;

        if (!text) return;

        ctx.save();

        // Calculamos el centro exacto del área del gráfico
        const centerX = (left + right) / 2;
        const centerY = (top + bottom) / 2;

        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.font = 'bold 16px sans-serif';
        ctx.fillStyle = '#333';

        // Dibujar el texto en el centro
        ctx.fillText(text, centerX, centerY);

        ctx.restore();
    }
}

import {
    Chart,
    ArcElement,
    DoughnutController,
    Tooltip,
    Legend,
    LinearScale,
    BarController,
    CategoryScale,
    BarElement,
} from 'chart.js'

Chart.register(
    ArcElement,
    DoughnutController,
    Tooltip,
    Legend,
    ChartDataLabels,
    centerTextPlugin,
    LinearScale,
    BarController,
    CategoryScale,
    BarElement,
)
export default {
    name: 'InicioVue',
    components: {
        LoginWelcome,
        DashboardSinAsignar,
    },
    props: {
        corporativo: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            loading: false,
            estadisticas: {
                pedido: 0,
                finalizadas: 0,
                extras: 0,
                desechadas: 0,
                pendiente: 0,
            },

            porEstado: [],

            headers: [
                { title: 'Estado', key: 'estado' },
                { title: 'Pedido', key: 'pedido' },
                { title: 'Producción', key: 'finalizadas' },
                { title: 'Extras', key: 'extras' },
                { title: 'Desechadas', key: 'desechadas' },
                { title: 'Pendiente', key: 'pendiente' },
            ],

            /* Para filtros */
            filtros: {
                periodo: 'mes',
                year: new Date().getFullYear(),
                month: new Date().getMonth() + 1,
                fecha: new Date().toISOString().substr(0, 10),
                tipo_cliente: 'todos',
            },

            periodos: [
                { title: 'Hoy', value: 'hoy' },
                { title: 'Día', value: 'dia' },
                { title: 'Mes', value: 'mes' },
                { title: 'Año', value: 'anio' },
            ],
            years: [],
            mesesDisponibles: {},

            meses: [],
            tiposClienteFiltro: [
                { title: 'Todos', value: 'todos' },
                { title: 'Nuevo', value: 'nuevo' },
                { title: 'Existente', value: 'existente' },
            ],

            chart: [],

            ventasPorPagina: [],
            headersVentas: [
                { title: 'No.', key: 'no' },
                { title: 'Página / Asesor', key: 'nombre' },
                { title: 'Venta sin envío', key: 'venta' },
                { title: 'Costo envío', key: 'envio' },
                { title: 'Total', key: 'total' }
            ],
            chartVentas: null,
            totalesVentaPagina: {
                venta: 0,
                envio: 0,
                total: 0,
            },
            coloresBase: [
                'rgba(79, 129, 189, 0.8)',  // azul
                'rgba(192, 80, 77, 0.8)',   // rojo
                'rgba(155, 187, 89, 0.8)',  // verde
                'rgba(128, 100, 162, 0.8)', // morado
                'rgba(75, 172, 198, 0.8)',  // celeste
                'rgba(247, 150, 70, 0.8)',  // naranja
                'rgba(146, 208, 80, 0.8)',  // verde claro
                'rgba(0, 176, 240, 0.8)',   // azul claro
                'rgba(255, 192, 0, 0.8)',   // amarillo
                'rgba(112, 48, 160, 0.8)',  // púrpura fuerte

                // extras
                'rgba(0, 112, 192, 0.8)',   // azul oscuro
                'rgba(0, 176, 80, 0.8)',    // verde fuerte
                'rgba(255, 102, 0, 0.8)',   // naranja fuerte
                'rgba(153, 153, 153, 0.8)', // gris
                'rgba(91, 155, 213, 0.8)',  // azul suave
                'rgba(237, 125, 49, 0.8)',  // naranja suave
            ],

            tiposProducto: [],
            totalesTipos: {
                unidades: 0,
                ventas: 0
            },
            chartTipos: null,
            comerciales: {
                tamanos: [],
                generos: [],
                tipos_cliente: [],
                departamentos: [],
                totales: {
                    ventas: 0,
                    total: 0,
                    unidades: 0,
                }
            },
            chartTamanos: null,
            chartGeneros: null,
            chartDepartamentos: null,
        }
    },
    methods: {

        async cargarEstadisticas() {
            this.loading = true
            try {
                const response = await axios.get('/estadisticas-produccion', {
                    params: this.filtros
                })

                this.estadisticas = response.data.totales
                this.porEstado = response.data.por_estado

                this.$nextTick(() => this.renderChart())

                if (this.puedeVerCorporativo) {
                    const response2 = await axios.get('/estadisticas-por-pagina', {
                        params: this.filtros
                    })

                    const response3 = await axios.get('/estadisticas-por-tipo', {
                        params: this.filtros
                    })

                    const response4 = await axios.get('/estadisticas-comerciales', {
                        params: this.filtros
                    })

                    this.ventasPorPagina = response2.data.ventas_por_pagina || []
                    this.totalesVentaPagina = response2.data.totales || {
                        venta: 0,
                        envio: 0,
                        total: 0,
                    }

                    this.tiposProducto = response3.data.tipos_producto || []
                    this.totalesTipos = response3.data.totales || {
                        unidades: 0,
                        ventas: 0
                    }

                    this.comerciales = {
                        tamanos: response4.data.tamanos || [],
                        generos: response4.data.generos || [],
                        tipos_cliente: response4.data.tipos_cliente || [],
                        departamentos: response4.data.departamentos || [],
                        totales: response4.data.totales || {
                            ventas: 0,
                            total: 0,
                            unidades: 0,
                        }
                    }

                    this.$nextTick(() => this.renderChartVentas())
                    this.$nextTick(() => this.renderChartTipos())
                    this.$nextTick(() => this.renderChartsComerciales())
                }

            } catch (error) {
                console.error(error)
                toast.error('No se pudieron cargar las estadísticas')
            } finally {
                this.loading = false
            }
        },

        renderChart() {

            // destruir charts anteriores
            if (this.chart.length) {
                this.chart.forEach(c => c.destroy())
            }

            this.chart = []

            this.porEstado.forEach((estado, index) => {

                const ctx = document.getElementById('chart-' + index)

                if (!ctx) return

                const chartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Producción', 'Pendiente'],
                        datasets: [
                            {
                                data: [
                                    estado.finalizadas,
                                    estado.pendiente
                                ],
                                backgroundColor: [
                                    'rgba(76, 175, 80, 0.8)', // verde
                                    'rgba(158, 158, 158, 0.5)' // gris
                                ],
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                bottom: 20
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },

                            centerText: {
                                text: `Total: ${this.formatNumber(estado.pedido)}`
                            },
                            datalabels: {
                                formatter: (value) => {
                                    return value > 0 ? Number(value).toLocaleString('en-US') : ''
                                },
                                color: '#000',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => {
                                        const value = context.raw || 0
                                        return `${context.label}: ${Number(value).toLocaleString('en-US')}`
                                    }
                                }
                            }
                        }
                    }
                })

                this.chart.push(chartInstance)
            })
        },

        formatNumber(value) {
            if (value === null || value === undefined) return '0'
            return Number(value).toLocaleString('es-GT')
        },

        formatQuetzales,

        async cargarFiltros() {
            const { data } = await axios.get('/filtros-produccion')

            this.years = data.years
            this.mesesDisponibles = data.meses

            // seleccionar por defecto
            this.filtros.year = this.years[0] || new Date().getFullYear()

            this.actualizarMeses()
        },

        actualizarMeses() {
            const meses = this.mesesDisponibles[this.filtros.year] || []

            this.meses = meses.map(m => ({
                title: this.nombreMes(m),
                value: m
            }))

            //this.filtros.month = this.meses[0]?.value || null
        },

        nombreMes(mes) {
            const nombres = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ]
            return nombres[mes - 1]
        },

        async exportPDF() {
            try {
                const response = await axios.post('/export/pdf', {
                    ...this.filtros,
                    charts: this.getChartImages(),
                }, {
                    responseType: 'blob',
                })

                const blob = new Blob([response.data], { type: 'application/pdf' })
                const url = window.URL.createObjectURL(blob)
                window.open(url, '_blank')
            } catch (error) {
                console.error(error)
                toast.error('No se pudo exportar el PDF')
            }
        },

        exportExcel() {
            const params = new URLSearchParams(this.filtros).toString()
            window.open(`/export/excel?${params}`, '_blank')
        },

        renderChartVentas() {

            if (this.chartVentas) {
                this.chartVentas.destroy()
            }
            const colores = this.ventasPorPagina.map((_, index) => {
                return this.coloresBase[index % this.coloresBase.length]
            })

            const ctx = document.getElementById('graficaVentas')

            const labels = this.ventasPorPagina.map(v => this.chartLabel(v.nombre))
            const data = this.ventasPorPagina.map(v => v.total)

            this.chartVentas = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total por página',
                            data: data,
                            backgroundColor: colores,
                            borderColor: colores.map(c => c.replace('0.8', '1')),
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            bottom: 34
                        }
                    },
                    plugins: {
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => {
                                return this.formatQuetzales(value)
                            },
                            font: {
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    const label = items[0]?.label
                                    return Array.isArray(label) ? label.join(' ') : label
                                },
                                label: (context) => {
                                    return this.formatQuetzales(context.raw)
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                display: true,
                                autoSkip: false,
                                maxRotation: 45,
                                minRotation: 45,
                                padding: 10,
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            },
                            grid: {
                                offset: true
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => this.formatQuetzales(value)
                            }
                        }
                    }
                }
            })
        },

        chartLabel(label) {
            const words = String(label || 'Sin página').split(' ')

            if (words.length === 1) {
                return words[0]
            }

            return words
        },

        renderChartTipos() {

            if (this.chartTipos) {
                this.chartTipos.destroy()
            }

            const ctx = document.getElementById('graficaTipos')

            const labels = this.tiposProducto.map(t => t.tipo)
            const data = this.tiposProducto.map(t => t.unidades)

            const colores = data.map((_, index) => {
                return this.coloresBase[index % this.coloresBase.length]
            })

            this.chartTipos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Unidades vendidas',
                            data: data,
                            backgroundColor: colores,
                            borderColor: colores.map(c => c.replace('0.8', '1')),
                            borderWidth: 1,
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 40,
                            left: 10,
                            right: 10
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => {
                                return value > 0
                                    ? Number(value).toLocaleString('es-GT')
                                    : ''
                            },
                            font: {
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    return Number(context.raw)
                                        .toLocaleString('es-GT')
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 35,
                                minRotation: 35,
                                padding: 10,
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return Number(value)
                                        .toLocaleString('es-GT')
                                }
                            }
                        }
                    }
                }
            })
        },

        renderChartsComerciales() {
            this.chartTamanos = this.renderBarChart({
                chart: this.chartTamanos,
                canvasId: 'graficaTamanos',
                labels: this.comerciales.tamanos.map(item => item.tamano),
                data: this.comerciales.tamanos.map(item => item.unidades),
                label: 'Unidades vendidas',
                money: false,
            })

            this.chartGeneros = this.renderBarChart({
                chart: this.chartGeneros,
                canvasId: 'graficaGeneros',
                labels: this.comerciales.generos.map(item => item.genero),
                data: this.comerciales.generos.map(item => item.total),
                label: 'Monto vendido',
                money: true,
            })

            this.chartDepartamentos = this.renderBarChart({
                chart: this.chartDepartamentos,
                canvasId: 'graficaDepartamentos',
                labels: this.comerciales.departamentos.slice(0, 10).map(item => item.departamento),
                data: this.comerciales.departamentos.slice(0, 10).map(item => item.total),
                label: 'Monto vendido',
                money: true,
            })
        },

        renderBarChart({ chart, canvasId, labels, data, label, money }) {
            if (chart) {
                chart.destroy()
            }

            const ctx = document.getElementById(canvasId)

            if (!ctx) return null

            const colores = data.map((_, index) => {
                return this.coloresBase[index % this.coloresBase.length]
            })

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.map(item => this.chartLabel(item)),
                    datasets: [
                        {
                            label,
                            data,
                            backgroundColor: colores,
                            borderColor: colores.map(c => c.replace('0.8', '1')),
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 42,
                            left: 10,
                            right: 10,
                        }
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => {
                                if (Number(value) <= 0) return ''
                                return money
                                    ? this.formatQuetzales(value)
                                    : this.formatNumber(value)
                            },
                            font: {
                                weight: 'bold',
                            }
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    const chartLabel = items[0]?.label
                                    return Array.isArray(chartLabel) ? chartLabel.join(' ') : chartLabel
                                },
                                label: (context) => {
                                    return money
                                        ? this.formatQuetzales(context.raw)
                                        : this.formatNumber(context.raw)
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,
                                maxRotation: 35,
                                minRotation: 35,
                                padding: 10,
                                font: {
                                    size: 11,
                                }
                            },
                            grid: {
                                display: false,
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return money
                                        ? this.formatQuetzales(value)
                                        : this.formatNumber(value)
                                }
                            }
                        }
                    }
                }
            })
        },

        getChartImages() {
            return {
                ventasPorPagina: this.chartVentas?.toBase64Image() || null,
                tiposProducto: this.chartTipos?.toBase64Image() || null,
                tamanos: this.chartTamanos?.toBase64Image() || null,
                generos: this.chartGeneros?.toBase64Image() || null,
                departamentos: this.chartDepartamentos?.toBase64Image() || null,
            }
        }
    },
    computed: {
        puedeVerGeneral() {
            return this.can('dashboard.general.ver')
        },

        puedeVerCorporativo() {
            return this.corporativo && this.can('dashboard.corporativo.ver')
        },
    },
    async mounted() {
        await this.cargarFiltros()
        await this.cargarEstadisticas()
    },

    watch: {
        'filtros.year'(val) {
            this.actualizarMeses()
        }
    }
}
</script>

<style scoped>
.dashboard-filter-card {
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e6e8eb;
}

.filter-card-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.filter-group {
    height: 100%;
    padding: 14px;
    border: 1px solid #e6e8eb;
    border-radius: 8px;
    background: #fbfcfd;
}

.filter-group-corporate {
    background: #f5fbfa;
    border-color: #cde8e4;
}

.filter-group-title {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
    font-size: 0.82rem;
    font-weight: 700;
    color: #2f3a45;
}

.dashboard-section {
    padding-top: 4px;
}

.section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e6e8eb;
}

.section-filter-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

.dashboard-section-corporate {
    padding-top: 24px;
    border-top: 2px solid #d9efec;
}

.subsection-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #272727;
}

.metric-card {
    min-height: 92px;
    padding: 14px;
    border-radius: 8px;
    border: 1px solid #e6e8eb;
    border-left-width: 4px;
}

.metric-primary {
    border-left-color: #005b83;
}

.metric-success {
    border-left-color: #2e7d32;
}

.metric-purple {
    border-left-color: #8e24aa;
}

.metric-danger {
    border-left-color: #d32f2f;
}

.metric-muted {
    border-left-color: #757575;
}

.area-card {
    height: 100%;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid #e6e8eb;
}

.ventas-chart-wrap {
    position: relative;
    height: 300px;
    min-height: 300px;
}

.commercial-card {
    min-height: 620px;
}

.commercial-chart-wrap {
    position: relative;
    height: 280px;
    min-height: 280px;
    margin-top: 20px;
}

@media (max-width: 960px) {
    .filter-card-header,
    .section-heading {
        align-items: stretch;
        flex-direction: column;
    }

    .filter-actions,
    .section-filter-chips {
        justify-content: flex-start;
    }
}
</style>
