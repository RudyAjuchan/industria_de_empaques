<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h1 {
            text-align: center;
            margin-bottom: 8px;
        }

        .filter {
            margin-bottom: 10px;
            font-size: 10px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #167160;
            color: #fff;
            text-align: left;
            font-size: 11px;
        }

        .small {
            font-size: 10px;
            color: #444;
        }
    </style>
</head>

<body>

    <h1>Listado de Clientes</h1>

    @if (!empty($search))
        <div class="filter">
            Filtro aplicado: <strong>{{ $search }}</strong>
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Contactos</th>
                <th>Ubicación</th>
                <th>DPI</th>
                <th>NIT</th>
                <th>Dirección</th>
                <th>Creado</th>
                <th>Actualizado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($clientes as $cli)
                <tr>
                    {{-- Nombre --}}
                    <td>{{ $cli->nombre }}</td>

                    {{-- Contactos --}}
                    <td class="small">
                        {{-- Emails --}}
                        @if ($cli->emails->count())
                            <strong>Emails:</strong><br>
                            @foreach ($cli->emails as $email)
                                {{ $email->email }}<br>
                            @endforeach
                        @endif

                        {{-- Teléfonos --}}
                        @if ($cli->telefonos->count())
                            <br><strong>Teléfonos:</strong><br>
                            @foreach ($cli->telefonos as $tel)
                                {{ $tel->telefono_codigo_pais }} {{ $tel->telefono_numero }}<br>
                            @endforeach
                        @endif
                    </td>

                    {{-- Ubicación --}}
                    <td class="small">
                        @if ($cli->municipio)
                            {{ $cli->municipio->nombre }},
                            {{ $cli->municipio->departamento->nombre }}
                        @else
                            {{ $cli->ciudad_pais }}<br>
                            {{ $cli->estado_pais }}
                        @endif
                    </td>

                    {{-- DPI --}}
                    <td>{{ $cli->dpi }}</td>

                    {{-- NIT --}}
                    <td>{{ $cli->nit }}</td>

                    {{-- Dirección --}}
                    <td>{{ $cli->direccion }}</td>

                    {{-- Fechas --}}
                    <td>{{ $cli->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $cli->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:10px;">
                        No se encontraron registros
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
