<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina       = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos, true) ? $porPaginaRaw : 10;

$estadosPermitidos = ['todos', 'revision', 'aprobado', 'pendiente', 'denegado'];
$estadoFiltro = in_array($_GET['estado'] ?? '', $estadosPermitidos, true)
    ? $_GET['estado']
    : 'todos';
$busqueda = trim($_GET['busqueda'] ?? '');

$resultado    = $ctrl->listarCEOsPaginado($pagina, $porPagina, $estadoFiltro, $busqueda);
$ceos         = $resultado['data'] ?? [];
$total        = (int)($resultado['total'] ?? 0);
$pagina       = (int)($resultado['pagina'] ?? 1);
$totalPaginas = (int)($resultado['totalPaginas'] ?? 1);

$stats       = $ctrl->obtenerEstadisticasCEOs();
$desde       = $total === 0 ? 0 : ($pagina - 1) * $porPagina + 1;
$hasta       = min($pagina * $porPagina, $total);

function urlPaginaCEOs(
    int $pag,
    ?int $porPagina = null,
    ?string $estado = null,
    ?string $busqueda = null
): string {
    $params = $_GET;
    $params['pagina'] = 'ceos';
    $params['seccion'] = 'listado';
    $params['pag'] = $pag;

    if ($porPagina !== null) {
        $params['porPagina'] = $porPagina;
    }

    if ($estado !== null) {
        if ($estado === '') {
            unset($params['estado']);
        } else {
            $params['estado'] = $estado;
        }
    }

    if ($busqueda !== null) {
        if ($busqueda === '') {
            unset($params['busqueda']);
        } else {
            $params['busqueda'] = $busqueda;
        }
    }

    return 'index.php?' . http_build_query($params);
}

