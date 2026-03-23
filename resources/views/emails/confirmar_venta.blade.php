<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial; background:#f5f5f5; padding:20px; margin:0;">

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:10px; overflow:hidden;">

                    <!-- HEADER -->
                    <tr>
                        <td style="background:#00432C; color:white; padding:20px; text-align:center;">

                            <img src="{{ config('app.url') }}/img/jeple_logo.png" width="120"
                                style="display:block; margin:0 auto 10px;">

                            <h2 style="margin:0;">Jeple Store</h2>

                        </td>
                    </tr>

                    <!-- BODY -->
                    <tr>
                        <td style="padding:20px; color:#333;">

                            <h3 style="color:#00432C;">Hola {{ $cliente->nombre }}</h3>

                            <p>Tu solicitud ha sido aprobada correctamente.</p>

                            <p>
                                <strong>No.</strong> {{ $venta->serie }}-{{ $venta->numero }}
                            </p>

                            <hr style="border:none; border-top:1px solid #ddd;">

                            <h4 style="color:#00432C;">Detalle del pedido:</h4>

                            <!-- TABLA -->
                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse;">

                                <thead>
                                    <tr style="background:#7DBA19; color:white;">
                                        <th align="left">Producto</th>
                                        <th align="center">Cantidad</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($venta->detalles as $item)
                                        <tr style="border-bottom:1px solid #eee;">

                                            <!-- PRODUCTO -->
                                            <td>
                                                <table width="100%" cellpadding="0" cellspacing="0">
                                                    <tr>

                                                        <td width="70">
                                                            <img src="{{ $item->producto->imagen_principal_url }}"
                                                                width="60"
                                                                style="border-radius:6px; display:block;">
                                                        </td>

                                                        <td style="padding-left:10px;">
                                                            <strong>{{ $item->producto->nombre }}</strong>

                                                            @if ($item->detalle_impresion)
                                                                <br><small>Impresión:
                                                                    {{ $item->detalle_impresion }}</small>
                                                            @endif

                                                            @if ($item->color_agarrador)
                                                                <br><small>Color: {{ $item->color_agarrador }}</small>
                                                            @endif

                                                            @if ($item->nombre_logo)
                                                                <br><small>Nombre Logo: {{ $item->nombre_logo }}</small>
                                                            @endif
                                                            
                                                            @if ($item->tipoAgarrador)
                                                                <br><small>Agarrador: {{ $item->tipoAgarrador->nombre }}</small>
                                                            @endif
                                                            @if ($item->tipoPapel)
                                                                <br><small>Papel: {{ $item->tipoPapel->nombre }}</small>
                                                            @endif
                                                        </td>

                                                    </tr>
                                                </table>
                                            </td>

                                            <!-- CANTIDAD -->
                                            <td align="center">
                                                {{ $item->cantidad }}
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                            <br>

                            <p>
                                ¡Gracias por su compra y su confianza!
                            </p>

                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td style="background:#0C2A00; color:white; text-align:center; padding:15px;">
                            © {{ date('Y') }} Jeple Store
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
