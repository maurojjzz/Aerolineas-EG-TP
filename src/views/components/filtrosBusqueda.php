<?php
/**
 * Formulario de filtros de la búsqueda de vuelos.
 * Se reutiliza en la columna lateral (escritorio) y en el offcanvas (mobile).
 *
 * Variables esperadas:
 * - $facetas        ['aerolineas' => [], 'precioMin' => 0, 'precioMax' => 0]
 * - $filtrosActivos ['aerolineas' => [], 'horarios' => [], 'precioMin' => null, 'precioMax' => null]
 * - $paramsFiltros  parámetros de la búsqueda que se conservan al filtrar
 * - $idPrefijo      prefijo único para los id de los inputs
 * - $urlLimpiar     URL que quita todos los filtros
 */

$facetas        = $facetas ?? [];
$filtrosActivos = $filtrosActivos ?? [];
$paramsFiltros  = $paramsFiltros ?? [];
$idPrefijo      = $idPrefijo ?? 'filtros';
$urlLimpiar     = $urlLimpiar ?? url('index.php?pagina=buscar-vuelos');

$aerolineasFiltro = array_map('intval', $filtrosActivos['aerolineas'] ?? []);
$horariosFiltro   = $filtrosActivos['horarios'] ?? [];
$precioMinFiltro  = $filtrosActivos['precioMin'] ?? null;
$precioMaxFiltro  = $filtrosActivos['precioMax'] ?? null;

$facAerolineas = $facetas['aerolineas'] ?? [];
$facPrecioMin  = (float)($facetas['precioMin'] ?? 0);
$facPrecioMax  = (float)($facetas['precioMax'] ?? 0);

$franjasHorarias = [
    'madrugada' => 'Madrugada (00:00 a 05:59)',
    'manana'    => 'Mañana (06:00 a 11:59)',
    'tarde'     => 'Tarde (12:00 a 17:59)',
    'noche'     => 'Noche (18:00 a 23:59)',
];

$precioLegible = static fn($valor): string => '$ ' . number_format((float)$valor, 0, ',', '.');
?>
<form method="GET" action="<?= url('index.php?pagina=buscar-vuelos') ?>" class="filtros-form">
    <input type="hidden" name="pagina" value="buscar-vuelos">

    <?php foreach ($paramsFiltros as $nombreParam => $valorParam): ?>
        <?php if ($nombreParam === 'pagina' || $valorParam === '' || $valorParam === null) continue; ?>
        <input type="hidden" name="<?= htmlspecialchars((string)$nombreParam) ?>" value="<?= htmlspecialchars((string)$valorParam) ?>">
    <?php endforeach; ?>

    <fieldset class="filtro-grupo">
        <legend class="filtro-titulo">Aerolínea</legend>

        <?php if (empty($facAerolineas)): ?>
            <p class="filtro-vacio">No hay aerolíneas disponibles para esta búsqueda.</p>
        <?php else: ?>
            <div class="filtro-opciones">
                <?php foreach ($facAerolineas as $aerolineaFac): ?>
                    <?php $idInput = $idPrefijo . 'Aerolinea' . (int)$aerolineaFac['idAerolinea']; ?>
                    <div class="form-check filtro-check">
                        <input class="form-check-input"
                               type="checkbox"
                               name="aerolinea[]"
                               id="<?= htmlspecialchars($idInput) ?>"
                               value="<?= (int)$aerolineaFac['idAerolinea'] ?>"
                               <?= in_array((int)$aerolineaFac['idAerolinea'], $aerolineasFiltro, true) ? 'checked' : '' ?>>
                        <label class="form-check-label d-flex justify-content-between align-items-center gap-2" for="<?= htmlspecialchars($idInput) ?>">
                            <span><?= htmlspecialchars((string)$aerolineaFac['nombreAerolinea']) ?></span>
                            <span class="filtro-cantidad"><?= (int)$aerolineaFac['total'] ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </fieldset>

    <fieldset class="filtro-grupo">
        <legend class="filtro-titulo">Precio por pasajero</legend>
        <p class="filtro-ayuda">
            Precio de lista
            <?php if ($facPrecioMin > 0): ?>
                (<?= htmlspecialchars($precioLegible($facPrecioMin)) ?> a <?= htmlspecialchars($precioLegible($facPrecioMax)) ?>)
            <?php endif; ?>
        </p>

        <div class="filtro-precio">
            <div>
                <label class="form-label" for="<?= htmlspecialchars($idPrefijo) ?>PrecioMin">Desde</label>
                <input type="number"
                       inputmode="numeric"
                       min="0"
                       step="1000"
                       class="form-control form-control-sm"
                       id="<?= htmlspecialchars($idPrefijo) ?>PrecioMin"
                       name="precio_min"
                       placeholder="Mínimo"
                       value="<?= $precioMinFiltro !== null ? (int)$precioMinFiltro : '' ?>">
            </div>
            <div>
                <label class="form-label" for="<?= htmlspecialchars($idPrefijo) ?>PrecioMax">Hasta</label>
                <input type="number"
                       inputmode="numeric"
                       min="0"
                       step="1000"
                       class="form-control form-control-sm"
                       id="<?= htmlspecialchars($idPrefijo) ?>PrecioMax"
                       name="precio_max"
                       placeholder="Máximo"
                       value="<?= $precioMaxFiltro !== null ? (int)$precioMaxFiltro : '' ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="filtro-grupo">
        <legend class="filtro-titulo">Horario de salida</legend>

        <div class="filtro-opciones">
            <?php foreach ($franjasHorarias as $claveFranja => $textoFranja): ?>
                <?php $idFranja = $idPrefijo . 'Horario' . ucfirst($claveFranja); ?>
                <div class="form-check filtro-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="horario[]"
                           id="<?= htmlspecialchars($idFranja) ?>"
                           value="<?= htmlspecialchars($claveFranja) ?>"
                           <?= in_array($claveFranja, $horariosFiltro, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="<?= htmlspecialchars($idFranja) ?>">
                        <?= htmlspecialchars($textoFranja) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="filtro-acciones">
        <button type="submit" class="btn btn-primary w-100 fw-semibold">Aplicar filtros</button>
        <a href="<?= htmlspecialchars($urlLimpiar) ?>" class="btn btn-outline-secondary w-100 fw-semibold">Limpiar filtros</a>
    </div>
</form>