function badgeEstadoCEO(string $estado): string {
    if ($estado === 'Pendiente') {
        return 'text-bg-warning';
    }
    if ($estado === 'Aprobado') {
        return 'text-bg-success';
    }
    return 'text-bg-danger';
}
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=inicio') ?>">Inicio</a></li> -->
            <li class="breadcrumb-item active" aria-current="page">CEOs</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h1 class="m-0 p-0 fs-2 fw-bold">Gestión de CEOs</h1>
        <p class="m-0 p-0 mb-1 subt">Revisá las solicitudes pendientes y consultá el historial de CEOs aprobados y denegados.</p>

        <!-- KPIs -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" class="img-fluid" alt="total" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">CEOs totales</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['total'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">Solicitudes registradas</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" class="img-fluid" alt="pendientes" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pendientes de revisión</p>
                            <p class="fs-3 fw-bold text-warning m-0 p-0"><?= $stats['pendientes'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">Requieren tu decisión</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" class="img-fluid" alt="aprobados" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Aprobados</p>
                            <p class="fs-3 fw-bold text-success m-0 p-0"><?= $stats['aprobados'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;"><?= $stats['denegados'] ?> denegado<?= $stats['denegados'] === 1 ? '' : 's' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h2 class="m-0 p-0 fs-4 fw-bold">Listado de CEOs</h2>

                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <select
                            class="form-select ctm-inp"
                            style="max-width: 190px; cursor:pointer;"
                            aria-label="Filtrar por estado"
                            onchange="window.location.href=this.value"
                        >
                            <option value="<?= urlPaginaCEOs(1, $porPagina, 'todos', $busqueda) ?>" <?= $estadoFiltro === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="<?= urlPaginaCEOs(1, $porPagina, 'revision', $busqueda) ?>" <?= $estadoFiltro === 'revision' ? 'selected' : '' ?>>Pendientes y denegados</option>
                            <option value="<?= urlPaginaCEOs(1, $porPagina, 'aprobado', $busqueda) ?>" <?= $estadoFiltro === 'aprobado' ? 'selected' : '' ?>>Aprobados</option>
                            <option value="<?= urlPaginaCEOs(1, $porPagina, 'pendiente', $busqueda) ?>" <?= $estadoFiltro === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                            <option value="<?= urlPaginaCEOs(1, $porPagina, 'denegado', $busqueda) ?>" <?= $estadoFiltro === 'denegado' ? 'selected' : '' ?>>Denegados</option>
                        </select>

                        <form method="GET" action="index.php" class="d-flex gap-2">
                            <input type="hidden" name="pagina" value="ceos">
                            <input type="hidden" name="seccion" value="listado">
                            <input type="hidden" name="porPagina" value="<?= $porPagina ?>">
                            <?php if ($estadoFiltro !== ''): ?>
                                <input type="hidden" name="estado" value="<?= htmlspecialchars($estadoFiltro) ?>">
                            <?php endif; ?>
                            <input
                                type="text"
                                name="busqueda"
                                class="form-control ctm-inp"
                                placeholder="Buscar..."
                                aria-label="Buscar CEO por nombre, email o aerolínea"
                                value="<?= htmlspecialchars($busqueda) ?>"
                                style="max-width: 240px;"
                            >
                        </form>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas">
                    <caption class="visually-hidden">Listado de CEOs</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="fw-bold">CEO</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">DOCUMENTO</th>
                            <th scope="col" class="fw-bold">EMAIL</th>
                            <th scope="col" class="fw-bold d-none d-lg-table-cell">AEROLÍNEA</th>
                            <th scope="col" class="fw-bold d-none d-xl-table-cell">REGISTRO</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                            <th scope="col" class="text-center fw-bold" style="width: 130px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="ceoTableBody">
                        <?php if (empty($ceos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <?php
                                    $mensajesVacio = [
                                        'todos'     => 'No hay CEOs registrados.',
                                        'revision'  => 'No hay CEOs pendientes o denegados.',
                                        'aprobado'  => 'No hay CEOs aprobados.',
                                        'pendiente' => 'No hay CEOs pendientes.',
                                        'denegado'  => 'No hay CEOs denegados.',
                                    ];
                                    ?>
                                    <?= $mensajesVacio[$estadoFiltro] ?? 'No hay CEOs para mostrar.' ?>
                                </td>
                            </tr>
                        <?php else:
                            foreach ($ceos as $c):
                                $id             = (int)$c['idUsuario'];
                                $fechaEstado    = $c['fecha_hora_autorizacion'] ?? null;

                                if ($fechaEstado === null) {
                                    $estado = (int)$c['activo'] === 1 ? 'Aprobado' : 'Pendiente';
                                } elseif ($fechaEstado === FECHA_DENEGACION_CEO) {
                                    $estado = 'Denegado';
                                } else {
                                    $estado = 'Aprobado';
                                }

                                $esPendiente    = $estado === 'Pendiente';
                                $esAprobado     = $estado === 'Aprobado';
                                $nombreCompleto = trim(($c['nombre'] ?? '') . ' ' . ($c['apellido'] ?? ''));
                                $iniciales      = strtoupper(substr($c['nombre'] ?? '', 0, 1) . substr($c['apellido'] ?? '', 0, 1));
                                $fechaRegistro  = !empty($c['fechaCreacion']) ? date('d/m/Y', strtotime($c['fechaCreacion'])) : '—';
                                $fechaNacimiento = !empty($c['fechaNacimiento']) ? date('d/m/Y', strtotime($c['fechaNacimiento'])) : '—';
                                $aerolinea      = $c['nombreAerolinea'] ?? '—';
                        ?>
                        <tr class="fila-tabla" data-id="<?= $id ?>" style="cursor:pointer;">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:30px; height:30px; flex-shrink:0;">
                                        <span class="text-white fw-bold text-uppercase" style="font-size:0.65rem;">
                                            <?= htmlspecialchars($iniciales) ?>
                                        </span>
                                    </div>
                                    <span class="fw-medium"><?= htmlspecialchars($nombreCompleto) ?></span>
                                </div>
                            </td>

                            <td class="d-none d-md-table-cell">
                                <?= htmlspecialchars(($c['tipoDocumento'] ?? '') . ' ' . ($c['nroDocumento'] ?? '')) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($c['email'] ?? '') ?>
                                <?php if (empty($c['emailVerificado'])): ?>
                                    <span class="badge text-bg-warning ms-1" title="El email todavía no fue verificado">Sin verificar</span>
                                <?php endif; ?>
                            </td>

                            <td class="d-none d-lg-table-cell"><?= htmlspecialchars($aerolinea) ?></td>

                            <td class="d-none d-xl-table-cell text-muted" style="font-size:0.85rem;">
                                <?= $fechaRegistro ?>
                            </td>

                            <td class="d-none d-sm-table-cell">
                                <span class="badge <?= badgeEstadoCEO($estado) ?>"><?= $estado ?></span>
                            </td>

                            <td>
                                <div class="d-flex justify-content-center align-items-center w-100 gap-2">
                                    <?php if ($esPendiente): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success py-0 px-2 btn-resolver-ceo"
                                            data-id="<?= $id ?>"
                                            data-nombre="<?= htmlspecialchars($nombreCompleto) ?>"
                                            data-accion="aprobar"
                                            data-bs-toggle="tooltip" data-bs-title="Aprobar" data-bs-trigger="hover">✓</button>

                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger py-0 px-2 btn-resolver-ceo"
                                            data-id="<?= $id ?>"
                                            data-nombre="<?= htmlspecialchars($nombreCompleto) ?>"
                                            data-accion="denegar"
                                            data-bs-toggle="tooltip" data-bs-title="Denegar" data-bs-trigger="hover">✕</button>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.8rem;">Resuelto</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>

                        <!-- Fila desplegable de detalles -->
                        <tr class="fila-detalle d-none" id="detalle-<?= $id ?>">
                            <td colspan="7" class="p-0">
                                <div class="detalle-aerolinea px-4 py-3">
                                    <div class="row g-3 align-items-start">

                                        <!-- Columna 1: avatar + datos -->
                                        <div class="col-12 col-md-4 d-flex gap-3">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;">
                                                <span class="text-white fw-bold text-uppercase" style="font-size:1rem;">
                                                    <?= htmlspecialchars($iniciales) ?>
                                                </span>
                                            </div>

                                            <div>
                                                <p class="fw-bold m-0" style="font-size:0.95rem;"><?= htmlspecialchars($nombreCompleto) ?></p>
                                                <p class="text-muted m-0" style="font-size:0.8rem;"><?= htmlspecialchars($c['email'] ?? '') ?></p>

                                                <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                    <div><span class="text-muted">Documento:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars(($c['tipoDocumento'] ?? '') . ' ' . ($c['nroDocumento'] ?? '')) ?></span></div>
                                                    <div><span class="text-muted">Teléfono:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($c['telefono'] ?? '—') ?></span></div>
                                                    <div><span class="text-muted">Nacimiento:</span> <span class="text-primary fw-medium ms-1"><?= $fechaNacimiento ?></span></div>
                                                    <div><span class="text-muted">Aerolínea:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($aerolinea) ?></span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna 2: estado -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2" style="font-size:0.85rem;">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Estado</span>
                                                <span class="badge <?= badgeEstadoCEO($estado) ?>"><?= $estado ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Email verificado</span>
                                                <span class="fw-medium <?= !empty($c['emailVerificado']) ? 'text-success' : 'text-warning' ?>">
                                                    <?= !empty($c['emailVerificado']) ? 'Sí' : 'No' ?>
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Fecha de registro</span>
                                                <span class="fw-medium"><?= $fechaRegistro ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Decisión</span>
                                                <span class="fw-medium"><?= $esPendiente ? 'Pendiente' : ($esAprobado ? 'Aprobada' : 'Denegada') ?></span>
                                            </div>
                                            <?php if ($esAprobado): ?>
                                                <div class="alert alert-light border text-center m-0 mt-1 p-2" style="font-size:0.8rem;">
                                                    Esta solicitud fue <strong>aprobada</strong>.
                                                </div>
                                            <?php elseif (!$esPendiente): ?>
                                                <div class="alert alert-light border text-center m-0 mt-1 p-2" style="font-size:0.8rem;">
                                                    Esta solicitud fue <strong>denegada</strong>.
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Columna 3: acciones -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>

                                            <?php if ($esPendiente): ?>
                                                <button type="button"
                                                    class="btn btn-outline-success btn-sm btn-resolver-ceo"
                                                    data-id="<?= $id ?>"
                                                    data-nombre="<?= htmlspecialchars($nombreCompleto) ?>"
                                                    data-accion="aprobar">Aprobar CEO</button>

                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm btn-resolver-ceo"
                                                    data-id="<?= $id ?>"
                                                    data-nombre="<?= htmlspecialchars($nombreCompleto) ?>"
                                                    data-accion="denegar">Denegar CEO</button>
                                            <?php else: ?>
                                                <div class="alert alert-light border text-center m-0 p-2" style="font-size:0.8rem;">
                                                    El CEO ya fue notificado de la decisión.
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Paginación -->
                <div class="border-top px-3 py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                        <span class="text-muted" style="font-size:0.8rem;">
                            Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> CEOs
                        </span>

                        <nav aria-label="Paginación">
                            <ul class="pagination pagination-sm m-0 gap-1">
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pagina > 1 ? urlPaginaCEOs(1, $porPagina, $estadoFiltro, $busqueda) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pagina > 1 ? urlPaginaCEOs($pagina - 1, $porPagina, $estadoFiltro, $busqueda) : '#' ?>">‹</a>
                                </li>

                                <?php
                                $rango  = 2;
                                $inicio = max(1, $pagina - $rango);
                                $fin    = min($totalPaginas, $pagina + $rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i === $pagina ? 'active-pag' : 'text-secondary' ?>"
                                           href="<?= urlPaginaCEOs($i, $porPagina, $estadoFiltro, $busqueda) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagina < $totalPaginas ? urlPaginaCEOs($pagina + 1, $porPagina, $estadoFiltro, $busqueda) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagina < $totalPaginas ? urlPaginaCEOs($totalPaginas, $porPagina, $estadoFiltro, $busqueda) : '#' ?>">»</a>
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
                                <?php foreach ($porPaginaPermitidos as $op): ?>
                                    <option value="<?= urlPaginaCEOs(1, $op, $estadoFiltro, $busqueda) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
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

    <!-- Modal confirmar aprobar / denegar -->
    <div class="modal fade" id="modalResolverCEO" tabindex="-1" role="dialog" aria-labelledby="modalResolverTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fw-bold h5" id="modalResolverTitulo"></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="modalResolverTexto" class="text-muted m-0"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formResolverCEO" data-loading-form
                          data-url-aprobar="<?= url('src/routes/usuarios.php?accion=aprobar-ceo') ?>"
                          data-url-denegar="<?= url('src/routes/usuarios.php?accion=denegar-ceo') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" id="modalResolverId">
                        <button type="submit" class="btn btn-sm" id="modalResolverConfirmar">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= url('public/js/admin/actionLoading.js') ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
            const buscadorCeo = document.querySelector('input[name="busqueda"]');
            const ceosBody = document.querySelector("tbody");
            if (buscadorCeo && ceosBody) {
                buscadorCeo.addEventListener("input", function () {
                    const texto = this.value.toLowerCase().trim();
                    ceosBody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
                        fila.style.display = !texto || fila.textContent.toLowerCase().includes(texto) ? "" : "none";
                    });
                });
            }

        const tbody = document.getElementById("ceoTableBody");

        if (typeof bootstrap !== "undefined") {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }

        // Expandir / colapsar el detalle al hacer click en la fila.
        if (tbody) {
            tbody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
                fila.addEventListener("click", function (e) {
                    if (e.target.closest(".btn-resolver-ceo")) return;

                    const detalle = document.getElementById("detalle-" + this.dataset.id);
                    if (detalle) detalle.classList.toggle("d-none");
                });
            });
        }

        // Modal de confirmación aprobar / denegar.
        const modalEl     = document.getElementById("modalResolverCEO");
        const form        = document.getElementById("formResolverCEO");
        const modalTitulo = document.getElementById("modalResolverTitulo");
        const modalTexto  = document.getElementById("modalResolverTexto");
        const modalId     = document.getElementById("modalResolverId");
        const modalBtn    = document.getElementById("modalResolverConfirmar");

        if (typeof bootstrap !== "undefined" && modalEl && form && modalBtn) {
            const bsModal = new bootstrap.Modal(modalEl);

            document.querySelectorAll(".btn-resolver-ceo").forEach(function (btn) {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const aprobar = this.dataset.accion === "aprobar";

                    modalId.value = this.dataset.id;
                    form.action   = aprobar ? form.dataset.urlAprobar : form.dataset.urlDenegar;

                    if (aprobar) {
                        modalTitulo.textContent = "Aprobar CEO";
                        modalTexto.textContent  = `¿Seguro que querés aprobar a "${this.dataset.nombre}"? Podrá iniciar sesión y gestionar su aerolínea.`;
                        modalBtn.textContent    = "Aprobar";
                        modalBtn.className      = "btn btn-success btn-sm";
                    } else {
                        modalTitulo.textContent = "Denegar CEO";
                        modalTexto.textContent  = `¿Seguro que querés denegar a "${this.dataset.nombre}"? Se le notificará por email y no podrá iniciar sesión.`;
                        modalBtn.textContent    = "Denegar";
                        modalBtn.className      = "btn btn-danger btn-sm";
                    }

                    bsModal.show();
                });
            });
        }
    });
</script>
