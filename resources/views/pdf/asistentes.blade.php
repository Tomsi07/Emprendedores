{{-- Guardar como: resources/views/pdf/asistentes.blade.php --}}
@php
    // Responsables y contacto pueden llegar como texto o como lista
    $texto = fn ($v) => is_array($v)
        ? (implode(', ', array_filter($v)) ?: '-')
        : (filled($v) ? $v : '-');

    $fechaEvento = $evento->fecha ? \Carbon\Carbon::parse($evento->fecha)->format('d/m/Y') : '-';
    $exportado   = $exportadoEl->format('d/m/Y H:i');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Emprendimientos - {{ $evento->nombreEvento }}</title>
    <style>
        @page { margin: 95px 36px 60px 36px; }

        body {
            font-family: 'DejaVu Sans', sans-serif; /* soporta tildes y ñ */
            font-size: 10px;
            color: #0e1729;
        }

        header {
            position: fixed;
            top: -65px;
            left: 0;
            right: 0;
            border-bottom: 2px solid #1d4b44;
            padding-bottom: 6px;
            font-size: 9px;
            color: #475569;
        }
        header table { width: 100%; border-collapse: collapse; }
        header td { vertical-align: bottom; }
        header td.der { text-align: right; }
        header .titulo {
            font-size: 20px;
            font-weight: bold;
            color: #1d4b44;
        }

        footer {
            position: fixed;
            bottom: -38px;
            left: 0;
            right: 0;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            font-size: 9px;
            color: #475569;
        }
        footer .pagina:after { content: "Página " counter(page); }

        h1 { font-size: 20px; margin: 0 0 10px 0; color: #1d4b44; }

        table.datos { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.datos td {
            width: 25%;
            padding: 8px 10px;
            background: #eef6f4;
            border: 1px solid #cfe3de;
            vertical-align: top;
        }
        .etiqueta { font-size: 8px; color: #475569; }
        .valor { font-size: 11px; font-weight: bold; }

        .total { margin: 10px 0 16px 0; font-size: 12px; }
        .total strong { color: #1d4b44; }

        table.lista { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.lista thead { display: table-header-group; } /* repite los títulos en cada página */
        table.lista tr { page-break-inside: avoid; }
        table.lista th.rubro {
            background: #1d4b44;
            color: #ffffff;
            text-align: left;
            font-size: 11px;
            padding: 6px 8px;
        }
        table.lista tr.cols th {
            background: #dcebe7;
            color: #1d4b44;
            text-align: left;
            font-size: 9px;
            padding: 5px 8px;
            border-bottom: 1px solid #bcd8d1;
        }
        table.lista td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        table.lista tbody tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>

<header>
    <table>
        <tr>
            <td class="titulo">Emprendimientos</td>
            <td class="der">
                Asistencias &mdash; {{ $evento->nombreEvento }}<br>
                Exportado el {{ $exportado }}
            </td>
        </tr>
    </table>
</header>

<footer>
    Secretaría de Desarrollo Local, Municipalidad de Saladillo
    &nbsp;&middot;&nbsp;
    <span class="pagina"></span>
</footer>

<main>
    <h1>{{ $evento->nombreEvento }}</h1>

    <table class="datos">
        <tr>
            <td>
                <div class="etiqueta">Fecha</div>
                <div class="valor">{{ $fechaEvento }}</div>
            </td>
            <td>
                <div class="etiqueta">Lugar</div>
                <div class="valor">{{ $evento->lugar ?: '-' }}</div>
            </td>
            <td>
                <div class="etiqueta">Ciudad</div>
                <div class="valor">{{ $evento->ciudad ?: '-' }}</div>
            </td>
            <td>
                <div class="etiqueta">Tipo de evento</div>
                <div class="valor">{{ $evento->tipoDeEvento ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <p class="total">
        Emprendimientos que asistieron: <strong>{{ $total }}</strong>
        &nbsp;&middot;&nbsp; Datos exportados el <strong>{{ $exportado }}</strong>
    </p>

    @foreach ($porRubro as $rubro => $items)
        <table class="lista">
            <thead>
                <tr>
                    <th class="rubro" colspan="3">{{ $rubro }} ({{ $items->count() }})</th>
                </tr>
                <tr class="cols">
                    <th style="width: 40%;">Emprendimiento</th>
                    <th style="width: 30%;">Responsables</th>
                    <th style="width: 30%;">Contacto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $a)
                    <tr>
                        <td><strong>{{ $a->nombreEmprendimiento }}</strong></td>
                        <td>{{ $texto($a->responsables ?? null) }}</td>
                        <td>
                            @forelse ((array) ($a->contacto ?? []) as $linea)
                                {{ $linea }}@if (! $loop->last)<br>@endif
                            @empty
                                -
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</main>

</body>
</html>