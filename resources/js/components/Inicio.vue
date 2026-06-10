<template>
    <div class="pa-10">
        <LoginWelcome />

        <!-- FILTROS PARA EL DASHBOARD -->
        <v-row>

            <!-- TIPO DE PERIODO -->
            <v-col cols="12" md="3">
                <v-select v-model="filtros.periodo" :items="periodos" label="Periodo" density="compact"
                    variant="outlined" />
            </v-col>

            <!-- AÑO -->
            <v-col cols="12" md="2">
                <v-select v-model="filtros.year" :items="years" label="Año" density="compact" variant="outlined" />
            </v-col>

            <!-- MES -->
            <v-col cols="12" md="2" v-if="filtros.periodo === 'mes'">
                <v-select v-model="filtros.month" :items="meses" label="Mes" density="compact" variant="outlined" />
            </v-col>

            <v-col cols="12" md="2" v-if="filtros.periodo === 'dia'">
                <v-text-field v-model="filtros.fecha" type="date" label="Fecha" density="compact" variant="outlined" />
            </v-col>

            <!-- BOTÓN -->
            <v-col cols="12" md="5" class="d-flex ga-3">
                <v-btn color="primary" @click="cargarEstadisticas">
                    Aplicar
                </v-btn>

                <v-btn v-if="corporativo && can('dashboard.corporativo.reporte')" color="error" variant="tonal" @click="exportPDF">
                    Exportar PDF
                </v-btn>

                <v-btn v-if="corporativo && can('dashboard.corporativo.reporte')" color="success" variant="tonal" @click="exportExcel">
                    Exportar Excel
                </v-btn>
            </v-col>

        </v-row>

        <!-- ESTADISTICAS GENERALES (unidades) -->
        <v-row class="mb-5">
            <v-col cols="12">
                <h2>Estado de pedidos del periodo</h2>
            </v-col>
            <v-col cols="12" sm="6" md="2">
                <v-card class="pa-3 elevation-2">
                    <div class="text-caption text-grey">Pedido</div>
                    <div class="text-h5 font-weight-bold text-primary">
                        {{ formatNumber(estadisticas.pedido) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="pa-3 elevation-2">
                    <div class="text-caption text-grey">Producción</div>
                    <div class="text-h5 font-weight-bold text-green">
                        {{ formatNumber(estadisticas.finalizadas) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="pa-3 elevation-2">
                    <div class="text-caption text-grey">Extras</div>
                    <div class="text-h5 font-weight-bold text-purple">
                        +{{ formatNumber(estadisticas.extras) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="pa-3 elevation-2">
                    <div class="text-caption text-grey">Desechadas</div>
                    <div class="text-h5 font-weight-bold text-red">
                        {{ formatNumber(estadisticas.desechadas) }}
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="2">
                <v-card class="pa-3 elevation-2">
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
                <h2>Estadísticas Por área (unidades)</h2>
                <div class="text-caption text-grey mb-4">
                    Mostrando el avance de los pedidos creados en el periodo seleccionado
                </div>
            </v-col>
            <v-col v-for="(estado, index) in porEstado" :key="index" cols="12" sm="6" md="4" lg="3">
                <v-card class="pa-4 elevation-2 rounded-lg">

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
                        <v-col cols="12">
                            <h2>Comparativa por área</h2>
                        </v-col>

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

        <v-row v-if="corporativo" class="mt-5">

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

        <v-row v-if="corporativo" class="mt-6">

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

        <v-overlay :model-value="loading" class="align-center justify-center">
            <v-progress-circular indeterminate size="64" />
        </v-overlay>
    </div>
</template>
<script>
import LoginWelcome from './LoginWelcome.vue';
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
        LoginWelcome
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
            chartTipos: null
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

                if (this.corporativo) {
                    const response2 = await axios.get('/estadisticas-por-pagina', {
                        params: this.filtros
                    })

                    const response3 = await axios.get('/estadisticas-por-tipo', {
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

                    this.$nextTick(() => this.renderChartVentas())
                    this.$nextTick(() => this.renderChartTipos())
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

        exportPDF() {
            const params = new URLSearchParams(this.filtros).toString()
            window.open(`/export/pdf?${params}`, '_blank')
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
        }
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
.ventas-chart-wrap {
    position: relative;
    height: 300px;
    min-height: 300px;
}
</style>
