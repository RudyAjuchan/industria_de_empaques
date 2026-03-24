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
                                        <th align="center">Precio</th>
                                        <th align="center">Cantidad</th>
                                        <th align="center">Total</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($venta->detalles as $item)
                                        @php
                                            $promo = $item->promocion_aplicada;
                                        @endphp

                                        <tr style="border-bottom:1px solid #eee;">

                                            <td>
                                                <table width="100%">
                                                    <tr>
                                                        <td width="70">
                                                            <img src="{{ $item->producto->imagen_principal_url }}"
                                                                width="60" style="border-radius:6px;">
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

                                                            @if ($item->tipoAgarrador)
                                                                <br><small>Agarrador:
                                                                    {{ $item->tipoAgarrador->nombre }}</small>
                                                            @endif

                                                            @if ($item->tipoPapel)
                                                                <br><small>Papel: {{ $item->tipoPapel->nombre }}</small>
                                                            @endif

                                                            {{-- PROMO PRODUCTO --}}
                                                            @if ($promo)
                                                                <br>
                                                                <small style="color:#d32f2f;">
                                                                    {{ $promo['nombre'] ?? 'Promoción' }}
                                                                    @if ($promo['tipo'] === 'porcentaje')
                                                                        ({{ $promo['valor'] }}%)
                                                                    @else
                                                                        (Q{{ number_format($promo['valor'], 2) }})
                                                                    @endif
                                                                </small>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>

                                            <td align="center">
                                                Q{{ number_format($item->precio, 2) }}
                                            </td>

                                            <td align="center">
                                                {{ $item->cantidad }}
                                            </td>

                                            <td align="center">
                                                <strong>Q{{ number_format($item->total, 2) }}</strong>
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>

                            <br>

                            <table width="100%" cellpadding="6">
                                <tr>
                                    <td align="right">Subtotal:</td>
                                    <td align="right"><strong>Q{{ number_format($venta->subtotal, 2) }}</strong></td>
                                </tr>

                                @if ($venta->descuento > 0)
                                    <tr>
                                        <td align="right">Descuento:</td>
                                        <td align="right">- Q{{ number_format($venta->descuento, 2) }}</td>
                                    </tr>
                                @endif

                                {{-- PROMO CARRITO --}}
                                @if ($venta->promociones)
                                    @php
                                        $promo = $venta->promociones;
                                        $promoMonto = 0;

                                        if ($promo['tipo'] === 'porcentaje') {
                                            $promoMonto = $venta->subtotal * ($promo['valor'] / 100);
                                        } else {
                                            $promoMonto = $promo['valor'];
                                        }
                                    @endphp

                                    <tr>
                                        <td align="right">
                                            {{ $promo['nombre'] ?? 'Promoción' }}
                                        </td>
                                        <td align="right">
                                            - Q{{ number_format($promoMonto, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                @if ($venta->costo_envio > 0)
                                    <tr>
                                        <td align="right">Envío:</td>
                                        <td align="right">Q{{ number_format($venta->costo_envio, 2) }}</td>
                                    </tr>
                                @endif

                                <tr>
                                    <td align="right"><strong>Total:</strong></td>
                                    <td align="right">
                                        <strong style="color:#00432C;">
                                            Q{{ number_format($venta->total, 2) }}
                                        </strong>
                                    </td>
                                </tr>
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
