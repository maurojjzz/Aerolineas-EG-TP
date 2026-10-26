<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/VueloController.php';

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) redirect('index.php?pagina=login');

$ctrl = new VueloController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagActual    = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ?$porPaginaRaw : 10;
$busqueda     = trim($_GET['busqueda'] ?? '');
$desde        = trim($_GET['desde'] ?? '');
$hasta        = trim($_GET['hasta'] ?? '');
$estado       = in_array($_GET['estado'] ?? '', ['proximos', 'finalizados', 'disponibles', 'agotados'], true) ? $_GET['estado'] : '';
$orden        = in_array($_GET['orden'] ?? '', ['salida', 'precio_asc', 'precio_desc', 'disponibles'], true) ? $_GET['orden'] : 'salida';
$filtrosActivos = ($busqueda !== '' ? 1 : 0) + ($desde !== '' ? 1 : 0) + ($hasta !== '' ? 1 : 0) + ($estado !== '' ? 1 : 0) + ($orden !== 'salida' ? 1 : 0);

$resultado    = $ctrl->listarVuelosPaginado($idAerolinea, $pagActual, $porPagina, $busqueda, $desde, $hasta, $estado, $orden);$vuelos       = $resultado['data'];$total        = $resultado['total'];$totalPaginas = max(1, (int)($resultado['totalPaginas'] ?? ceil($total / $porPagina)));$pagActual    = min($pagActual,$totalPaginas);

$registroDesde = $total === 0 ? 0 : ($pagActual - 1) * $porPagina + 1;
$registroHasta = min($pagActual * $porPagina, $total);

