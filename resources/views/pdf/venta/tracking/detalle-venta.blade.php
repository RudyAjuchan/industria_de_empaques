<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
}

h2 {
    margin-bottom: 5px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}

th, td {
    border: 1px solid #ccc;
    padding: 4px;
}

th {
    background: #167160;
    color: white;
}
</style>
</head>

<body>

<h2>Tracking de Venta {{ $venta->numero_completo }}</h2>

@foreach($venta->detalles as $detalle)

    <h3>{{ $detalle->producto->nombre }} (Cantidad: {{ $detalle->cantidad }})</h3>

    <table>
        <thead>
            <tr>
                <th>Evento</th>
                <th>Estado</th>
                <th>Proceso</th>
                <th>Responsable</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalle->historialEstados as $h)

                <tr>
                    <td>{{ $h->tipo_evento }}</td>
                    <td>{{ $h->estadoProduccion->orden }}.{{ $h->estadoProduccion->nombre ?? '-' }}</td>
                    <td>{{ $h->procesoEstado->nombre ?? '-' }}</td>
                    <td>{{ $h->usuario->name ?? 'Sistema' }}</td>
                    <td>{{ \Carbon\Carbon::parse($h->fecha_inicio)->format('d/m/Y H:i') }}</td>
                    <td>
                        {{ $h->fecha_fin ? \Carbon\Carbon::parse($h->fecha_fin)->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td>{{ $h->observacion }}</td>
                </tr>

            @endforeach
        </tbody>
    </table>

@endforeach

</body>
</html>
