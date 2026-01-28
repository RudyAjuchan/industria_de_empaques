<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Venta #{{ $venta->id }}</title>

    <style>
        @page {
            margin: 20px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 0.6rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #666;
            padding: 2px 3px;
            vertical-align: top;
        }

        th {
            background: #f3f3f3;
            text-align: left;
        }

        .no-border td, .no-border th {
            border: none;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .total {
            font-size: 14px;
            font-weight: bold;
            color: darkred;
        }
    </style>
</head>
<body>

@include('pdf.venta.header')
@include('pdf.venta.cliente-pago')
@include('pdf.venta.detalle-productos')
@include('pdf.venta.resumen')
@include('pdf.venta.orden-produccion')

</body>
</html>