function urlPaginaVuelo(int $pag, ?int $porPagina = null): string {
    $params = [];$params['pagina'] = 'vuelos';
    $params['pag'] = (int)$pag;
    
    if ($porPagina !== null) {
        $params['porPagina'] = (int)$porPagina;
    }
    
    if (!empty($_GET['busqueda'])) {
        $params['busqueda'] = trim($_GET['busqueda']);
    }
    foreach (['desde', 'hasta', 'estado', 'orden'] as $filtro) {
        if (!empty($_GET[$filtro])) {
            $params[$filtro] = trim($_GET[$filtro]);
        }
    }
    
    return 'index.php?' . http_build_query($params); 
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vuelos — Panel de CEO — Volaré</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        
        <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
            <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
    </head>
    <body>
        <?php require __DIR__ . '/../layouts/skipLink.php'; ?>
        <div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">
            
            <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1 ">
                <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
            </aside>

            <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden">

                <header class="admin-header">
                    <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
                </header>

                <main id="contenido" tabindex="-1" class="container-fluid d-flex flex-column gap-3 p-3 admin-content">
                    <?php require __DIR__ . '/../components/alertToast.php';  ?>
                    <nav class="breadcrumbCont" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active" aria-current="page">Vuelos</li>
                        </ol>
                    </nav>
                    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
                        <h1 class="m-0 p-0 fs-2 fw-bold">Gestión de Vuelos</h1>
                        <p class="m-0 p-0 mb-2 subt">Administrá los vuelos de tu aerolínea.</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="table-responsive bg-white shadow rounded-3 pt-3 ">
                                
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center px-2 mb-3 gap-3">
                                    <h2 class="m-0 p-0 fs-4 fw-bold">Listado de Vuelos</h2>
                                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                                        <button type="button" class="btn btn-outline-primary text-nowrap"
                                                data-bs-toggle="collapse" data-bs-target="#panelFiltrosVuelos"
                                                aria-expanded="<?= $filtrosActivos > 0 ? 'true' : 'false' ?>"
                                                aria-controls="panelFiltrosVuelos">
                                            <i class="bi bi-funnel me-1" aria-hidden="true"></i> Filtros
                                            <?php if ($filtrosActivos > 0): ?>
                                                <span class="badge text-bg-primary ms-1"><?= $filtrosActivos ?></span>
                                            <?php endif; ?>
                                        </button>
                                        <a href="<?= url('index.php?pagina=vuelo') ?>" class="btn btn-primary text-nowrap">
                                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Crear vuelo
                                        </a>
                                    </div>
                                </div>

                                <div class="collapse <?= $filtrosActivos > 0 ? 'show' : '' ?>" id="panelFiltrosVuelos">
                                    <form method="GET" action="index.php" class="px-2 pb-3">
                                        <input type="hidden" name="pagina" value="vuelos">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-12 col-lg-4">
                                                <label class="form-label fw-semibold mb-1" for="busquedaVuelo">Buscar</label>
                                                <input type="search" name="busqueda" id="busquedaVuelo" class="form-control form-control-sm"
                                                       placeholder="Ruta, código IATA o N° de vuelo"
                                                       aria-label="Buscar por ruta, código IATA o número de vuelo"
                                                       autocomplete="off" value="<?= htmlspecialchars($busqueda) ?>">
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <label class="form-label fw-semibold mb-1" for="desdeVuelo">Salida desde</label>
                                                <input type="date" name="desde" id="desdeVuelo" class="form-control form-control-sm"
                                                       value="<?= htmlspecialchars($desde) ?>">
                                            </div>
                                            <div class="col-6 col-lg-2">
                                                <label class="form-label fw-semibold mb-1" for="hastaVuelo">Salida hasta</label>
                                                <input type="date" name="hasta" id="hastaVuelo" class="form-control form-control-sm"
                                                       value="<?= htmlspecialchars($hasta) ?>">
                                            </div>
                                            <div class="col-12 col-lg-2">
                                                <label class="form-label fw-semibold mb-1" for="estadoVuelo">Estado</label>
                                                <select name="estado" id="estadoVuelo" class="form-select form-select-sm">
                                                    <option value="">Todos</option>
                                                    <option value="proximos" <?= $estado === 'proximos' ? 'selected' : '' ?>>Próximos</option>
                                                    <option value="finalizados" <?= $estado === 'finalizados' ? 'selected' : '' ?>>Finalizados</option>
                                                    <option value="disponibles" <?= $estado === 'disponibles' ? 'selected' : '' ?>>Con disponibilidad</option>
                                                    <option value="agotados" <?= $estado === 'agotados' ? 'selected' : '' ?>>Agotados</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-lg-2">
                                                <label class="form-label fw-semibold mb-1" for="ordenVuelo">Ordenar</label>
                                                <select name="orden" id="ordenVuelo" class="form-select form-select-sm">
                                                    <option value="salida" <?= $orden === 'salida' ? 'selected' : '' ?>>Por salida</option>
                                                    <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Menor precio</option>
                                                    <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Mayor precio</option>
                                                    <option value="disponibles" <?= $orden === 'disponibles' ? 'selected' : '' ?>>Más disponibles</option>
                                                </select>
                                            </div>
                                            <div class="col-12 d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                                                <?php if ($filtrosActivos > 0): ?>
                                                    <a href="<?= url('index.php?pagina=vuelos') ?>" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <table class="table table-hover align-middle border-top mt-2 tabla-vuelos ceo-tabla-vuelos">
                                    <caption class="visually-hidden">Listado de vuelos</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col" class="fw-bold" >ORIGEN</th>
                                            <th scope="col" class=" fw-bold ">DESTINO</th>
                                            <th scope="col" class=" fw-bold d-none d-md-table-cell">SALIDA</th> 
                                            <th scope="col" class=" fw-bold d-none d-md-table-cell">LLEGADA</th> 
                                            <th scope="col" class=" fw-bold d-none d-sm-table-cell">ASIENTOS</th>
                                            <th scope="col" class=" fw-bold d-none d-sm-table-cell">DISPONIBLES</th>
                                            <th scope="col" class="fw-bold ">PRECIO</th>
                                            <th scope="col" class="fw-bold text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vueloTableBody">
                                        <?php if (empty($vuelos)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">
                                                    Todavía no hay vuelos cargados para tu aerolínea.
                                                </td>
                                            </tr>
                                        <?php else: foreach ($vuelos as$v): ?>
                                        <tr class="fila-tabla" data-id="<?= $v['idVuelo'] ?>">
                                            <td>
                                                <button type="button"
                                                        class="btn-detalle-vuelo d-flex align-items-center gap-2 w-100 p-0 border-0 bg-transparent text-start"
                                                        aria-controls="detalle-<?= $v['idVuelo'] ?>"
                                                        aria-expanded="false"
                                                        aria-label="Mostrar detalles del vuelo <?= htmlspecialchars($v['origenVuelo'] . ' a ' . $v['destinoVuelo']) ?>">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                        style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="Icono de avión">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['origenVuelo']) ?></span>
                                                </button>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                        style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="Icono de avión">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['destinoVuelo']) ?></span>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= date('d/m/Y', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                <span class="text-muted ms-1" style="font-size:0.85rem;">
                                                    <?= date('H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= date('d/m/Y', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                <span class="text-muted ms-1" style="font-size:0.85rem;">
                                                    <?= date('H:i', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell "><?= $v['asientosTotales'] ?></td>
                                            <td class="d-none d-sm-table-cell "><?= $v['asientosDisponibles'] ?></td>
                                            <td class="fw-semibold">
                                                $<?= number_format($v['precioVuelo'], 2, ',', '.') ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <a href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>"
                                                    class="p-0 bg-transparent border-0"
                                                    title="Editar vuelo"
                                                    aria-label="Editar el vuelo">
                                                        <img src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                                            class="img-fluid btn-tabla" alt="">
                                                    </a>
                                                    <button type="button"
                                                            class="p-0 bg-transparent border-0 btn-eliminar-vuelo"
                                                            data-id="<?= $v['idVuelo'] ?>"
                                                            data-ruta="<?= htmlspecialchars($v['origenVuelo'] . ' → ' .$v['destinoVuelo']) ?>"
                                                            title="Eliminar vuelo"
                                                            aria-label="Eliminar vuelo">
                                                        <img src="<?= url('public/img/icons/trash.png') ?>"
                                                            class="img-fluid btn-tabla" alt="">
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="fila-detalle d-none" id="detalle-<?= $v['idVuelo'] ?>">
                                            <td colspan="8" class="p-0">
                                                <div class="detalle-aerolinea px-4 py-3">
                                                    <div class="row g-3 align-items-start">
                                                        <div class="col-12 col-md-4">
                                                            <p class="fw-bold m-0" style="font-size:0.95rem;">
                                                                <?= htmlspecialchars($v['origenVuelo']) ?> → <?= htmlspecialchars($v['destinoVuelo']) ?>
                                                            </p>
                                                            <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                                <div>
                                                                    <span class="text-muted">Fecha y hora salida:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= date('d/m/Y H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                                    </span>
                                                                </div>

                                                                <div>
                                                                    <span class="text-muted">Fecha y hora llegada:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= date('d/m/Y H:i', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                                    </span>
                                                                </div>

                                                                <div>
                                                                    <span class="text-muted">Asientos totales:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= $v['asientosTotales'] ?>
                                                                    </span>
                                                                </div>

                                                                <div>
                                                                    <span class="text-muted">Asientos disponibles:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= $v['asientosDisponibles'] ?> 
                                                                    </span>
                                                                </div>

                                                                <div>
                                                                    <span class="text-muted">Precio:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        $<?= number_format($v['precioVuelo'], 2, ',', '.') ?>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <span class="text-muted">Creado:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= date('d/m/Y', strtotime($v['fechaCreacion'])) ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-md-4"></div>
                                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>
                                                            <a href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>"
                                                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                                                                Editar vuelo
                                                            </a>
                                                            <button type="button"
                                                                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2 btn-eliminar-vuelo"
                                                                    data-id="<?= $v['idVuelo'] ?>"
                                                                    data-ruta="<?= htmlspecialchars($v['origenVuelo'] . ' → ' .$v['destinoVuelo']) ?>">
                                                                Eliminar vuelo
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; endif; ?>
                                    </tbody>
                                </table>

                                <div class="admin-mobile-cards ceo-mobile-cards">
                                    <?php if (empty($vuelos)): ?>
                                        <div class="admin-mobile-empty">Todavía no hay vuelos cargados para tu aerolínea.</div>
                                    <?php else: ?>
                                        <?php foreach ($vuelos as $v): ?>
                                            <article class="admin-mobile-card">
                                                <div class="admin-mobile-card__header">
                                                    <div>
                                                        <h3 class="h6 m-0"><?= htmlspecialchars($v['origenVuelo']) ?> → <?= htmlspecialchars($v['destinoVuelo']) ?></h3>
                                                        <small>Salida: <?= date('d/m/Y H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?></small>
                                                    </div>
                                                    <span class="badge text-bg-light">$<?= number_format($v['precioVuelo'], 2, ',', '.') ?></span>
                                                </div>
                                                <dl class="admin-mobile-card__data">
                                                    <div><dt>Llegada</dt><dd><?= date('d/m/Y H:i', strtotime($v['fechaHoraLlegadaVuelo'])) ?></dd></div>
                                                    <div><dt>Asientos</dt><dd><?= $v['asientosTotales'] ?></dd></div>
                                                    <div><dt>Disponibles</dt><dd><?= $v['asientosDisponibles'] ?></dd></div>
                                                </dl>
                                                <div class="admin-mobile-card__actions">
                                                    <a class="btn btn-outline-secondary btn-sm" href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>">Editar vuelo</a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-vuelo" data-id="<?= $v['idVuelo'] ?>" data-ruta="<?= htmlspecialchars($v['origenVuelo'] . ' → ' . $v['destinoVuelo']) ?>">Eliminar</button>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Bloque de Paginación Estandarizado -->
                                <div class="border-top px-3 py-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">

                                        <span class="text-muted" style="font-size:0.8rem;">
                                            Mostrando <strong><?= $registroDesde ?></strong> a <strong><?= $registroHasta ?></strong> de <strong><?= $total ?></strong> vuelos
                                        </span>

                                        <nav aria-label="Paginación">
                                            <ul class="pagination pagination-sm m-0 gap-1">

                                                <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link rounded-2 border-0 <?= $pagActual <= 1 ? 'text-muted' : '' ?>" 
                                                    href="<?= $pagActual > 1 ? urlPaginaVuelo(1) : '#' ?>">«</a>
                                                </li>
                                                <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                                                    <a class="page-link rounded-2 border-0 <?= $pagActual <= 1 ? 'text-muted' : '' ?>" 
                                                    href="<?= $pagActual > 1 ? urlPaginaVuelo($pagActual - 1) : '#' ?>">‹</a>
                                                </li>

                                                <?php
                                                $rango  = 2;
                                                $inicio = max(1, $pagActual -$rango);
                                                $fin    = min($totalPaginas, $pagActual +$rango);
                                                ?>

                                                <?php if ($inicio > 1): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                                    </li>
                                                <?php endif; ?>

                                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                                    <li class="page-item">
                                                        <a class="page-link rounded-2 border-0 <?= $i ===$pagActual ? 'active-pag' : 'text-secondary' ?>" 
                                                        href="<?= urlPaginaVuelo($i) ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($fin <$totalPaginas): ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                                    </li>
                                                <?php endif; ?>

                                                <li class="page-item <?= $pagActual >=$totalPaginas ? 'disabled' : '' ?>">
                                                    <a class="page-link rounded-2 border-0 <?= $pagActual >=$totalPaginas ? 'text-muted' : '' ?>" 
                                                    href="<?= $pagActual < $totalPaginas ? urlPaginaVuelo($pagActual + 1) : '#' ?>">›</a>
                                                </li>
                                                <li class="page-item <?= $pagActual >=$totalPaginas ? 'disabled' : '' ?>">
                                                    <a class="page-link rounded-2 border-0 <?= $pagActual >=$totalPaginas ? 'text-muted' : '' ?>" 
                                                    href="<?= $pagActual < $totalPaginas ? urlPaginaVuelo($totalPaginas) : '#' ?>">»</a>
                                                </li>

                                            </ul>
                                        </nav>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                            <select
                                                class="form-select form-select-sm border-0 bg-light rounded-2"
                                                style="width:auto; font-size:0.8rem; cursor:pointer;"
                                                onchange="window.location.href=this.value"
                                             aria-label="Resultados por página">
                                                <?php foreach ($porPaginaPermitidos as$op): ?>
                                                    <option value="<?= urlPaginaVuelo(1, $op) ?>" <?= $op ===$porPagina ? 'selected' : '' ?>>
                                                        <?= $op ?> por página
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Modal eliminar vuelo -->
        <div class="modal fade" id="modalEliminarVuelo" tabindex="-1" role="dialog" aria-labelledby="modalEliminarVueloLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="modal-title fw-bold h5" id="modalEliminarVueloLabel">Eliminar vuelo</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalEliminarTexto" class="text-muted m-0"></p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <form method="POST" action="<?= url('src/routes/vuelo.php?accion=eliminar') ?>" data-loading-form>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="id" id="modalEliminarId">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function () {

            // Tooltips del sidebar
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });

            // Expand fila detalle
            const tbody = document.getElementById("vueloTableBody");
            if (tbody) {
                tbody.addEventListener("click", function (e) {
                    const botonDetalle = e.target.closest(".btn-detalle-vuelo");
                    if (!botonDetalle) return;

                    const id = botonDetalle.closest(".fila-tabla").dataset.id;
                    const detalle = document.getElementById("detalle-" + id);
                    if (!detalle) return;

                    document.querySelectorAll(".fila-detalle").forEach(f => {
                        if (f !== detalle) f.classList.add("d-none");
                    });
                    const expandido = !detalle.classList.contains("d-none");
                    detalle.classList.toggle("d-none", expandido);
                    document.querySelectorAll(".btn-detalle-vuelo").forEach(boton => {
                        if (boton !== botonDetalle) boton.setAttribute("aria-expanded", "false");
                    });
                    botonDetalle.setAttribute("aria-expanded", expandido ? "false" : "true");
                });
            }

            // Modal eliminar
            const modalEl    = document.getElementById("modalEliminarVuelo");
            const modalTexto = document.getElementById("modalEliminarTexto");
            const modalId    = document.getElementById("modalEliminarId");

            if (modalEl && modalTexto && modalId) {
                const bsModal = new bootstrap.Modal(modalEl);

                document.addEventListener("click", function (e) {
                    const btn = e.target.closest(".btn-eliminar-vuelo");
                    if (!btn) return;
                    e.stopPropagation();
                    modalId.value          = btn.dataset.id;
                    modalTexto.textContent = `¿Seguro que querés eliminar el vuelo "${btn.dataset.ruta}"? Esta acción no se puede deshacer.`;
                    bsModal.show();
                });
            }

            // Buscador con Enter
            const buscador = document.getElementById("buscadorVuelo");
            if (buscador) {
                buscador.addEventListener("keydown", function (e) {
                    if (e.key !== "Enter") return;
                    const params = new URLSearchParams(window.location.search);
                    params.set("pagina", "vuelos");
                    params.set("busqueda", this.value.trim());
                    params.set("pag", "1");
                    window.location.href = "index.php?" + params.toString();
                });
            }

        });
        </script>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    </body>
</html>
