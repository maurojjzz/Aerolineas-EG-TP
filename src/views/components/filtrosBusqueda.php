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
 * - $busqueda       (opcional) datos de la búsqueda actual para poder modificarla desde el panel:
 *                   ['origen','destino','fecha','fechaVuelta','pasajeros','esIdaVuelta','ciudades']
 */

$facetas        = $facetas ?? [];
$filtrosActivos = $filtrosActivos ?? [];
$paramsFiltros  = $paramsFiltros ?? [];
$idPrefijo      = $idPrefijo ?? 'filtros';
$urlLimpiar     = $urlLimpiar ?? url('index.php?pagina=buscar-vuelos');
$busqueda       = $busqueda ?? null;

// Si se muestra la búsqueda editable, esos campos no van como ocultos.
$camposBusqueda = $busqueda ? ['origen', 'destino', 'fecha', 'fecha_vuelta', 'pasajeros', 'tipo_viaje'] : [];

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
        <?php if ($nombreParam === 'pagina' || in_array($nombreParam, $camposBusqueda, true) || $valorParam === '' || $valorParam === null) continue; ?>
        <input type="hidden" name="<?= htmlspecialchars((string)$nombreParam) ?>" value="<?= htmlspecialchars((string)$valorParam) ?>">
    <?php endforeach; ?>

    <?php if ($busqueda): ?>
        <fieldset class="filtro-grupo filtro-busqueda">
            <legend class="filtro-titulo">Tu búsqueda</legend>

            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>TipoViaje">Tipo de viaje</label>
                <select class="form-select form-select-sm js-tipo-viaje" id="<?= $idPrefijo ?>TipoViaje" name="tipo_viaje">
                    <option value="solo_ida" <?= !$busqueda['esIdaVuelta'] ? 'selected' : '' ?>>Solo ida</option>
                    <option value="ida_vuelta" <?= $busqueda['esIdaVuelta'] ? 'selected' : '' ?>>Ida y vuelta</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>Origen">Origen</label>
                <input type="text" class="form-control form-control-sm" id="<?= $idPrefijo ?>Origen" name="origen"
                       list="<?= $idPrefijo ?>Ciudades" placeholder="Cualquier origen" autocomplete="off"
                       value="<?= htmlspecialchars($busqueda['origen']) ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>Destino">Destino</label>
                <input type="text" class="form-control form-control-sm" id="<?= $idPrefijo ?>Destino" name="destino"
                       list="<?= $idPrefijo ?>Ciudades" placeholder="Cualquier destino" autocomplete="off"
                       value="<?= htmlspecialchars($busqueda['destino']) ?>">
            </div>
            <div class="row g-2 mb-2">
                <div class="col">
                    <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>Fecha">Salida</label>
                    <input type="date" class="form-control form-control-sm js-fecha-ida" id="<?= $idPrefijo ?>Fecha" name="fecha"
                           min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($busqueda['fecha']) ?>">
                </div>
                <div class="col js-cont-vuelta <?= $busqueda['esIdaVuelta'] ? '' : 'd-none' ?>">
                    <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>FechaVuelta">Vuelta</label>
                    <input type="date" class="form-control form-control-sm js-fecha-vuelta" id="<?= $idPrefijo ?>FechaVuelta" name="fecha_vuelta"
                           min="<?= htmlspecialchars($busqueda['fecha'] ?: date('Y-m-d')) ?>" value="<?= htmlspecialchars($busqueda['fechaVuelta']) ?>"
                           <?= $busqueda['esIdaVuelta'] ? '' : 'disabled' ?>>
                </div>
            </div>
            <div>
                <label class="form-label small fw-semibold mb-1" for="<?= $idPrefijo ?>Pasajeros">Pasajeros</label>
                <select class="form-select form-select-sm" id="<?= $idPrefijo ?>Pasajeros" name="pasajeros">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $i === (int)$busqueda['pasajeros'] ? 'selected' : '' ?>><?= $i ?> pasajero<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <datalist id="<?= $idPrefijo ?>Ciudades">
                <?php foreach (array_unique(array_column($busqueda['ciudades'], 'city')) as $ciudad): ?>
                    <option value="<?= htmlspecialchars($ciudad) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </fieldset>
    <?php endif; ?>

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
        <button type="submit" class="btn btn-primary w-100 fw-semibold"><?= $busqueda ? 'Buscar' : 'Aplicar filtros' ?></button>
        <a href="<?= htmlspecialchars($urlLimpiar) ?>" class="btn btn-outline-secondary w-100 fw-semibold">Limpiar filtros</a>
    </div>
</form>
