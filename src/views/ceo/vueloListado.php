<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/VueloController.php';

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) redirect('index.php?pagina=login');

$ctrl = new VueloController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina       = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;
$busqueda     = trim($_GET['busqueda'] ?? '');

$res          = $ctrl->listarVuelosPaginado($idAerolinea, $pagina, $porPagina, $busqueda);
$vuelos       = $res['data'];
$total        = $res['total'];
$totalPaginas = $res['totalPaginas'];

function urlPaginaVuelo(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pag'] = $pag;
    if ($porPagina !== null) $params['porPagina'] = $porPagina;
    return 'index.php?' . http_build_query($params);
}

$desde = $total === 0 ? 0 : ($pagina - 1) * $porPagina + 1;
$hasta = min($pagina * $porPagina, $total);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vuelos - Panel CEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
</head>
<body>
<div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">

    <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1">
        <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
    </aside>

    <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden">
        <header class="admin-header">
            <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
        </header>

        <main class="container-fluid d-flex flex-column gap-3 p-2 admin-content">
            <?php require __DIR__ . '/../components/alertToast.php'; ?>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Mi Aerolínea</a></li>
                    <li class="breadcrumb-item active">Vuelos</li>
                </ol>
            </nav>

            <div class="contForm col-12 d-flex flex-column p-2 gap-3">
                <h2 class="m-0 p-0 fs-2 fw-bold">Vuelos</h2>
                <p class="m-0 p-0 mb-1 subt">Administrá los vuelos de tu aerolínea.</p>

                <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                        <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Vuelos</h3>
                        <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                            <input
                                type="text"
                                id="buscadorVuelo"
                                class="form-control ctm-inp"
                                placeholder="Buscar en la tabla..."
                                autocomplete="off"
                                style="max-width: 240px;"
                            >
                            <a href="<?= url('index.php?pagina=vuelo') ?>" class="btn btn-primary text-nowrap">
                                + Crear Vuelo
                            </a>
                        </div>
                    </div>

                    <table class="table table-hover align-middle border-top mt-2">
                        <thead>
                            <tr>
                                <th scope="col" class="fw-bold">ORIGEN</th>
                                <th scope="col" class="fw-bold">DESTINO</th>
                                <th scope="col" class="fw-bold">SALIDA</th>
                                <th scope="col" class="fw-bold d-none d-md-table-cell text-end">ASIENTOS</th>
                                <th scope="col" class="fw-bold text-end">PRECIO</th>
                                <th scope="col" class="fw-bold text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody id="vueloTableBody">
                            <?php if (empty($vuelos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No hay vuelos registrados todavía.
                                    </td>
                                </tr>
                            <?php else: foreach ($vuelos as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['origenVuelo']) ?></td>
                                <td><?= htmlspecialchars($v['destinoVuelo']) ?></td>
                                <td>
                                    <?= date('d/m/Y', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                    <br>
                                    <span class="text-muted" style="font-size:0.8rem;">
                                        <?= date('H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?> hs
                                    </span>
                                </td>
                                <td class="d-none d-md-table-cell text-end"><?= (int)$v['asientosDisponibles'] ?></td>
                                <td class="text-end fw-semibold text-primary">
                                    $ <?= number_format((float)$v['precioVuelo'], 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>"
                                           class="p-0 bg-transparent border-0"
                                           data-bs-toggle="tooltip"
                                           data-bs-title="Editar Vuelo">
                                            <img src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                                 class="img-fluid btn-tabla" alt="editar">
                                        </a>
                                        <form method="POST"
                                              action="<?= url('src/routes/vuelo.php?accion=eliminar') ?>"
                                              onsubmit="return confirm('¿Eliminar este vuelo?');"
                                              class="m-0">
                                            <input type="hidden" name="id" value="<?= (int)$v['idVuelo'] ?>">
                                            <button type="submit" class="p-0 bg-transparent border-0"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-title="Eliminar Vuelo">
                                                <img src="<?= url('public/img/icons/trash.png') ?>"
                                                     class="img-fluid btn-tabla" alt="eliminar">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>

                    <div class="border-top px-3 py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                            <span class="text-muted" style="font-size:0.8rem;">
                                Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> vuelos
                            </span>

                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm m-0 gap-1">
                                    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0" href="<?= $pagina > 1 ? urlPaginaVuelo(1) : '#' ?>">«</a>
                                    </li>
                                    <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0" href="<?= $pagina > 1 ? urlPaginaVuelo($pagina - 1) : '#' ?>">‹</a>
                                    </li>

                                    <?php
                                    $rango  = 2;
                                    $inicio = max(1, $pagina - $rango);
                                    $fin    = min($totalPaginas, $pagina + $rango);
                                    ?>

                                    <?php if ($inicio > 1): ?>
                                        <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">...</span></li>
                                    <?php endif; ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-2 border-0 <?= $i === $pagina ? 'active-pag' : 'text-secondary' ?>"
                                               href="<?= urlPaginaVuelo($i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($fin < $totalPaginas): ?>
                                        <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">...</span></li>
                                    <?php endif; ?>

                                    <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0" href="<?= $pagina < $totalPaginas ? urlPaginaVuelo($pagina + 1) : '#' ?>">›</a>
                                    </li>
                                    <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0" href="<?= $pagina < $totalPaginas ? urlPaginaVuelo($totalPaginas) : '#' ?>">»</a>
                                    </li>
                                </ul>
                            </nav>

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                <select class="form-select form-select-sm border-0 bg-light rounded-2"
                                        style="width:auto; font-size:0.8rem; cursor:pointer;"
                                        onchange="window.location.href=this.value">
                                    <?php foreach ([5, 10, 25, 50] as $op): ?>
                                        <option value="<?= urlPaginaVuelo(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
                                            <?= $op ?> por página
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require __DIR__ . '/../components/menuMobileAdmin.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
<script src="<?= url('public/js/ceo/vueloListado.js') ?>"></script>
</body>
</html>