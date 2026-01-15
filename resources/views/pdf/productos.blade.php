<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .filter {
            margin-bottom: 10px;
            font-size: 11px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #167160;
            color: #fff;
            text-align: left;
        }
    </style>
</head>

<body>

    <h1>Listado de Páginas</h1>

    @if ($search)
        <div class="filter">
            Filtro aplicado: <strong>{{ $search }}</strong>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Alto</th>
                <th>Ancho</th>
                <th>Fuelle</th>
                <th>Tipo</th>
                <th>Creado</th>
                <th>Actualizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>
                        @php
                            $img = optional($producto->imagenes->first())->path;
                        @endphp

                        @if ($img)
                            <img width="20" src="{{ public_path('storage/' . $img) }}">
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->alto }}</td>
                    <td>{{ $producto->ancho }}</td>
                    <td>{{ $producto->fuelle }}</td>
                    <td>{{ $producto->tipo }}</td>
                    <td>{{ $producto->created_at }}</td>
                    <td>{{ $producto->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
