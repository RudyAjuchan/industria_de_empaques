<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
</head>

<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:20px; margin:0;">
    @php
        $detalles = $venta->detalles ?? collect();
        $productos = $detalles->count();
        $unidades = $detalles->sum('cantidad');
        $subtotalProductos = $detalles->sum(fn($item) => (float) $item->precio * (int) $item->cantidad);
        $descuentoProductos = $detalles->sum(function ($item) {
            return max(0, ((float) $item->precio * (int) $item->cantidad) - (float) $item->total);
        });
        $subtotalConPromociones = (float) $venta->subtotal;
        $descuentoManual = (float) ($venta->descuento ?? 0);
        $promoCarrito = $venta->promociones;
        $descuentoCarrito = 0;

        if ($promoCarrito) {
            if (($promoCarrito['tipo'] ?? null) === 'porcentaje') {
                $descuentoCarrito = $subtotalConPromociones * ((float) ($promoCarrito['valor'] ?? 0) / 100);
            } else {
                $descuentoCarrito = (float) ($promoCarrito['valor'] ?? 0);
            }
        }

        $totalDescuentos = $descuentoProductos + $descuentoManual + $descuentoCarrito;
        $pagado = $venta->pagos->sum('monto');
        $pendiente = max(0, (float) $venta->total - (float) $pagado);
    @endphp

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="max-width:600px; background:#ffffff; border-radius:10px; overflow:hidden;">

                    <tr>
                        <td style="background:#00432C; color:white; padding:20px; text-align:center;">
                            <img src="{{ config('app.url') }}/img/jeple_logo.png" width="120"
                                style="display:block; margin:0 auto 10px; border:0;">
                            <h2 style="margin:0;">Jeple Store</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px; color:#333;">
                            <h3 style="color:#00432C; margin:0 0 10px;">Hola {{ $cliente->nombre }}</h3>

                            <p style="margin:0 0 8px; line-height:1.5;">
                                Tu solicitud fue revisada y ya quedó confirmada como pedido.
                            </p>

                            <p style="margin:0 0 18px; line-height:1.5;">
                                A continuación encontrarás el resumen de productos, descuentos y saldo de tu pedido.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f4f8f1; border:1px solid #d9ead0; border-radius:8px;">
                                <tr>
                                    <td style="padding:14px;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:13px; color:#5d6b7a;">Pedido</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:22px; font-weight:bold; color:#111; padding-top:3px;">
                                                    {{ $venta->serie }}-{{ str_pad($venta->numero, 6, '0', STR_PAD_LEFT) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:13px; color:#5d6b7a; padding-top:10px;">Estado</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:15px; font-weight:bold; color:#2e7d32; padding-top:3px;">
                                                    Confirmado
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <br>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#fafafa; border:1px solid #eeeeee; border-radius:8px;">
                                <tr>
                                    <td style="padding:10px; font-size:13px; color:#5d6b7a;">Productos</td>
                                    <td align="right" style="padding:10px; font-size:20px; font-weight:bold; color:#111;">
                                        {{ $productos }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px; font-size:13px; color:#5d6b7a; border-top:1px solid #eeeeee;">Unidades</td>
                                    <td align="right" style="padding:10px; font-size:20px; font-weight:bold; color:#111; border-top:1px solid #eeeeee;">
                                        {{ number_format($unidades) }}
                                    </td>
                                </tr>
                            </table>

                            <br>

                            <h4 style="color:#00432C; margin:0 0 10px;">Detalle de productos</h4>

                            @foreach ($detalles as $item)
                                @php
                                    $promo = $item->promocion_aplicada;
                                    $productoNombre = $item->producto_nombre ?? $item->producto?->nombre ?? 'Producto';
                                    $imagen = $item->producto?->imagen_principal_url;
                                    $subtotalLinea = (float) $item->precio * (int) $item->cantidad;
                                    $descuentoLinea = max(0, $subtotalLinea - (float) $item->total);
                                    $esCotizacion = (float) $item->precio <= 0 || (float) $item->total <= 0;
                                @endphp

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="border:1px solid #eeeeee; border-radius:8px; margin-bottom:12px;">
                                    <tr>
                                        <td style="padding:12px;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td width="68" valign="top">
                                                        @if ($imagen)
                                                            <img src="{{ $imagen }}" width="56" height="56"
                                                                style="border-radius:6px; display:block; object-fit:cover; border:0;">
                                                        @endif
                                                    </td>

                                                    <td valign="top" style="padding-left:10px;">
                                                        <div style="font-size:11px; font-weight:bold; color:{{ $esCotizacion ? '#6b7280' : '#2e7d32' }};">
                                                            {{ $esCotizacion ? 'COTIZACIÓN' : 'COMPRA' }}
                                                        </div>
                                                        <div style="font-size:16px; font-weight:bold; color:#111; margin-top:4px;">
                                                            {{ $productoNombre }}
                                                        </div>

                                                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:6px;">
                                                            <tr>
                                                                <td style="font-size:13px; color:#5d6b7a; padding-bottom:3px;">
                                                                    Cantidad: <strong>{{ number_format($item->cantidad) }}</strong>
                                                                </td>
                                                            </tr>
                                                            @if (!$esCotizacion)
                                                                <tr>
                                                                    <td style="font-size:13px; color:#5d6b7a;">
                                                                        Precio unitario: <strong>Q{{ number_format($item->precio, 2) }}</strong>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        </table>

                                                        @if ($item->nombre_logo || $item->detalle_impresion || $item->color_agarrador || $item->tipoAgarrador || $item->tipoPapel)
                                                            <div style="font-size:13px; color:#374151; margin-top:8px; line-height:1.45;">
                                                                @if ($item->nombre_logo)
                                                                    <strong>Logo:</strong> {{ $item->nombre_logo }}<br>
                                                                @endif
                                                                @if ($item->detalle_impresion)
                                                                    <strong>Impresión:</strong> {{ $item->detalle_impresion }}<br>
                                                                @endif
                                                                @if ($item->color_agarrador)
                                                                    <strong>Color:</strong> {{ $item->color_agarrador }}<br>
                                                                @endif
                                                                @if ($item->tipoAgarrador)
                                                                    <strong>Agarrador:</strong> {{ $item->tipoAgarrador->nombre }}<br>
                                                                @endif
                                                                @if ($item->tipoPapel)
                                                                    <strong>Papel:</strong> {{ $item->tipoPapel->nombre }}
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @if ($promo)
                                                            <div style="font-size:12px; color:#d32f2f; margin-top:8px;">
                                                                {{ $promo['nombre'] ?? 'Promoción aplicada' }}
                                                                @if (($promo['tipo'] ?? null) === 'porcentaje')
                                                                    ({{ $promo['valor'] }}% de descuento)
                                                                @else
                                                                    (Q{{ number_format($promo['valor'], 2) }} de descuento)
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>

                                                </tr>
                                            </table>

                                            <table width="100%" cellpadding="0" cellspacing="0"
                                                style="margin-top:12px; border-top:1px solid #eeeeee;">
                                                <tr>
                                                    <td style="padding-top:10px; font-size:13px; color:#5d6b7a;">
                                                        Total del producto
                                                    </td>
                                                    <td align="right" style="padding-top:10px;">
                                                        @if ($esCotizacion)
                                                            <span style="font-size:13px; font-weight:bold; color:#6b7280;">A cotizar</span>
                                                        @else
                                                            @if ($descuentoLinea > 0)
                                                                <span style="font-size:12px; color:#9ca3af; text-decoration:line-through;">
                                                                    Q{{ number_format($subtotalLinea, 2) }}
                                                                </span>
                                                                <br>
                                                            @endif
                                                            <span style="font-size:18px; font-weight:bold; color:#111;">
                                                                Q{{ number_format($item->total, 2) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <br>

                            <h4 style="color:#00432C; margin:0 0 10px;">Resumen del pedido</h4>

                            <table width="100%" cellpadding="7" cellspacing="0"
                                style="border-collapse:collapse; background:#fafafa; border:1px solid #eeeeee;">
                                <tr>
                                    <td style="color:#5d6b7a;">Subtotal productos</td>
                                    <td align="right"><strong>Q{{ number_format($subtotalProductos, 2) }}</strong></td>
                                </tr>

                                @if ($descuentoProductos > 0)
                                    <tr>
                                        <td style="color:#5d6b7a;">Descuento productos</td>
                                        <td align="right" style="color:#d32f2f;">-Q{{ number_format($descuentoProductos, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="color:#5d6b7a;">Subtotal con promociones</td>
                                        <td align="right"><strong>Q{{ number_format($subtotalConPromociones, 2) }}</strong></td>
                                    </tr>
                                @endif

                                @if ($descuentoManual > 0)
                                    <tr>
                                        <td style="color:#5d6b7a;">Descuento adicional</td>
                                        <td align="right" style="color:#d32f2f;">-Q{{ number_format($descuentoManual, 2) }}</td>
                                    </tr>
                                @endif

                                @if ($promoCarrito)
                                    <tr>
                                        <td style="color:#5d6b7a;">
                                            {{ $promoCarrito['nombre'] ?? 'Descuento carrito' }}
                                        </td>
                                        <td align="right" style="color:#d32f2f;">-Q{{ number_format($descuentoCarrito, 2) }}</td>
                                    </tr>
                                @endif

                                @if ($totalDescuentos > 0)
                                    <tr>
                                        <td style="color:#5d6b7a;">Total descuentos</td>
                                        <td align="right" style="color:#d32f2f;"><strong>-Q{{ number_format($totalDescuentos, 2) }}</strong></td>
                                    </tr>
                                @endif

                                @if ($venta->costo_envio > 0)
                                    <tr>
                                        <td style="color:#5d6b7a;">Envío</td>
                                        <td align="right">Q{{ number_format($venta->costo_envio, 2) }}</td>
                                    </tr>
                                @endif
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background:#111111; color:#ffffff; border-radius:8px; margin-top:14px;">
                                <tr>
                                    <td style="padding:14px; font-weight:bold;">Total final</td>
                                    <td align="right" style="padding:14px; font-size:22px; font-weight:bold;">
                                        Q{{ number_format($venta->total, 2) }}
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="7" cellspacing="0" style="margin-top:10px;">
                                <tr>
                                    <td align="right" style="color:#5d6b7a;">Pagado</td>
                                    <td align="right" width="120">Q{{ number_format($pagado, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right" style="color:#5d6b7a;">Pendiente</td>
                                    <td align="right" width="120">
                                        <strong style="color:{{ $pendiente > 0 ? '#d32f2f' : '#2e7d32' }};">
                                            Q{{ number_format($pendiente, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:18px; padding:12px; background:#f4f8f1; border-radius:8px; color:#344054; line-height:1.5;">
                                Un asesor te contactará si necesitamos confirmar disponibilidad, personalización o tiempos
                                de entrega.
                            </div>

                            <p style="margin:18px 0 0;">
                                ¡Gracias por tu compra y tu confianza!
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#0C2A00; color:white; text-align:center; padding:20px 15px;">
                            <table align="center" border="0" cellpadding="0" cellspacing="0"
                                style="margin-bottom: 15px;">
                                <tr>
                                    <td style="padding: 0 10px;">
                                        <a href="https://www.facebook.com/jeple.guatemala" target="_blank"
                                            style="text-decoration: none;">
                                            <img src="{{ config('app.url') }}/img/facebook.png" width="30"
                                                height="30" alt="Facebook" style="display:block; border:0;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 10px;">
                                        <a href="https://www.instagram.com/jeple.embalajes" target="_blank"
                                            style="text-decoration: none;">
                                            <img src="{{ config('app.url') }}/img/instagram.png" width="30"
                                                height="30" alt="Instagram" style="display:block; border:0;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 10px;">
                                        <a href="https://wa.me/50232500812" target="_blank"
                                            style="text-decoration: none;">
                                            <img src="{{ config('app.url') }}/img/whatsapp.png" width="30"
                                                height="30" alt="WhatsApp" style="display:block; border:0;">
                                        </a>
                                    </td>
                                    <td style="padding: 0 10px;">
                                        <a href="https://www.tiktok.com/@jeple.embalajes" target="_blank"
                                            style="text-decoration: none;">
                                            <img src="{{ config('app.url') }}/img/tiktok.png" width="30"
                                                height="30" alt="Tiktok" style="display:block; border:0;">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size: 12px; opacity: 0.8;">
                                © {{ date('Y') }} Jeple Store
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
