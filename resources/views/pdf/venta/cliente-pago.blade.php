@php
    $pagado = $venta->pagos->sum('monto');
    $pendiente = $venta->total - $pagado;
@endphp

<table class="no-border" style="margin-top:10px">
    <tr>
        <td width="48%">
            <strong>TIPO DE CLIENTE:</strong>
            {{ $venta->es_cliente_nuevo ? 'Nuevo' : 'Existente' }}<br>

            <strong>CLIENTE:</strong> {{ $venta->cliente->nombre }}<br>

            <strong>TELÉFONO:</strong>
            {{ $venta->cliente->telefonos->map(fn($t) => "{$t->telefono_codigo_pais} {$t->telefono_numero}")->join(' / ') }}<br>

            <strong>NIT:</strong> {{ $venta->cliente->nit ?? 'CF' }}
            <strong>DPI:</strong> {{ $venta->cliente->dpi ?? '-' }}<br>

            <strong>DIRECCIÓN:</strong>
            {{ $venta->cliente->direccion ?? '-' }}
        </td>

        <td width="4%"></td>

        <td width="48%">
            <strong>TIPO DE PAGO:</strong> {{ $venta->tipo_pago }}<br>

            <strong>PAGADO:</strong>
            Q {{ number_format($pagado, 2) }}<br>

            <strong>PENDIENTE:</strong>
            <span style="color:#d32f2f;">
                Q {{ number_format($pendiente, 2) }}
            </span><br>

            <!-- HISTORIAL -->
            @if ($venta->pagos->count())
                <br>
                <strong>DETALLE DE PAGOS:</strong><br>

                @foreach ($venta->pagos as $p)
                    <span style="font-size:10px;">
                        Q{{ number_format($p->monto, 2) }} -
                        {{ $p->metodo_pago ?? 'N/A' }}
                        ({{ $p->created_at->format('d/m/Y') }})
                    </span><br>
                @endforeach
            @endif

        </td>
    </tr>
</table>
