<table class="no-border">
    <tr>
        <td>
            <strong>INDUSTRIAS DE EMPAQUES, S.A.</strong><br>
            Fecha emisión: {{ now()->format('d-m-Y') }}<br>
            Hora: {{ now()->format('H:i:s') }}<br>
            Fecha entrega: {{ $venta->fecha_entrega }}
        </td>
        <td style="width: 300px;"></td>
        <td class="text-center">
            <div style="line-height: 0.5rem">
                <strong>{{ $venta->vendedor->name }}</strong><br>
                <span style="font-size: 0.5rem">ASESOR DE VENTAS</span><br>
            </div>
            <br>
            <div style="line-height: 0.5rem">
                <strong>{{ $venta->nombres_paginas_productos }}</strong><br>
                <span style="font-size: 0.5rem">PÁGINA</span>
            </div>
        </td>
    </tr>
</table>

<hr>
