<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

requireRol('ceo');

$ctrl = new PromocionController($link);
$promocionesTodas = $ctrl->listarPorCEO();

// ---------- KPIs ----------
$total      = count($promocionesTodas);
$pendientes = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Pendiente'));
$aprobadas  = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Aprobada'));
$denegadas  = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Denegada'));
$pctAprob   = $total > 0 ? round(($aprobadas / $total) * 100) : 0;

// ---------- Paginación (misma lógica que aerolíneas) ----------
$porPaginaPermitidos = [5, 10, 25, 50];
$pagActual       = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;

$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagActual       = min($pagActual, $totalPaginas);

$desde = $total === 0 ? 0 : ($pagActual - 1) * $porPagina + 1;
$hasta = min($pagActual * $porPagina, $total);
$promociones = array_slice($promocionesTodas, ($pagActual - 1) * $porPagina, $porPagina);

function urlPaginaPromos(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pagina']  = 'ceo';
    $params['seccion'] = 'promociones';
    $params['pag']     = $pag;
    if ($porPagina !== null) $params['porPagina'] = $porPagina;
    return 'index.php?' . http_build_query($params);
}

function badgeEstadoPromo(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEO - Gestión de Promociones</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
    <style>
        #modalCrearPromocion .form-control.is-invalid { background-image: none; padding-right: .75rem; }
        #modalCrearPromocion .invalid-feedback { font-size: .8rem; }
        /* Paginación: mismos estilos que el listado de aerolíneas */
        .tablaContenedor .pagination { display: flex; flex-wrap: nowrap; }
        .tablaContenedor .pagination .page-item { display: list-item; }
        .tablaContenedor .pagination .page-link {
            display: block; min-width: 32px; text-align: center;
            background-color: #f1f3f5; color: #6c757d;
        }
        .tablaContenedor .pagination .page-link.active-pag {
            background-color: #0d6efd; color: #fff; font-weight: 600;
        }
        .tablaContenedor .pagination .page-item.disabled .page-link { opacity: .6; }
    </style>
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

        <main class="container-fluid d-flex flex-column gap-3 p-3 admin-content">

<?php require __DIR__ . '/../components/alertToast.php'; ?>
<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promociones</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Gestión de Promociones</h2>
        <p class="m-0 p-0 mb-1 subt">Administra y crea las promociones de tu aerolínea.</p>

        <!-- KPIs -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" class="img-fluid" alt="total" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Promociones totales</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $total ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;"><?= $denegadas ?> denegada<?= $denegadas === 1 ? '' : 's' ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" class="img-fluid" alt="activas" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Activas / Aprobadas</p>
                            <p class="fs-3 fw-bold text-success m-0 p-0"><?= $aprobadas ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/' . ($aprobadas > 0 ? 'flecha-up.png' : 'simbolo-igual.png')) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 <?= $aprobadas > 0 ? 'text-success' : 'text-muted' ?>" style="font-size:14px;"><?= $pctAprob ?>% del total</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" class="img-fluid" alt="pendientes" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pendientes</p>
                            <p class="fs-3 fw-bold text-warning m-0 p-0"><?= $pendientes ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">En espera de aprobación</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Promociones</h3>
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <input
                            type="text"
                            id="buscadorPromocion"
                            class="form-control ctm-inp"
                            placeholder="Buscar en la tabla..."
                            autocomplete="off"
                            style="max-width: 240px;"
                        >
                        <button type="button" class="btn btn-primary text-nowrap w-100" data-bs-toggle="modal" data-bs-target="#modalCrearPromocion">
                            + Crear Promoción
                        </button>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center fw-bold">CODIGO</th>
                            <th scope="col" class="fw-bold">NOMBRE</th>
                            <th scope="col" class="fw-bold text-center">DESCUENTO</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">VIGENCIA</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                            <th scope="col" class="text-center fw-bold">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="promocionTableBody">
                        <?php if (empty($promociones)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No tenés promociones registradas todavía.
                                </td>
                            </tr>
                        <?php else:
                            foreach ($promociones as $p):
                                $idPromo = $p['idPromocion'] ?? $p['codigo'];
                                $inicio  = date('d/m/Y', strtotime($p['fechaInicio']));
                                $fin     = date('d/m/Y', strtotime($p['fechaFin']));
                        ?>
                        <tr class="fila-tabla" data-id="<?= htmlspecialchars((string)$idPromo) ?>" style="cursor:pointer;">
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:30px; height:30px; flex-shrink:0;">
                                        <span class="text-white fw-bold text-uppercase" style="font-size:0.6rem;"><?= htmlspecialchars(substr($p['codigo'], 0, 2)) ?></span>
                                    </div>
                                    <span class="text-uppercase fw-medium"><?= htmlspecialchars($p['codigo']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="text-center fw-bold text-success"><?= (float)$p['descuentoPorcentaje'] ?>%</td>
                            <td class="d-none d-md-table-cell text-muted" style="font-size:0.85rem;"><?= $inicio ?> - <?= $fin ?></td>
                            <td class="d-none d-sm-table-cell">
                                <span class="badge <?= badgeEstadoPromo($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-evenly w-100">
                                    <a
                                        href="<?= url('index.php?pagina=ceo&seccion=promocion-detalle&id=' . urlencode((string)$idPromo)) ?>"
                                        class="p-0 bg-transparent border-0 btn-ver-promo"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Ver detalle"
                                        data-bs-trigger="hover"
                                    >
                                        <img
                                            src="<?= url('public/img/icons/readTabla.png') ?>"
                                            class="img-fluid btn-tabla"
                                            alt="icono ver promoción"
                                        >
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <tr class="fila-detalle d-none" id="detalle-<?= htmlspecialchars((string)$idPromo) ?>">
                            <td colspan="6" class="p-0">
                                <div class="detalle-aerolinea px-4 py-3">
                                    <div class="row g-3 align-items-start">

                                        <!-- Columna 1: código + nombre + descripción -->
                                        <div class="col-12 col-md-4 d-flex gap-3">
                                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;">
                                                <span class="text-white fw-bold text-uppercase" style="font-size:0.9rem;"><?= htmlspecialchars(substr($p['codigo'], 0, 2)) ?></span>
                                            </div>
                                            <div>
                                                <p class="fw-bold m-0 text-uppercase" style="font-size:0.95rem;"><?= htmlspecialchars($p['codigo']) ?></p>
                                                <p class="text-muted m-0" style="font-size:0.8rem;"><?= htmlspecialchars($p['nombre']) ?></p>
                                                <?php if (!empty($p['descripcion'])): ?>
                                                    <p class="m-0 mt-2 text-muted" style="font-size:0.8rem; line-height:1.4;"><?= htmlspecialchars($p['descripcion']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Columna 2: datos -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2" style="font-size:0.85rem;">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Estado</span>
                                                <span class="fw-medium"><?= htmlspecialchars($p['estado']) ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Descuento</span>
                                                <span class="fw-medium text-success"><?= (float)$p['descuentoPorcentaje'] ?>%</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Fecha inicio</span>
                                                <span class="fw-medium"><?= $inicio ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Fecha fin</span>
                                                <span class="fw-medium"><?= $fin ?></span>
                                            </div>
                                        </div>

                                        <!-- Columna 3: condiciones -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Condiciones</p>
                                            <p class="m-0 text-muted" style="font-size:0.8rem; line-height:1.4;">
                                                <?= !empty($p['condiciones']) ? htmlspecialchars($p['condiciones']) : 'Sin condiciones especificadas.' ?>
                                            </p>
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
                            Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> promociones
                        </span>

                        <nav aria-label="Paginación">
                            <ul class="pagination pagination-sm m-0 gap-1">

                                <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual > 1 ? urlPaginaPromos(1, $porPagina) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual > 1 ? urlPaginaPromos($pagActual - 1, $porPagina) : '#' ?>">‹</a>
                                </li>

                                <?php
                                $rango  = 2;
                                $inicio = max(1, $pagActual - $rango);
                                $fin    = min($totalPaginas, $pagActual + $rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i === $pagActual ? 'active-pag' : 'text-secondary' ?>"
                                           href="<?= urlPaginaPromos($i, $porPagina) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                    </li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual < $totalPaginas ? urlPaginaPromos($pagActual + 1, $porPagina) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagActual >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual < $totalPaginas ? urlPaginaPromos($totalPaginas, $porPagina) : '#' ?>">»</a>
                                </li>

                            </ul>
                        </nav>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                            <select
                                class="form-select form-select-sm border-0 bg-light rounded-2"
                                style="width:auto; font-size:0.8rem; cursor:pointer;"
                                onchange="window.location.href=this.value"
                            >
                                <?php foreach ($porPaginaPermitidos as $op): ?>
                                    <option value="<?= urlPaginaPromos(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
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

    <!-- Modal Crear Promoción -->
    <div class="modal fade" id="modalCrearPromocion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="<?= url('src/routes/promociones.php?accion=guardar') ?>" method="POST" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Crear Nueva Promoción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Nombre de la promoción *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Verano Sin Límites" maxlength="150" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Descuento (%) *</label>
                            <input type="number" step="0.01" min="1" max="100" name="descuentoPorcentaje" class="form-control" placeholder="Ej. 20" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:0.9rem;">Fecha Inicio *</label>
                                <input type="date" name="fechaInicio" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold" style="font-size:0.9rem;">Fecha Fin *</label>
                                <input type="date" name="fechaFin" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Descripción / Alcance</label>
                            <textarea name="descripcion" class="form-control" rows="2" placeholder="Ej. Válido para todos los destinos nacionales."></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-semibold" style="font-size:0.9rem;">Condiciones</label>
                            <textarea name="condiciones" class="form-control" rows="2" placeholder="Ej. Compras anticipadas con al menos 7 días de antelación."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm">Enviar a Aprobación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tbody = document.getElementById("promocionTableBody");

    // Tooltips (mismo comportamiento que en aerolíneas)
    if (typeof bootstrap !== "undefined") {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }

    // Expandir / colapsar el resumen al hacer click en la fila (el ojo navega al detalle completo)
    tbody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
        fila.addEventListener("click", function (e) {
            if (e.target.closest(".btn-ver-promo")) return;
            const detalle = document.getElementById("detalle-" + this.dataset.id);
            if (detalle) detalle.classList.toggle("d-none");
        });
    });

    // Validación del formulario "Crear promoción": mensajes en español debajo de cada campo
    const modalCrear = document.getElementById("modalCrearPromocion");
    if (modalCrear) {
        const formCrear = modalCrear.querySelector("form");
        const inpNombre = formCrear.elements["nombre"];
        const inpDesc   = formCrear.elements["descuentoPorcentaje"];
        const inpInicio = formCrear.elements["fechaInicio"];
        const inpFin    = formCrear.elements["fechaFin"];
        const campos    = [inpNombre, inpDesc, inpInicio, inpFin];
        const tocados   = new Set();

        const validadores = {
            nombre() {
                const v = inpNombre.value.trim();
                if (v === "") return "Ingresá el nombre de la promoción.";
                if (v.length > 150) return "El nombre no puede superar los 150 caracteres.";
                return "";
            },
            descuentoPorcentaje() {
                const raw = inpDesc.value.trim();
                if (inpDesc.validity.badInput) return "Ingresá un número válido.";
                if (raw === "") return "Ingresá el porcentaje de descuento.";
                const n = Number(raw);
                if (isNaN(n)) return "Ingresá un número válido.";
                if (n < 1) return "El descuento debe ser de al menos 1%.";
                if (n > 100) return "El descuento no puede superar el 100%.";
                if (!/^\d+(\.\d+)?$/.test(raw)) return "Ingresá un número válido.";
                if (!/^\d+(\.\d{1,2})?$/.test(raw)) return "Usá como máximo 2 decimales.";
                return "";
            },
            fechaInicio() {
                if (!inpInicio.value) return "Seleccioná la fecha de inicio.";
                if (inpFin.value && inpInicio.value > inpFin.value)
                    return "La fecha de inicio no puede ser posterior a la fecha de fin.";
                return "";
            },
            fechaFin() {
                if (!inpFin.value) return "Seleccioná la fecha de fin.";
                if (inpInicio.value && inpFin.value < inpInicio.value)
                    return "La fecha de fin no puede ser anterior a la fecha de inicio.";
                return "";
            }
        };

        function validarCampo(input) {
            const msg = validadores[input.name]();
            const fb  = input.parentElement.querySelector(".invalid-feedback");
            input.classList.toggle("is-invalid", msg !== "");
            if (fb) fb.textContent = msg;
            return msg === "";
        }

        function alCambiar() {
            tocados.add(this.name);
            validarCampo(this);
            // Si cambia una fecha, se revisa también la otra (si ya se tocó)
            if (this === inpInicio && tocados.has("fechaFin"))    validarCampo(inpFin);
            if (this === inpFin    && tocados.has("fechaInicio")) validarCampo(inpInicio);
            // El calendario deshabilita los días fuera de rango
            inpFin.min    = inpInicio.value || "";
            inpInicio.max = inpFin.value || "";
        }

        // Se valida apenas se escribe o se elige un valor
        campos.forEach(function (c) {
            c.addEventListener("input", alCambiar);
            c.addEventListener("change", alCambiar);
        });

        // Al enviar se validan todos los campos y se bloquea si hay errores
        formCrear.addEventListener("submit", function (e) {
            let primero = null;
            campos.forEach(function (c) {
                tocados.add(c.name);
                if (!validarCampo(c) && !primero) primero = c;
            });
            if (primero) {
                e.preventDefault();
                primero.focus();
            }
        });

        // Al cerrar el modal se limpia todo
        modalCrear.addEventListener("hidden.bs.modal", function () {
            formCrear.reset();
            tocados.clear();
            campos.forEach(function (c) {
                c.classList.remove("is-invalid");
                const fb = c.parentElement.querySelector(".invalid-feedback");
                if (fb) fb.textContent = "";
            });
            inpFin.min = "";
            inpInicio.max = "";
        });
    }

    // Buscador en tiempo real
    const inputBuscador = document.getElementById("buscadorPromocion");
    if (inputBuscador) {
        inputBuscador.addEventListener("keyup", function () {
            const filtro = this.value.toLowerCase();
            tbody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
                const coincide = fila.textContent.toLowerCase().includes(filtro);
                fila.style.display = coincide ? "" : "none";
                if (!coincide) {
                    const detalle = document.getElementById("detalle-" + fila.dataset.id);
                    if (detalle) detalle.classList.add("d-none");
                }
            });
        });
    }
});
</script>
</body>
</html>