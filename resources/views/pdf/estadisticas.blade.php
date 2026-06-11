<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 10px;
        }

        .section {
            margin-bottom: 25px;
        }

        .cards {
            width: 100%;
            margin-bottom: 20px;
        }

        .card {
            width: 18%;
            display: inline-block;
            padding: 10px;
            border: 1px solid #ddd;
            margin-right: 5px;
            text-align: center;
        }

        .title {
            font-size: 10px;
            color: #666;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f0f0f0;
            padding: 6px;
            border: 1px solid #ccc;
        }

        td {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .green { color: green; }
        .red { color: red; }
        .purple { color: purple; }

        .chart {
            width: 100%;
            max-height: 260px;
            object-fit: contain;
            margin: 8px 0 12px;
        }

        .page-break {
            page-break-before: always;
        }

        .section-note {
            margin-top: -4px;
            margin-bottom: 12px;
            color: #666;
            text-align: center;
            font-size: 10px;
        }

        .page-heading {
            background: #f2f7f6;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>Reporte General</h2>

<p>
    Periodo: {{ $filtros['periodo'] ?? '' }} |
    Año: {{ $filtros['year'] ?? '' }} |
    Mes: {{ $filtros['month'] ?? '' }}
</p>

<!-- ================= PRODUCCIÓN ================= -->
<div class="section">
    <h3>Producción</h3>

    <div class="cards">
        <div class="card">
            <div class="title">Pedido</div>
            <div class="value">{{ number_format($totales['pedido']) }}</div>
        </div>
        <div class="card">
            <div class="title">Producción</div>
            <div class="value green">{{ number_format($totales['finalizadas']) }}</div>
        </div>
        <div class="card">
            <div class="title">Extras</div>
            <div class="value purple">+{{ number_format($totales['extras']) }}</div>
        </div>
        <div class="card">
            <div class="title">Desechadas</div>
            <div class="value red">{{ number_format($totales['desechadas']) }}</div>
        </div>
        <div class="card">
            <div class="title">Pendiente</div>
            <div class="value">{{ number_format($totales['pendiente']) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Pedido</th>
                <th>Producción</th>
                <th>Extras</th>
                <th>Desechadas</th>
                <th>Pendiente</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($porEstado as $item)
                <tr>
                    <td>{{ $item['estado'] }}</td>
                    <td>{{ number_format($item['pedido']) }}</td>
                    <td class="green">{{ number_format($item['finalizadas']) }}</td>
                    <td class="purple">+{{ number_format($item['extras']) }}</td>
                    <td class="red">{{ number_format($item['desechadas']) }}</td>
                    <td>{{ number_format($item['pendiente']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- ================= VENTAS POR PÁGINA ================= -->
<div class="section">
    <h3>Ventas por Página</h3>

    @if (!empty($charts['ventasPorPagina']))
        <img class="chart" src="{{ $charts['ventasPorPagina'] }}" alt="Ventas por página">
    @endif

    <table>
        <thead>
            <tr>
                <th>Página</th>
                <th>Venta</th>
                <th>Envío</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($porPagina as $item)
                <tr>
                    <td>{{ $item['nombre'] }}</td>
                    <td class="right">Q{{ number_format($item['venta'], 2) }}</td>
                    <td class="right">Q{{ number_format($item['envio'], 2) }}</td>
                    <td class="right bold">Q{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">TOTAL</td>
                <td class="right">Q{{ number_format($totalesPorPagina['venta'], 2) }}</td>
                <td class="right">Q{{ number_format($totalesPorPagina['envio'], 2) }}</td>
                <td class="right bold">Q{{ number_format($totalesPorPagina['total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ================= TIPOS DE PRODUCTO ================= -->
<div class="section">
    <h3>Tipos de Producto</h3>

    @if (!empty($charts['tiposProducto']))
        <img class="chart" src="{{ $charts['tiposProducto'] }}" alt="Tipos de producto">
    @endif

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Unidades</th>
                <th>Ventas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($porTipo as $item)
                <tr>
                    <td>{{ $item['tipo'] }}</td>
                    <td>{{ number_format($item['unidades']) }}</td>
                    <td>{{ $item['ventas'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="bold">TOTAL</td>
                <td>{{ number_format($totalesPorTipo['unidades']) }}</td>
                <td>{{ $totalesPorTipo['ventas'] }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="section">
    <h3>Tamaños más comercializados</h3>

    @if (!empty($charts['tamanos']))
        <img class="chart" src="{{ $charts['tamanos'] }}" alt="Tamaños más comercializados">
    @endif

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Tamaño</th>
                <th>Unidades</th>
                <th>Ventas</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($comercial['tamanos'] ?? []) as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td>{{ $item['tamano'] }}</td>
                    <td>{{ number_format($item['unidades']) }}</td>
                    <td>{{ number_format($item['ventas']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <h3>Compras por género</h3>

    @if (!empty($charts['generos']))
        <img class="chart" src="{{ $charts['generos'] }}" alt="Compras por género">
    @endif

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Género</th>
                <th>Ventas</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($comercial['generos'] ?? []) as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td>{{ $item['genero'] }}</td>
                    <td>{{ number_format($item['ventas']) }}</td>
                    <td class="right">Q{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <h3>Ventas por departamento</h3>

    @if (!empty($charts['departamentos']))
        <img class="chart" src="{{ $charts['departamentos'] }}" alt="Ventas por departamento">
    @endif

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Departamento</th>
                <th>Ventas</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($comercial['departamentos'] ?? []) as $item)
                <tr>
                    <td>{{ $item['no'] }}</td>
                    <td>{{ $item['departamento'] }}</td>
                    <td>{{ number_format($item['ventas']) }}</td>
                    <td class="right">Q{{ number_format($item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section page-break">
    <h3>CONTROL DE VENTAS - INDUSTRIA DE EMPAQUES S.A.</h3>
    <div class="section-note">
        Detalle de unidades y montos por producto según la página asignada a la venta.
    </div>

    @foreach (($productosPorPagina ?? []) as $pagina)
        <table>
            <thead>
                <tr class="page-heading">
                    <th colspan="6">{{ $pagina['nombre'] }}</th>
                </tr>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Unidades</th>
                    <th>Ventas</th>
                    <th>Total producto</th>
                    <th>Total página</th>
                </tr>
            </thead>
            <tbody>
                @foreach (($pagina['productos'] ?? []) as $producto)
                    <tr>
                        <td>{{ $producto['nombre'] }}</td>
                        <td>{{ $producto['tipo'] }}</td>
                        <td>{{ number_format($producto['unidades']) }}</td>
                        <td>{{ number_format($producto['ventas']) }}</td>
                        <td class="right">Q{{ number_format($producto['total'], 2) }}</td>
                        <td class="right">{{ $loop->first ? 'Q' . number_format($pagina['total'], 2) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="bold" colspan="2">TOTAL {{ $pagina['nombre'] }}</td>
                    <td class="bold">{{ number_format($pagina['unidades']) }}</td>
                    <td class="bold">{{ number_format($pagina['ventas']) }}</td>
                    <td></td>
                    <td class="right bold">Q{{ number_format($pagina['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach
</div>

</body>
</html>
