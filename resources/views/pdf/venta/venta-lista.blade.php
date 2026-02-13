<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
    }

    h1 {
        text-align: center;
        margin-bottom: 8px;
    }

    .filter {
        margin-bottom: 10px;
        font-size: 9px;
        color: #555;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 4px;
    }

    th {
        background: #167160;
        color: #fff;
        font-size: 10px;
        text-align: left;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

</style>
</head>

<body>

<h1>Listado de Ventas</h1>

@if (!empty($search))
    <div class="filter">
        Filtro aplicado: <strong>{{ $search }}</strong>
    </div>
@endif

<table>
    <thead>
        <tr>
            <th>Número</th>
            <th>Cliente</th>
            <th>Vendedor</th>
            <th class="right">Subtotal</th>
            <th class="right">Descuento</th>
            <th class="right">Envío</th>
            <th class="right">Total</th>
            <th class="center">Estado</th>
            <th class="center">Producción</th>
            <th>Fecha emisión</th>
            <th>Fecha entrega</th>
        </tr>
    </thead>
    <tbody>
        @forelse($ventas as $v)
            <tr>
                <td>{{ $v->numero_completo }}</td>
                <td>{{ $v->cliente->nombre ?? '-' }}</td>
                <td>{{ $v->vendedor->name ?? '-' }}</td>

                <td class="right">Q {{ number_format($v->subtotal, 2) }}</td>
                <td class="right">Q {{ number_format($v->descuento, 2) }}</td>
                <td class="right">Q {{ number_format($v->costo_envio, 2) }}</td>
                <td class="right"><strong>Q {{ number_format($v->total, 2) }}</strong></td>

                <td class="center">{{ ucfirst($v->estado) }}</td>
                <td class="center">{{ ucfirst(str_replace('_',' ',$v->estado_produccion)) }}</td>

                <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($v->fecha_entrega)->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:10px;">
                    No se encontraron registros
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
