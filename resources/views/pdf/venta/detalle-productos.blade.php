@foreach ($venta->detalles as $item)

<table style="margin-bottom:15px; margin-top: 15px">
    <colgroup>
        <col style="width:16.66%">
        <col style="width:16.66%">
        <col style="width:16.66%">
        <col style="width:16.66%">
        <col style="width:16.66%">
        <col style="width:16.66%">
    </colgroup>

    <tr>
        <th colspan="3">NOMBRE DEL LOGOTIPO</th>
        <td colspan="3">{{ $item->nombre_logo ?? '-' }}</td>
    </tr>

    <tr>
        <th colspan="3">NOMBRE DEL PRODUCTO</th>
        <td colspan="3">{{ $item->producto->nombre }}</td>
    </tr>

    <tr>
        <th>ALTO</th>
        <td>{{ $item->producto->alto }}</td>
        <th>COLOR AGARRADOR</th>
        <td>{{ $item->color_agarrador }}</td>
        <th>PRECIO U</th>
        <td>{{ number_format($item->precio,2) }}</td>
    </tr>

    <tr>
        <th>ANCHO</th>
        <td>{{ $item->producto->ancho }}</td>
        <th>TIPO AGARRADOR</th>
        <td>{{ $item->tipoAgarrador->nombre }}</td>
        <th>CANTIDAD</th>
        <td>{{ $item->cantidad }}</td>
    </tr>

    <tr>
        <th>FUELLE</th>
        <td>{{ $item->producto->fuelle }}</td>
        <th>TIPO PAPEL</th>
        <td>{{ $item->tipoPapel->nombre }}</td>
        <th>TOTAL</th>
        <td>{{ number_format($item->total,2) }}</td>
    </tr>

    <tr>
        <th>TIPO</th>
        <td>{{ $item->producto->tipo }}</td>
        <th>DETALLE IMPRESIÓN</th>
        <td colspan="3">{{ $item->detalle_impresion }}</td>
    </tr>
</table>

@endforeach
