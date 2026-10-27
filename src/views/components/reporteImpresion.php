<?php
/**
 * Documento imprimible de un reporte (admin y CEO).
 * Se renderiza como página independiente, sin la interfaz de la aplicación,
 * pensado para guardarse como PDF en pocas hojas A4.
 *
 * Variable esperada: $informe = [
 *   'titulo'       => 'Reporte de ventas',
 *   'alcance'      => 'Todas las aerolíneas',
 *   'meta'         => ['Período' => '...', 'Filtros' => '...'],
 *   'conclusiones' => ['texto', ...],
 *   'indicadores'  => [...]   // formato de repResumen()
 *   'secciones'    => [['titulo' => ..., 'columnas' => ..., 'filas' => ..., 'vacio' => ...?], ...],
 *   'detalle'      => ['titulo' => ..., 'columnas' => ..., 'filas' => ..., 'nota' => ...],
 *   'volver'       => 'index.php?...',
 * ]
 */
require_once __DIR__ . '/reporteBloques.php';

$usuarioInforme = usuarioActual();
$autorInforme = trim(($usuarioInforme['nombre'] ?? '') . ' ' . ($usuarioInforme['apellido'] ?? ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($informe['titulo']) ?> — Volaré</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 14mm;
            @bottom-right { content: "Página " counter(page) " de " counter(pages); font: 8px Arial, sans-serif; color: #7a8699; }
            @bottom-left { content: "Volaré · <?= htmlspecialchars($informe['titulo']) ?>"; font: 8px Arial, sans-serif; color: #7a8699; }
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #e9edf2;
            color: #172033;
            font: 11px/1.4 Arial, Helvetica, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .barra-acciones {
            position: sticky;
            top: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #031f47;
            z-index: 1;
        }

        .barra-acciones a,
        .barra-acciones button {
            font: 600 13px Arial, sans-serif;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #fff;
            background: transparent;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
        }

        .barra-acciones button { background: #fff; color: #031f47; }

        .hoja {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 12mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        .encabezado {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 16px;
            padding-bottom: 8px;
            border-bottom: 3px solid #123d72;
        }


        .marca { font-size: 10px; font-weight: 700; letter-spacing: 2px; color: #2878d7; text-transform: uppercase; }
        .encabezado h1 { margin: 2px 0 0; font-size: 22px; color: #123d72; }
        .encabezado .alcance { margin: 2px 0 0; font-size: 11px; color: #596579; }

        .meta { font-size: 9.5px; color: #3c475a; border-collapse: collapse; }
        .meta th { text-align: right; padding: 1px 6px 1px 0; color: #7a8699; font-weight: 600; white-space: nowrap; }
        .meta td { padding: 1px 0; white-space: nowrap; }

        h2 {
            margin: 12px 0 6px;
            font-size: 12px;
            color: #123d72;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sintesis {
            margin: 0;
            padding: 8px 10px 8px 24px;
            background: #f3f7fc;
            border-left: 3px solid #2878d7;
        }

        .sintesis li { margin: 2px 0; }

        /* Indicadores (repResumen) */
        .rep-resumen {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border: 1px solid #dbe2ea;
            border-radius: 6px;
            overflow: hidden;
        }

        .rep-indicador {
            display: flex;
            flex-direction: column;
            padding: 6px 9px;
            border-right: 1px solid #edf1f5;
            border-bottom: 1px solid #edf1f5;
        }

        .rep-indicador-label { font-size: 8.5px; font-weight: 700; color: #7a8699; text-transform: uppercase; letter-spacing: 0.3px; }
        .rep-indicador-valor { font-size: 16px; font-weight: 700; }
        .rep-indicador-detalle { font-size: 9px; color: #596579; }
        .rep-ok .rep-indicador-valor { color: #198754; }
        .rep-alerta .rep-indicador-valor { color: #b02a37; }

        /* Análisis (repTablaMini) */
        .analisis {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .rep-analisis-card {
            border: 1px solid #dbe2ea;
            border-radius: 6px;
            padding: 6px 8px;
            break-inside: avoid;
        }

        .rep-analisis-titulo { margin: 0 0 4px; font-size: 10.5px; color: #123d72; }
        .rep-analisis-vacio { margin: 0; color: #7a8699; }
        .rep-mini { font-size: 10px; }

        table { width: 100%; border-collapse: collapse; }
        .rep-mini th, .detalle th {
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
            color: #596579;
            background: #eef3f8;
            padding: 3px 5px;
        }
        .rep-mini td, .detalle td { padding: 2.5px 5px; border-bottom: 1px solid #eef1f5; }
        .rep-mini tr:last-child td { border-bottom: 0; }
        .text-end { text-align: right !important; }
        .text-center { text-align: center !important; }

        .rep-barra { display: flex; align-items: center; gap: 5px; }
        .rep-barra-base { flex: 1; min-width: 40px; height: 6px; background: #e8edf3; border-radius: 4px; overflow: hidden; }
        .rep-barra-valor { height: 100%; background: #2878d7; }
        .rep-barra span { min-width: 34px; text-align: right; }

        .detalle { font-size: 9.5px; }
        .detalle thead { display: table-header-group; }
        .detalle tr { break-inside: avoid; }
        .detalle tbody tr:nth-child(even) td { background: #fafbfd; }
        .detalle tfoot td { font-weight: 700; border-top: 2px solid #123d72; background: #f3f7fc; }
        .nota { margin: 4px 0 0; font-size: 9px; color: #7a8699; }

        .pie {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #dbe2ea;
            font-size: 8.5px;
            color: #7a8699;
        }

        @media print {
            body { background: #fff; }
            .barra-acciones { display: none; }
            .hoja { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="barra-acciones">
        <a href="<?= htmlspecialchars(url($informe['volver'])) ?>">Volver al reporte</a>
        <button type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    <main class="hoja">
        <header class="encabezado">
            <div>
                <div class="marca">Volaré · Informe de gestión</div>
                <h1><?= htmlspecialchars($informe['titulo']) ?></h1>
                <p class="alcance"><?= htmlspecialchars($informe['alcance']) ?></p>
            </div>
            <table class="meta">
                <?php foreach ($informe['meta'] as $clave => $valor): ?>
                    <tr><th scope="row"><?= htmlspecialchars($clave) ?></th><td><?= htmlspecialchars($valor) ?></td></tr>
                <?php endforeach; ?>
                <tr><th scope="row">Generado</th><td><?= date('d/m/Y H:i') ?><?= $autorInforme !== '' ? ' por ' . htmlspecialchars($autorInforme) : '' ?></td></tr>
            </table>
        </header>

        <?php if (!empty($informe['conclusiones'])): ?>
            <h2>Síntesis</h2>
            <ul class="sintesis">
                <?php foreach ($informe['conclusiones'] as $conclusion): ?>
                    <li><?= htmlspecialchars($conclusion) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Indicadores principales</h2>
        <?php repResumen($informe['indicadores']); ?>

        <h2>Análisis</h2>
        <section class="analisis">
            <?php foreach ($informe['secciones'] as $sec): ?>
                <?php repTablaMini($sec['titulo'], $sec['columnas'], $sec['filas'], $sec['vacio'] ?? 'Sin datos para el período.'); ?>
            <?php endforeach; ?>
        </section>

        <?php $det = $informe['detalle']; ?>
        <h2><?= htmlspecialchars($det['titulo']) ?></h2>
        <?php if (empty($det['filas'])): ?>
            <p class="nota">No hay registros para los filtros seleccionados.</p>
        <?php else: ?>
            <table class="detalle">
                <thead>
                    <tr>
                        <?php foreach ($det['columnas'] as $col): ?>
                            <th scope="col" class="<?= htmlspecialchars($col['clase'] ?? '') ?>"><?= htmlspecialchars($col['label']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($det['filas'] as $fila): ?>
                        <tr>
                            <?php foreach (array_values($fila) as $i => $celda): ?>
                                <td class="<?= htmlspecialchars($det['columnas'][$i]['clase'] ?? '') ?>"><?= htmlspecialchars((string)$celda) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if (!empty($det['totales'])): ?>
                    <tfoot>
                        <tr>
                            <?php foreach (array_values($det['totales']) as $i => $celda): ?>
                                <td class="<?= htmlspecialchars($det['columnas'][$i]['clase'] ?? '') ?>"><?= htmlspecialchars((string)$celda) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
            <?php if (!empty($det['nota'])): ?>
                <p class="nota"><?= htmlspecialchars($det['nota']) ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <footer class="pie">
            Información calculada sobre reservas confirmadas salvo indicación contraria. Los importes están expresados en pesos argentinos.
        </footer>
    </main>

    <script>
        // Abre el diálogo de impresión al cargar; la barra superior permite volver o reimprimir.
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });
    </script>
</body>
</html>
