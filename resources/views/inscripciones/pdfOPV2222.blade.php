<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        
        .header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .logo-container {
            width: 25%;
            float: left;
        }
        
        .institution-info {
            width: 40%;
            float: left;
            font-size: 11px;
            line-height: 1.2;
        }
        
        .code-container {
            width: 25%;
            float: right;
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        
        .title {
            clear: both;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0 15px 0;
        }
        
        .info-row {
            margin: 5px 0;
            clear: both;
        }
        
        .info-row span {
            text-decoration: underline;
            color: #FF0000;
        }
        
        /* Estilos modificados para las tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            table-layout: fixed; /* Importante: establece ancho fijo de columnas */
        }
        
        th {
            background-color: #000;
            color: #fff;
            padding: 5px;
            text-align: left;
            border: 1px solid #000;
            overflow-wrap: break-word; /* Permite que las palabras se rompan */
            word-wrap: break-word;
        }
        
        td {
            border: 1px solid #000;
            padding: 5px;
            overflow-wrap: break-word; /* Permite que las palabras se rompan */
            word-wrap: break-word;
            vertical-align: top; /* Alinea el texto a la parte superior de la celda */
        }
        
        /* primer columna más ancha */
        .tutor-table th:nth-child(1),
        .tutor-table td:nth-child(1) {
        width: 22%;
        }
        /* segunda columna un poco más estrecha */
        .tutor-table th:nth-child(2),
        .tutor-table td:nth-child(2) {
        width: 33%;
        }
        /* tercera columna… */
        .tutor-table th:nth-child(3),
        .tutor-table td:nth-child(3) {
        width: 34%;
        }
        /* cuarta columna… */
        .tutor-table th:nth-child(4),
        .tutor-table td:nth-child(4) {
        width: 11%;
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .red-text {
            color: #FF0000;
        }
        
        .watermark {
            width: 120px;
            position: absolute;
            top: 10px;
            left: 10px;
        }
        
        .sansi-logo {
            color: #FF0000;
            font-weight: bold;
            font-size: 24px;
        }
        
        .tutor-table {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="watermark">
        <img src="{{ public_path('img/logo-ohsansi.png') }}" alt="Logo UMSS" style="width: 100%; height: auto;">
    </div>
    
    <div class="header">
        <div class="logo-container">
            <!-- El logo ya está como watermark -->
        </div>
        
        <div class="institution-info">
            <p>Universidad Mayor de San Simon</p>
            <p>Ciencia y Conocimiento Desde 1832</p>
            <p>Dirección Av. Oquendo final Jordán s/n</p>
        </div>
        
        <div class="code-container">
            <p>CODIGO ORDEN DE PAGO:</p>
            <p>{{ $codigoOrden }}</p>
        </div>
    </div>
    
    <div class="title">ORDEN DE PAGO DE UN ESTUDIANTE</div>
    
    <div class="info-row">
        Fecha generación de la Orden de Pago: {{ $fechaGeneracion }}
    </div>
    {{-- <div class="info-row">
        Fecha limite valida de la orden de Pago: {{ $fechaVencimiento }}
    </div> --}}
    
    <div class="info-row">
        Referencia: Inscripciones Oh-Sansi!
    </div>
    
    @foreach($tutores as $tutor)
    <table class="tutor-table">
        <tr>
            <th colspan="4">DATOS DE TUTOR</th>
        </tr>
        <tr>
            <td><strong>Nombre(s):</strong></td>
            <td><strong>Apellido Paterno:</strong></td>
            <td><strong>Apellido Materno:</strong></td>
            <td><strong>CI:</strong></td>            
        </tr>
        <tr>
            <td>{{ $tutor['nombre'] }}</td>
            <td>{{ $tutor['apellido_paterno'] }}</td>
            <td>{{ $tutor['apellido_materno'] }}</td>
            <td>{{ $tutor['ci'] }}</td>
        </tr>
        <tr>
            <td><strong>Profesión:</strong></td>
            <td><strong>Área: </strong></td>
            <td><strong>Colegio: </strong></td>
            <td></td>
        </tr>
        <tr>
            <td>{{ $tutor['profesion'] }}</td>
            <td>{{ implode(', ', $tutor['areas']) }}</td>
            <td>{{ $tutor['colegio'] }}</td>
            <td></td>
        </tr>
    </table>
    @endforeach

    {{-- DATOS DEL ESTUDIANTE --}}
    <table>
        <tr>
            <th colspan="4">DATOS DEL ESTUDIANTE</th>
        </tr>
        <tr>
            <td><strong>Nombre(s):</strong></td>
            <td><strong>Apellido Paterno:</strong></td>
            <td><strong>Apellido Materno:</strong></td>
            <td><strong>CI:</strong></td> 
        </tr>
        <tr>
            <td>{{ $estudiante['nombre'] }}</td>
            <td>{{ $estudiante['apellido_paterno'] }}</td>
            <td>{{ $estudiante['apellido_materno'] }}</td>
            <td>{{ $estudiante['ci'] }}</td>
        </tr>
    </table>
    
    {{-- INSCRIPCIONES DEL ESTUDIANTE --}}
    <table>
        <tr>
            <th colspan="3">DATOS DE INSCRIPCION DEL ESTUDIANTE</th>
        </tr>
        <tr>
            <td><strong>AREA: </strong></td>
            <td><strong>CATEGORIA:</strong></td>
            <td><strong>GRADO:</strong></td>
        </tr>
        @foreach($inscripciones as $inscripcion)
        <tr>
            <td>{{ $inscripcion['area'] }}</td>
            <td>{{ $inscripcion['categoria'] }}</td>
            <td>{{ $estudiante['grado'] }}</td>
        </tr>
        @endforeach
    </table>
    
    {{-- DETALLE DE PAGO --}}
    <div>
        DETALLE:
    </div>
    <table>
        <tr>
            <th width="20%">MODALIDAD</th>
            <th width="60%">CONCEPTO</th>
            <th width="20%">MONTO(Bs)</th>
        </tr>

        @foreach($inscripciones as $inscripcion)
        <tr>
            <td>{{ $inscripcion['modalidad'] }}</td>
            <td>Inscripción en el Área: <span class="red-text">{{ $inscripcion['area'] }}</span> Categoria: <span class="red-text">{{ $inscripcion['categoria'] }}</span></td>
            <td>{{ $inscripcion['precio'] }}</td>
        </tr>
        @endforeach
        
        <tr class="total-row">
            <td colspan="2">TOTAL A PAGAR:</td>
            <td>{{ $totalPagar }}</td>
        </tr>
    </table>
    
    <div class="footer">
        <p>Imprima esta hoja y diríjase a cajas facultativas para realizar el pago. Tenga en cuenta que el pago debe realizarlo una persona mayor de 18 años, un Tutor puede efectuar el pago de la inscripción.</p>
    </div>
</body>
</html>