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
            <th>CÓDIGO:</th>
            <td colspan="2">{{ $item->producto->sku ?? $item->producto->id ?? '-' }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <th>PRODUCTO:</th>
            <td colspan="2">{{ $item->producto->nombre ?? '-' }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <th>LOGOTIPO:</th>
            <td colspan="2">{{ $item->nombre_logo ?? '-' }}</td>
            <td colspan="3"></td>
        </tr>

        <tr>
            <th style="border-top: #999 solid 1px; border-left: #999 solid 1px">ALTO:</th>
            <td style="border-top: #999 solid 1px; border-right: #999 solid 1px">{{ $item->producto->alto }}</td>
            <th style="border-top: #999 solid 1px">COLOR AGARRADOR:</th>
            <td style="border-top: #999 solid 1px; border-right: #999 solid 1px; border-left: #999 solid 1px">{{ $item->color_agarrador }}</td>
            <th style="border-top: #999 solid 1px">CANTIDAD:</th>
            <td style="border-top: #999 solid 1px; border-right: #999 solid 1px">{{ $item->cantidad }}</td>
        </tr>

        <tr>
            <th style="border-left: #999 solid 1px">ANCHO:</th>
            <td style="border-right: #999 solid 1px">{{ $item->producto->ancho }}</td>
            <th>TIPO AGARRADOR:</th>
            <td style="border-right: #999 solid 1px; border-left: #999 solid 1px">{{ $item->tipoAgarrador->nombre ?? '-' }}</td>
            <th>PRECIO UNIT:</th>
            <td style="border-right: #999 solid 1px">Q {{ number_format($item->precio, 2) }}</td>
        </tr>

        <tr>
            <th style="border-left: #999 solid 1px">FUELLE:</th>
            <td style="border-right: #999 solid 1px">{{ $item->producto->fuelle }}</td>
            <th>TIPO PAPEL:</th>
            <td style="border-right: #999 solid 1px; border-left: #999 solid 1px">{{ $item->tipoPapel->nombre ?? '-' }}</td>
            <th>TOTAL:</th>
            <td style="border-right: #999 solid 1px">
                @if (!empty($item->promocion_aplicada))
                    <span style="text-decoration:line-through; color:#999;">
                        Q {{ number_format($item->precio * $item->cantidad, 2) }}
                    </span>
                    <br>
                    <span style="font-size:10px; font-weight: bold">
                        -{{ $item->promocion_aplicada['tipo'] == 'porcentaje' ? $item->promocion_aplicada['valor'].'%' : 'Q.'.$item->promocion_aplicada['valor'] }}
                    </span><br>

                    <strong>Q {{ number_format($item->total, 2) }}</strong>
                    <br>
                    <span style="color:#d32f2f; font-size:8px;">
                        {{ $item->promocion_aplicada['nombre'] }}
                    </span>
                @else
                    Q {{ number_format($item->total, 2) }}
                @endif
            </td>
        </tr>
        <tr>
            <th style="border-bottom: #999 solid 1px; border-left: #999 solid 1px">TIPO:</th>
            <td style="border-bottom: #999 solid 1px; border-right: #999 solid 1px">{{ $item->producto->tipo }}</td>
            <th style="border-bottom: #999 solid 1px">DETALLE IMPRESIÓN:</th>
            <td colspan="3" style="border: #999 solid 1px">{{ $item->detalle_impresion }}</td>
        </tr>
        <tr>
            <td style="border: #999 solid 1px" colspan="6"><strong>OBSERVACIONES:</strong> {{ $item->observaciones }}</td>
        </tr>
    </table>
@endforeach
