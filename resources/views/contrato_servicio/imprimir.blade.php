<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Servicio</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        h1, h2 {
            text-align: center;
        }
        .seccion {
            margin-top: 25px;
        }
        .titulo {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .firma {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .firma div {
            width: 40%;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

<button onclick="window.print()">🖨️ Imprimir</button>

<h1>CONTRATO DE SERVICIO</h1>

<p>
    En la fecha <strong>{{ $contrato->contrato_fecha }}</strong>, 
    entre la empresa <strong>{{ $contrato->empresa->emp_razon_social }}</strong>, 
    con sucursal <strong>{{ $contrato->sucursal->suc_razon_social }}</strong>, 
    y el cliente <strong>{{ $contrato->cliente->cli_nombre }} {{ $contrato->cliente->cli_apellido }}</strong>, 
    RUC <strong>{{ $contrato->cliente->cli_ruc }}</strong>, 
    se celebra el presente contrato.
</p>

<div class="seccion">
    <div class="titulo">1. Objeto</div>
    <p>{{ $contrato->contrato_objeto }}</p>
</div>

<div class="seccion">
    <div class="titulo">2. Alcance</div>
    <p>{{ $contrato->contrato_alcance }}</p>
</div>

<div class="seccion">
    <div class="titulo">3. Responsabilidad</div>
    <p>{{ $contrato->contrato_responsabilidad }}</p>
</div>

<div class="seccion">
    <div class="titulo">4. Garantía</div>
    <p>{{ $contrato->contrato_garantia }}</p>
</div>

<div class="seccion">
    <div class="titulo">5. Limitación</div>
    <p>{{ $contrato->contrato_limitacion }}</p>
</div>

<div class="seccion">
    <div class="titulo">6. Fuerza mayor</div>
    <p>{{ $contrato->contrato_fuerza_mayor }}</p>
</div>

<div class="seccion">
    <div class="titulo">7. Jurisdicción</div>
    <p>{{ $contrato->contrato_jurisdiccion }}</p>
</div>

<div class="seccion">
    <div class="titulo">Condiciones de Pago</div>
    <p>
        Condición: <strong>{{ $contrato->contrato_condicion_pago }}</strong><br>
        Cuotas: <strong>{{ $contrato->contrato_cuotas ?? 'N/A' }}</strong>
    </p>
</div>

<div class="firma">
    <div>Firma Empresa</div>
    <div>Firma Cliente</div>
</div>

<p style="margin-top:40px; font-size: 12px;">
    Documento generado por el sistema. Usuario responsable: {{ $contrato->user->name }}
</p>

</body>
</html>
