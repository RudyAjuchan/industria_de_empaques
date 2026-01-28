<table style="margin-top:10px">
    <tr>
        <th>SUB-TOTAL</th>
        <td class="text-right">Q {{ number_format($venta->subtotal,2) }}</td>
    </tr>
    <tr>
        <th>DESCUENTO</th>
        <td class="text-right"> -- Q {{ number_format($venta->descuento,2) }}</td>
    </tr>
    <tr>
        <th>PROMOCIONES</th>
        <td class="text-right"> -- Q {{ number_format($venta->promociones,2) }}</td>
    </tr>
    <tr>
        <th>COSTO DE LOGO</th>
        <td class="text-right"> +  Q {{ number_format($venta->costo_logo,2) }}</td>
    </tr>
    <tr>
        <th>COSTO DE ENVÍO</th>
        <td class="text-right"> +  Q {{ number_format($venta->costo_envio,2) }}</td>
    </tr>
    <tr>
        <th class="total">TOTAL</th>
        <td class="text-right total">
            Q {{ number_format($venta->total,2) }}
        </td>
    </tr>
</table>
