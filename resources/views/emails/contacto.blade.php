<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo mensaje</title>
</head>
<body style="font-family: Arial; background:#f4f4f4; padding:20px;">

    <div style="max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px;">
        
        <h2 style="color:#00432C;">Nuevo mensaje de contacto</h2>

        <p><strong>Nombre:</strong> {{ $data['nombre'] }}</p>
        <p><strong>Correo:</strong> {{ $data['email'] }}</p>
        <p><strong>Mensaje:</strong></p>

        <p style="background:#f9f9f9; padding:10px; border-radius:5px;">
            {{ $data['mensaje'] }}
        </p>

    </div>

</body>
</html>