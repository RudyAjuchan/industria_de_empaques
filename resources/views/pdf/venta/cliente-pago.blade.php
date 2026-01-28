<table class="no-border" style="margin-top:10px">
    <tr>
        <td width="48%">
            <strong>TIPO DE CLIENTE:</strong> Nuevo<br>
            <strong>CLIENTE:</strong> {{ $venta->cliente->nombre }}<br>
            <strong>TELÉFONO:</strong>
            {{ $venta->cliente->telefonos->map(fn($t) => "{$t->telefono_codigo_pais} {$t->telefono_numero}")->join(' / ') }}<br>
            <strong>NIT:</strong> {{ $venta->cliente->nit ?? 'CF' }}<br>
            <strong>DIRECCIÓN:</strong>
            {{ $venta->cliente->direccion }}
        </td>

        <td width="4%"></td>

        <td width="48%">
            <strong>TIPO DE PAGO:</strong> {{ $venta->tipo_pago }}<br>
            <strong>BANCO:</strong> {{ $venta->banco->nombre }}<br>
            <strong>NO. DEPÓSITO:</strong> {{ $venta->no_deposito ?? '-' }}<br>
            <strong>CANTIDAD DEPÓSITO:</strong> Q {{ number_format($venta->cantidad_deposito, 2) }}<br>
            <strong>PENDIENTE A PAGAR:</strong>
            Q {{ number_format($venta->pendiente_pagar, 2) }}
        </td>
    </tr>
</table>
