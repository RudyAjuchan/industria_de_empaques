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

            <!-- BOTÓN -->
            <v-col cols="12" md="5" class="d-flex ga-3">
                <v-btn color="primary" @click="cargarEstadisticas">
                    Aplicar
                </v-btn>

                <v-btn color="red" variant="tonal" @click="exportPDF">
                    Exportar PDF
                </v-btn>

                <v-btn color="green" variant="tonal" @click="exportExcel">
                    Exportar Excel
                </v-btn>
            </v-col>

        </v-row>

        <!-- ESTADISTICAS GENERALES (unidades) -->
        <v-row class="mb-5">
            <v-col cols="12">
                <h2>Estadísticas Generales</h2>
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
                    <div class="text-caption text-grey">Pendiente</div>
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
                    <canvas id="graficaEstados"></canvas>
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
</template>
<script>
import LoginWelcome from './LoginWelcome.vue';
import ChartDataLabels from 'chartjs-plugin-datalabels'
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend
} from 'chart.js'

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    ChartDataLabels
)
export default {
    name: 'InicioVue',
    components: {
        LoginWelcome
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

            chart: null,

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
            },

            periodos: [
                { title: 'Hoy', value: 'hoy' },
                { title: 'Mes', value: 'mes' },
                { title: 'Año', value: 'anio' },
            ],
            years: [],
            mesesDisponibles: {},

            meses: []
        }
    },
    methods: {

        async cargarEstadisticas() {
            this.loading = true
            try {
                const { data } = await axios.get('/estadisticas-produccion', {
                    params: this.filtros
                })

                this.estadisticas = data.totales
                this.porEstado = data.por_estado

                this.$nextTick(() => this.renderChart())

            } finally {
                this.loading = false
            }
        },

        renderChart() {

            if (this.chart) {
                this.chart.destroy()
            }

            const ctx = document.getElementById('graficaEstados')

            const labels = this.porEstado.map(e => e.estado)
            const produccion = this.porEstado.map(e => e.finalizadas)
            const desechadas = this.porEstado.map(e => e.desechadas)
            const extras = this.porEstado.map(e => e.extras)

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Producción',
                            data: produccion,
                            backgroundColor: 'rgba(76, 175, 80, 0.6)',
                            borderColor: 'rgba(76, 175, 80, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Extras',
                            data: extras,
                            backgroundColor: 'rgba(156, 39, 176, 0.6)',
                            borderColor: 'rgba(156, 39, 176, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Desechadas',
                            data: desechadas,
                            backgroundColor: 'rgba(244, 67, 54, 0.6)',
                            borderColor: 'rgba(244, 67, 54, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        datalabels: {
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => {
                                return value > 0 ? Number(value).toLocaleString('en-US') : ''
                            },
                            font: {
                                weight: 'bold'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const value = context.raw || 0
                                    return `${context.dataset.label}: ${Number(value).toLocaleString('en-US')}`
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => {
                                    return Number(value).toLocaleString('en-US')
                                }
                            }
                        }
                    },

                }
            })
        },

        formatNumber(value) {
            if (value === null || value === undefined) return '0'
            return Number(value).toLocaleString('es-GT')
        },

        async cargarFiltros() {
            const { data } = await axios.get('/filtros-produccion')

            this.years = data.years
            this.mesesDisponibles = data.meses

            // seleccionar por defecto
            this.filtros.year = this.years[0]

            this.actualizarMeses()
        },

        actualizarMeses() {
            const meses = this.mesesDisponibles[this.filtros.year] || []

            this.meses = meses.map(m => ({
                title: this.nombreMes(m),
                value: m
            }))

            this.filtros.month = this.meses[0]?.value || null
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
        }
    },
    mounted() {
        this.cargarFiltros()
        this.cargarEstadisticas()
    },

    watch: {
        'filtros.year'(val) {
            this.actualizarMeses()
        }
    }
}
</script>