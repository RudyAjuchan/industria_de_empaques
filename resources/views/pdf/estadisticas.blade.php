<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
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
            font-size: 11px;
            color: #666;
        }

        .value {
            font-size: 16px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .green {
            color: green;
        }

        .red {
            color: red;
        }

        .purple {
            color: purple;
        }
    </style>
</head>

<body>

    <h2>Reporte de Producción</h2>

    <!-- FILTROS -->
    <p>
        Periodo: {{ $filtros['periodo'] ?? '' }} |
        Año: {{ $filtros['year'] ?? '' }} |
        Mes: {{ $filtros['month'] ?? '' }}
    </p>

    <!-- CARDS -->
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

    <!-- TABLA -->
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

</body>

</html>
