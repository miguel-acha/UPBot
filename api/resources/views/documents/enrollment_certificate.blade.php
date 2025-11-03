<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia de Inscripción</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #222;
            margin: 40px;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .box {
            border: 1px solid #444;
            border-radius: 6px;
            padding: 20px;
        }
        p {
            margin: 8px 0;
        }
        .footer {
            margin-top: 40px;
            text-align: right;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="title">CONSTANCIA DE INSCRIPCIÓN</div>

    <div class="box">
        <p><strong>Estudiante:</strong> {{ $student_name }} ({{ $student_code }})</p>
        <p><strong>Programa:</strong> {{ $program }}</p>
        <p><strong>Campus:</strong> {{ $campus }}</p>
        <p><strong>Estado Académico:</strong> {{ $status }}</p>
        <p><strong>Semestre:</strong> {{ $semester }}</p>
        @if(!empty($message))
            <p><em>{{ $message }}</em></p>
        @endif
    </div>

    <div class="footer">
        Emitido el {{ now()->format('d/m/Y') }}
    </div>

</body>
</html>
