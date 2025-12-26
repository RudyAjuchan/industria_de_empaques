<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Roles</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h1 {
            color: #167160;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 11px;
            margin-bottom: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #167160;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        td {
            border: 1px solid #ddd;
            padding: 6px;
            vertical-align: top;
        }

        .permissions {
            font-size: 10px;
            color: #444;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>

<h1>Listado de Roles</h1>

@if($search)
    <div class="subtitle">
        Filtro aplicado: <b>{{ $search }}</b>
    </div>
@endif

<table>
    <thead>
        <tr>
            <th style="width: 25%">Rol</th>
            <th style="width: 15%"># Permisos</th>
            <th>Permisos</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($roles as $role)
            <tr>
                <td>{{ $role->name }}</td>
                <td>{{ $role->permissions->count() }}</td>
                <td class="permissions">
                    {{ $role->permissions->pluck('name')->join(', ') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No hay roles para mostrar</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>
