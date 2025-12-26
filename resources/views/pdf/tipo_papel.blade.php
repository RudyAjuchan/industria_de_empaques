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

    <h1>Listado de Tipos de papel</h1>

    @if ($search)
        <div class="filter">
            Filtro aplicado: <strong>{{ $search }}</strong>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Creado</th>
                <th>Actualizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tipo_papel as $tipo)
                <tr>
                    <td>{{ $tipo->nombre }}</td>
                    <td>{{ $tipo->created_at }}</td>
                    <td>{{ $tipo->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
