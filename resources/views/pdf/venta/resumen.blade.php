@php
    $promoMonto = 0;

    if ($venta->promociones) {
        if ($venta->promociones['tipo'] === 'porcentaje') {
            $promoMonto = $venta->subtotal * ($venta->promociones['valor'] / 100);
        } else {
            $promoMonto = $venta->promociones['valor'];
        }
    }
@endphp
<table style="margin-top:10px">
    <tr>
        <th>SUB-TOTAL</th>
        <td class="text-right">Q {{ number_format($venta->subtotal, 2) }}</td>
    </tr>
    <tr>
        <th>DESCUENTO</th>
        <td class="text-right"> -- Q {{ number_format($venta->descuento, 2) }}</td>
    </tr>
    <tr>
        <th>PROMOCIONES</th>
        <td class="text-right">
            @if ($venta->promociones)
                <span style="color:#d32f2f;">
                    - Q {{ number_format($promoMonto, 2) }}
                </span>

                <br>

                <span style="font-size:10px; color:#555;">
                    {{ $venta->promociones['nombre'] }}
                </span>
            @else
                -- Q 0.00
            @endif
        </td>
    </tr>
    <tr>
        <th>COSTO DE LOGO</th>
        <td class="text-right"> + Q {{ number_format($venta->costo_logo, 2) }}</td>
    </tr>
    <tr>
        <th>COSTO DE ENVÍO</th>
        <td class="text-right"> + Q {{ number_format($venta->costo_envio, 2) }}</td>
    </tr>
    <tr>
        <th class="total">TOTAL</th>
        <td class="text-right total">
            Q {{ number_format($venta->total, 2) }}
        </td>
    </tr>
</table>
