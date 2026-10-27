<?php
/**
 * Bloques compartidos de los reportes (admin y CEO):
 * - repResumen():   franja compacta de indicadores.
 * - repTablaMini(): tabla de análisis chica (top rutas, por aerolínea, por mes...).
 *
 * Están pensados para que en pantalla y en el PDF la información clave
 * quepa en la primera hoja, antes del detalle.
 */

if (!function_exists('repResumen')) {

    function repNum($valor, int $decimales = 0): string {
        return number_format((float)$valor, $decimales, ',', '.');
    }

    function repPesos($valor): string {
        return '$ ' . number_format((float)$valor, 0, ',', '.');
    }

    function repPct($parte, $total, int $decimales = 0): string {
        return (float)$total > 0
            ? number_format(((float)$parte / (float)$total) * 100, $decimales, ',', '.') . '%'
            : '—';
    }

    function repMes(string $anioMes): string {
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        [$anio, $mes] = array_pad(explode('-', $anioMes), 2, '01');
        return ($meses[(int)$mes - 1] ?? $mes) . ' ' . $anio;
    }

    /** Celda con barra proporcional: ['barra' => 0..100, 'texto' => '45%'] */
    function repBarra(float $porcentaje, string $texto): array {
        return ['barra' => max(0, min(100, $porcentaje)), 'texto' => $texto];
    }

    /**
     * @param array $items [['label' => ..., 'valor' => ..., 'detalle' => ...?, 'tono' => 'ok|alerta'?], ...]
     */
    function repResumen(array $items): void { ?>
        <section class="rep-resumen" aria-label="Indicadores principales">
            <?php foreach ($items as $item): ?>
                <div class="rep-indicador <?= !empty($item['tono']) ? 'rep-' . htmlspecialchars($item['tono']) : '' ?>">
                    <span class="rep-indicador-label"><?= htmlspecialchars($item['label']) ?></span>
                    <span class="rep-indicador-valor" title="<?= htmlspecialchars($item['titulo'] ?? $item['valor']) ?>"><?= htmlspecialchars($item['valor']) ?></span>
                    <?php if (!empty($item['detalle'])): ?>
                        <span class="rep-indicador-detalle"><?= htmlspecialchars($item['detalle']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
    <?php }

    /**
     * @param array $columnas [['label' => 'Ruta', 'clase' => 'text-end'?], ...]
     * @param array $filas    filas con un valor por columna (string o repBarra())
     */
    function repTablaMini(string $titulo, array $columnas, array $filas, string $vacio = 'Sin datos para el período.'): void { ?>
        <div class="rep-analisis-card">
            <h3 class="rep-analisis-titulo"><?= htmlspecialchars($titulo) ?></h3>
            <?php if (empty($filas)): ?>
                <p class="rep-analisis-vacio"><?= htmlspecialchars($vacio) ?></p>
            <?php else: ?>
                <table class="table table-sm rep-mini mb-0">
                    <thead>
                        <tr>
                            <?php foreach ($columnas as $col): ?>
                                <th scope="col" class="<?= htmlspecialchars($col['clase'] ?? '') ?>"><?= htmlspecialchars($col['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filas as $fila): ?>
                            <tr>
                                <?php foreach (array_values($fila) as $i => $celda): ?>
                                    <td class="<?= htmlspecialchars($columnas[$i]['clase'] ?? '') ?>">
                                        <?php if (is_array($celda)): ?>
                                            <div class="rep-barra">
                                                <div class="rep-barra-base"><div class="rep-barra-valor" style="width: <?= round($celda['barra']) ?>%;"></div></div>
                                                <span><?= htmlspecialchars($celda['texto']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <?= htmlspecialchars((string)$celda) ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php }
}
