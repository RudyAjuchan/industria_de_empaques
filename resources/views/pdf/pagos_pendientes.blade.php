<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
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

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            vertical-align: top;
            word-break: break-word;
        }

        th {
            background: #167160;
            color: #fff;
            font-size: 10px;
            text-align: left;
        }

        .detail-row td {
            background: #f7faf9;
            color: #333;
        }

        .detail-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-grid td {
            border: 0;
            padding: 2px 6px 2px 0;
        }

        .label {
            color: #555;
            font-weight: bold;
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

    <h1>Reporte de créditos</h1>

    @if (!empty($search))
        <div class="filter">
            Filtro aplicado: <strong>{{ $search }}</strong>
        </div>
    @endif

    <div class="filter">
        @php
            $estadoCreditoLabels = [
                'vigentes' => 'Vigentes',
                'generados' => 'Créditos otorgados',
                'pagados' => 'Pagados',
                'todos' => 'Todos',
            ];
        @endphp
        Tipo: <strong>{{ $estadoCreditoLabels[$filtros['estado_credito'] ?? 'vigentes'] ?? 'Vigentes' }}</strong>
        @if (!empty($filtros['desde']) || !empty($filtros['hasta']))
            |
            Rango:
            <strong>{{ $filtros['desde'] ?? 'inicio' }}</strong>
            a
            <strong>{{ $filtros['hasta'] ?? 'hoy' }}</strong>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No. informe</th>
                <th>Cliente / negocio</th>
                <th>Negocio / logotipo</th>
                <th>Página</th>
                <th>Asesor</th>
                <th class="right">Total</th>
                <th class="right">Pagado</th>
                <th class="right">Crédito</th>
                <th>Fecha emisión</th>
                <th>Fecha entrega</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ventas as $v)
                @php
                    $pagado = $v->pagos->sum('monto');
                    $pendiente = $v->total - $pagado;
                    $notas = $v->pagos->pluck('nota')->filter();
                @endphp
                <tr>
                    <td>{{ $v->numero_completo }}</td>
                    <td>{{ $v->cliente->nombre ?? '-' }}</td>
                    <td>{{ $v->negocio_logotipo ?? '-' }}</td>
                    <td>{{ $v->pagina->nombre ?? '-' }}</td>
                    <td>{{ $v->vendedor->name ?? '-' }}</td>

                    <td class="right" style="white-space: nowrap"><strong>Q {{ number_format($v->total, 2) }}</strong></td>
                    <td class="right" style="white-space: nowrap">Q {{ number_format($pagado, 2) }}</td>
                    <td class="right" style="white-space: nowrap"><strong>Q {{ number_format($pendiente, 2) }}</strong></td>

                    <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($v->fecha_entrega)->format('d/m/Y') }}</td>
                </tr>
                <tr class="detail-row">
                    <td colspan="10">
                        <table class="detail-grid">
                            <tr>
                                <td width="14%"><span class="label">Subtotal:</span> Q {{ number_format($v->subtotal, 2) }}</td>
                                <td width="14%"><span class="label">Descuento:</span> Q {{ number_format($v->descuento, 2) }}</td>
                                <td width="14%"><span class="label">Envío:</span> Q {{ number_format($v->costo_envio, 2) }}</td>
                                <td width="14%"><span class="label">Estado:</span> {{ ucfirst($v->estado) }}</td>
                                <td width="16%"><span class="label">Producción:</span> {{ ucfirst(str_replace('_', ' ', $v->estado_produccion)) }}</td>
                                <td width="28%">
                                    <span class="label">Nota:</span>
                                    {{ $notas->count() ? $notas->implode(' | ') : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <span class="label">Pagos / boletas:</span>
                                    @if ($v->pagos && count($v->pagos))
                                        @foreach ($v->pagos as $p)
                                            Q{{ number_format($p->monto, 2) }}
                                            ({{ $p->metodo_pago ?? 'N/A' }})
                                            {{ $p->banco?->nombre ? '- ' . $p->banco?->nombre : '' }}
                                            {{ $p->referencia ? '- No. dep: ' . $p->referencia : '' }}
                                            @if ($p->comprobante_path)
                                                - <a href="{{ route('pagos.comprobante', $p) }}">Descargar comprobante</a>
                                            @endif
                                            @if (!$loop->last) | @endif
                                        @endforeach
                                    @else
                                        Sin pagos registrados
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; padding:10px;">
                        No se encontraron registros
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
