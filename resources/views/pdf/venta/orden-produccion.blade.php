<div style="page-break-before: always;"></div>

<h3>ORDEN DE PRODUCCIÓN</h3>

@foreach ($venta->detalles as $item)
    <colgroup>
        <col style="width:30%">
        <col style="width:17.5%">
        <col style="width:17.5%">
        <col style="width:17.5%">
        <col style="width:17.5%">
    </colgroup>
    <table style="margin-bottom:25px">
        <tbody>
            <tr>
                <th style="width:25%">NÚMERO DE INFORME</th>
                <td colspan="4" style="width:17.5%">{{ $venta->numero_completo }}</td>
            </tr>
            <tr>
                <th>PRODUCTO</th>
                <td colspan="4">{{ $item->producto->nombre }}</td>
            </tr>
            <tr>
                <th>CANTIDAD DE BOLSAS</th>
                <td colspan="4">{{ $item->cantidad }}</td>
            </tr>
            <tr>
                <th>ALTO</th>
                <td colspan="4">{{ $item->producto->alto }}</td>
            </tr>
            <tr>
                <th>ANCHO</th>
                <td colspan="4">{{ $item->producto->ancho }}</td>
            </tr>
            <tr>
                <th>FUELLE</th>
                <td colspan="4">{{ $item->producto->fuelle }}</td>
            </tr>

            <tr>
                <th>PEGAR</th>
                <td colspan="4"></td>
            </tr>

            <tr>
                <th rowspan="2">TOTAL</th>
                <th style="width:17.5%">CORTE</th>
                <th style="width:17.5%">ELABORACIÓN</th>
                <th style="width:17.5%">COLOCACIÓN CINTA</th>
                <th style="width:17.5%">EMPAQUE</th>
            </tr>
            <tr>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
            </tr>

            <tr>
                <th>LOGOTIPO</th>
                <td colspan="4">{{ $item->nombre_logo ?? '-' }}</td>
            </tr>

            <tr>
                <th>TIPO DE AGARRADOR Y COLOR</th>
                <td colspan="4">
                    {{ $item->tipoAgarrador->nombre }} / {{ $item->color_agarrador }}
                </td>
            </tr>

            <tr>
                <th>BASE</th>
                <td colspan="4"></td>
            </tr>

            <tr>
                <th rowspan="2">DESECHA</th>
                <th>CORTE</th>
                <th>ELALBORACIÓN</th>
                <th>COLOCACIÓN CINTA</th>
                <th>EMPAQUE</th>
            </tr>

            <tr>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
                <td style="height: 15px"></td>
            </tr>

            <tr>
                <th>FECHA</th>
                <td colspan="4"></td>
            </tr>
        </tbody>
    </table>
@endforeach
