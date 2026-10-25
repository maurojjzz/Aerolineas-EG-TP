<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

requireRol('ceo');

$ctrl = new PromocionController($link);
$promocionesTodas = $ctrl->listarPorCEO();

// Datos para el modal de creación
$vuelosCEO    = $ctrl->obtenerVuelosCEO();
$origenesCEO  = $ctrl->obtenerOrigenesCEO();

// ---------- KPIs ----------
$total      = count($promocionesTodas);
$pendientes = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Pendiente'));
$aprobadas  = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Aprobada'));
$denegadas  = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Denegada'));
$canceladas = count(array_filter($promocionesTodas, fn($p) => $p['estado'] === 'Cancelada'));
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
        'Cancelada' => 'text-bg-secondary',
        default    => 'text-bg-warning',
    };
}

// Helper para mostrar el alcance de una promo en la tabla
function textoAlcancePromo(array $p): string {
    if (!empty($p['idVuelo'])) {
        $origen = $p['codigoOrigen'] ?? '—';
        $destino = $p['codigoDestino'] ?? '—';
        return "Vuelo #{$p['idVuelo']} ($origen → $destino)";
    }
    if (!empty($p['codigoOrigen']) && !empty($p['codigoDestino'])) {
        return "Ruta {$p['codigoOrigen']} → {$p['codigoDestino']}";
    }
    return 'Aerolínea completa';
}

// Helper para badge de estado de vigencia (recibe array promo completo)
function badgeVigenciaPromo(array $p): string {
    // Si está denegada, mostrar denegada
    if (in_array($p['estado'], ['Denegada', 'Cancelada'], true)) {
        return '<span class="badge ' . ($p['estado'] === 'Cancelada' ? 'text-bg-secondary' : 'text-bg-danger') . '">' . htmlspecialchars($p['estado']) . '</span>';
    }
    // Si está pendiente, mostrar pendiente
    if ($p['estado'] === 'Pendiente') {
        return '<span class="badge text-bg-warning">Pendiente</span>';
    }
    // Solo para Aprobada: calcular por fechas
    $hoy = new DateTime();
    $fin = new DateTime($p['fechaFin']);
    if ($fin < $hoy) {
        return '<span class="badge text-bg-danger">Vencida</span>';
    }
    $diff = $hoy->diff($fin)->days;
    if ($diff <= 7) {
        return '<span class="badge text-bg-warning">Quedan ' . $diff . ' d&iacute;a' . ($diff === 1 ? '' : 's') . '</span>';
    }
    return '<span class="badge text-bg-success">Vigente</span>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Promociones — Panel de CEO — Volaré</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>?v=<?= time() ?>">
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

        /* Selector de alcance - sombreado sutil en activo */
        .alcance-option.active {
            background-color: rgba(13, 110, 253, 0.08) !important;
            border-color: #0d6efd !important;
        }
        .alcance-option.active .alcance-title {
            color: #0d6efd !important;
            font-weight: 600;
        }
        .alcance-option.active .alcance-subtitle {
            color: #495057 !important; /* gris oscuro legible */
        }
        .alcance-option.active i {
            color: #0d6efd !important;
        }
        /* En estado normal (no activo) */
        .alcance-option:not(.active) .alcance-title {
            color: #0d6efd;
        }
        .alcance-option:not(.active) .alcance-subtitle {
            color: #6c757d;
        }
        .alcance-option:not(.active) i {
            color: #0d6efd;
        }
        /* Hover sutil */
        .alcance-option:hover:not(.active) {
            background-color: rgba(13, 110, 253, 0.04);
        }

        /* Selects no cortados - texto completo visible */
        .alcance-panel select.form-control,
        #filtroEstadoPromo {
            min-width: 280px;
            white-space: nowrap;
        }

        /* Buscador más ancho */
        #buscadorPromocion {
            flex: 1;
            min-width: 280px;
            max-width: 100%;
        }

        /* Botón crear más chico */
        .btn-crear-promocion {
            min-width: 120px;
            white-space: nowrap;
        }
    </style>
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
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

        <main id="contenido" tabindex="-1" class="container-fluid d-flex flex-column gap-3 p-3 admin-content">
            <?php require __DIR__ . '/../components/alertToast.php'; ?>
          
                <nav class="breadcrumbCont" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <!-- <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Inicio</a></li> -->
                        <li class="breadcrumb-item active" aria-current="page">Promociones</li>
                    </ol>
                </nav>

                <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
                    <h1 class="m-0 p-0 fs-2 fw-bold">Gestión de Promociones</h1>
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
                                <h2 class="m-0 p-0 fs-4 fw-bold">Listado de Promociones</h2>
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 flex-wrap">
                                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-center flex-grow-1">
                                        <select
                                            id="filtroEstadoPromo"
                                            class="form-select form-select-sm"
                                            aria-label="Filtrar promociones por estado"
                                            style="min-width: 220px;"
                                        >
                                            <option value="pendientes_aprobadas_vigentes" selected>Pendientes y Aprobadas vigentes</option>
                                            <option value="pendientes">Solo pendientes de aprobación</option>
                                            <option value="todas">Todas</option>
                                            <option value="canceladas">Canceladas</option>
                                            <option value="en_vigencia">En vigencia (Aprobadas vigentes)</option>
                                            <option value="vencidas">Ya vencidas</option>
                                            <option value="denegadas">Denegadas</option>
                                        </select>
                                        <input
                                            type="text"
                                            id="buscadorPromocion"
                                            class="form-control ctm-inp"
                                            placeholder="Buscar código, nombre, alcance..."
                                            aria-label="Buscar promoción por código, nombre o alcance"
                                            autocomplete="off"
                                            style="flex: 1; min-width: 280px; max-width: 100%;"
                                        >
                                    </div>
                                    <button type="button" class="btn btn-primary text-nowrap btn-crear-promocion" data-bs-toggle="modal" data-bs-target="#modalCrearPromocion">
                                        + Crear
                                    </button>
                                </div>
                            </div>

                            <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas ceo-tabla-promociones">
                                <caption class="visually-hidden">Listado de promociones</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="text-center fw-bold">CODIGO</th>
                                        <th scope="col" class="fw-bold">NOMBRE</th>
                                        <th scope="col" class="fw-bold text-center">DESCUENTO</th>
                                        <th scope="col" class="fw-bold d-none d-md-table-cell">ALCANCE</th>
                                        <th scope="col" class="fw-bold d-none d-md-table-cell">VIGENCIA</th>
                                        <th scope="col" class="fw-bold d-none d-md-table-cell">ESTADO VIGENCIA</th>
                                        <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                                        <th scope="col" class="text-center fw-bold">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="promocionTableBody">
                                    <?php if (empty($promociones)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                Todavía no hay promociones cargadas para tu aerolínea.
                                            </td>
                                        </tr>
                                    <?php else:
                                        foreach ($promociones as $p):
                                            $idPromo = $p['idPromocion'] ?? $p['codigo'];
                                            $inicio  = date('d/m/Y', strtotime($p['fechaInicio']));
                                            $fin     = date('d/m/Y', strtotime($p['fechaFin']));
                                            $alcanceTexto = textoAlcancePromo($p);
                                    ?>
                                    <tr class="fila-tabla" 
                                        data-id="<?= htmlspecialchars((string)$idPromo) ?>" 
                                        data-estado="<?= htmlspecialchars($p['estado']) ?>"
                                        data-vigencia="<?= htmlspecialchars(in_array($p['estado'], ['Denegada', 'Cancelada'], true) ? strtolower($p['estado']) : ($p['estado'] === 'Pendiente' ? 'pendiente' : (new DateTime($p['fechaFin']) < new DateTime() ? 'vencida' : ((new DateTime())->diff(new DateTime($p['fechaFin']))->days <= 7 ? 'proxima' : 'vigente')))) ?>"
                                        style="cursor:pointer;">
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
                                        <td class="d-none d-md-table-cell text-muted" style="font-size:0.8rem;"><?= htmlspecialchars($alcanceTexto) ?></td>
                                        <td class="d-none d-md-table-cell text-muted" style="font-size:0.85rem;"><?= $inicio ?> - <?= $fin ?></td>
                                        <td class="d-none d-md-table-cell">
                                            <?= badgeVigenciaPromo($p) ?>
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            <span class="badge <?= badgeEstadoPromo($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-evenly w-100 gap-2">
                                                <!-- Ver detalle (siempre) -->
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

                                                <?php if ($p['estado'] === 'Pendiente'): ?>
                                                    <!-- Editar -->
                                                    <button type="button"
                                                            class="p-0 bg-transparent border-0 btn-editar-promo"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditarPromocion"
                                                            data-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            data-bs-title="Editar"
                                                            data-bs-trigger="hover"
                                                    >
                                                        <img
                                                            src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                                            class="img-fluid btn-tabla"
                                                            alt="icono editar promoción"
                                                        >
                                                    </button>

                                                    <!-- Eliminar -->
                                                    <form method="POST"
                                                        action="<?= url('src/routes/promociones.php?accion=eliminar') ?>"
                                                        class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$idPromo) ?>">
                                                        <button type="button"
                                                                class="p-0 bg-transparent border-0 btn-eliminar-promo"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalConfirmarAccionPromo"
                                                                data-promo-accion="eliminar"
                                                                data-promo-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                                data-promo-titulo="Eliminar promoción pendiente"
                                                                data-promo-mensaje="¿Seguro que querés eliminar esta promoción pendiente? Esta acción no se puede deshacer."
                                                                aria-label="Eliminar promoción"
                                                        >
                                                            <img
                                                                src="<?= url('public/img/icons/trash.png') ?>"
                                                                class="img-fluid btn-tabla"
                                                                alt="icono eliminar promoción"
                                                            >
                                                        </button>
                                                    </form>
                                                <?php elseif ($p['estado'] === 'Denegada'): ?>
                                                    <!-- Eliminar para denegadas -->
                                                    <form method="POST"
                                                        action="<?= url('src/routes/promociones.php?accion=eliminar') ?>"
                                                        class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$idPromo) ?>">
                                                        <button type="button"
                                                                class="p-0 bg-transparent border-0 btn-eliminar-promo"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalConfirmarAccionPromo"
                                                                data-promo-accion="eliminar"
                                                                data-promo-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                                data-promo-titulo="Eliminar promoción denegada"
                                                                data-promo-mensaje="¿Seguro que querés eliminar esta promoción denegada? Esta acción no se puede deshacer."
                                                                aria-label="Eliminar promoción"
                                                        >
                                                            <img
                                                                src="<?= url('public/img/icons/trash.png') ?>"
                                                                class="img-fluid btn-tabla"
                                                                alt="icono eliminar promoción"
                                                            >
                                                        </button>
                                                    </form>
                                                <?php elseif ($p['estado'] === 'Aprobada'): ?>
                                                    <form method="POST"
                                                        action="<?= url('src/routes/promociones.php?accion=cancelar') ?>"
                                                        class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$idPromo) ?>">
                                                        <button type="button"
                                                                class="p-0 bg-transparent border-0 btn-cancelar-promo"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalConfirmarAccionPromo"
                                                                data-promo-accion="cancelar"
                                                                data-promo-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                                data-promo-titulo="Cancelar promoción"
                                                                data-promo-mensaje="¿Seguro que querés cancelar esta promoción aprobada? Dejará de aplicarse a nuevas reservas."
                                                                data-loading-text="Cancelando..."
                                                                aria-label="Cancelar promoción">
                                                            <img src="<?= url('public/img/icons/trash.png') ?>"
                                                                 class="img-fluid btn-tabla" alt="icono cancelar promoción">
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr class="fila-detalle d-none" id="detalle-<?= htmlspecialchars((string)$idPromo) ?>">
                                        <td colspan="7" class="p-0">
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

                            <div class="admin-mobile-cards ceo-mobile-cards d-md-none">
                                <?php if (empty($promociones)): ?>
                                    <div class="admin-mobile-empty">Todavía no hay promociones cargadas para tu aerolínea.</div>
                                <?php else: ?>
                                    <?php foreach ($promociones as $p):
                                        $idCard = $p['idPromocion'] ?? $p['codigo'];
                                        $inicioCard = date('d/m/Y', strtotime($p['fechaInicio']));
                                        $finCard = date('d/m/Y', strtotime($p['fechaFin']));
                                        $alcanceCard = textoAlcancePromo($p);
                                    ?>
                                        <article class="admin-mobile-card">
                                            <div class="admin-mobile-card__header">
                                                <div>
                                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                                    <small>Código: <?= htmlspecialchars($p['codigo']) ?></small>
                                                </div>
                                                <span class="badge <?= badgeEstadoPromo($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                            </div>
                                            <dl class="admin-mobile-card__data">
                                                <div><dt>Descuento</dt><dd class="text-success fw-bold"><?= (float)$p['descuentoPorcentaje'] ?>%</dd></div>
                                                <div><dt>Alcance</dt><dd><?= htmlspecialchars($alcanceCard) ?></dd></div>
                                                <div><dt>Vigencia</dt><dd><?= $inicioCard ?> - <?= $finCard ?></dd></div>
                                                <div><dt>Estado</dt><dd><?= badgeVigenciaPromo($p) ?></dd></div>
                                            </dl>
                                            <div class="admin-mobile-card__actions">
                                                <a class="btn btn-outline-primary btn-sm" href="<?= url('index.php?pagina=ceo&seccion=promocion-detalle&id=' . urlencode((string)$idCard)) ?>">Ver detalle</a>
                                                <?php if ($p['estado'] === 'Pendiente'): ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-editar-promo" data-bs-toggle="modal" data-bs-target="#modalEditarPromocion" data-id="<?= htmlspecialchars((string)$idCard) ?>">Editar</button>
                                                <?php endif; ?>
                                                <?php if (in_array($p['estado'], ['Pendiente', 'Denegada', 'Aprobada'], true)): ?>
                                                    <button type="button" class="btn btn-outline-danger btn-sm <?= $p['estado'] === 'Aprobada' ? 'btn-cancelar-promo' : 'btn-eliminar-promo' ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalConfirmarAccionPromo"
                                                        data-promo-accion="<?= $p['estado'] === 'Aprobada' ? 'cancelar' : 'eliminar' ?>"
                                                        data-promo-id="<?= htmlspecialchars((string)$idCard) ?>"
                                                        data-promo-titulo="<?= $p['estado'] === 'Aprobada' ? 'Cancelar promoción' : 'Eliminar promoción' ?>"
                                                        data-promo-mensaje="¿Seguro que querés realizar esta acción?"><?= $p['estado'] === 'Aprobada' ? 'Cancelar' : 'Eliminar' ?></button>
                                                <?php endif; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

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
                                         aria-label="Resultados por página">
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
                <div class="modal fade" id="modalCrearPromocion" tabindex="-1" role="dialog" aria-labelledby="modalCrearPromocionLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form action="<?= url('src/routes/promociones.php?accion=crear') ?>" method="POST" novalidate id="formCrearPromocion">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <div class="modal-header border-0">
                                    <h2 class="modal-title fw-bold h5" id="modalCrearPromocionLabel">Crear Nueva Promoción</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <p class="small text-secondary mb-2">Los campos con <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">asterisco</span> son obligatorios.</p>
                                <div class="modal-body d-flex flex-column gap-3">
                                    <!-- Selector de alcance -->
                                    <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                        <label class="form-label fw-semibold mb-2 d-block" style="font-size:0.9rem;">Alcance de la promoción *</label>
                                        <div class="d-flex flex-column flex-md-row gap-2" id="alcanceOptions">
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option active" data-alcance="aerolinea">
                                                <input type="radio" name="alcance" value="aerolinea" checked class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-building fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Aerolínea completa</span>
                                                        <span class="d-block alcance-subtitle small">Aplica a todos los vuelos de tu aerolínea</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option" data-alcance="ruta">
                                                <input type="radio" name="alcance" value="ruta" class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-geo-alt fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Ruta específica</span>
                                                        <span class="d-block alcance-subtitle small">Origen → Destino (todos los vuelos de esa ruta)</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option" data-alcance="vuelo">
                                                <input type="radio" name="alcance" value="vuelo" class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-airplane fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Vuelo individual</span>
                                                        <span class="d-block alcance-subtitle small">Un solo vuelo específico</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: AEROLÍNEA (default) -->
                                    <div id="alcance-aerolinea" class="alcance-panel">
                                        <div class="text-muted small text-center py-2">
                                            La promoción se aplicará a <strong>todos los vuelos</strong> de tu aerolínea durante el período seleccionado.
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: RUTA -->
                                    <div id="alcance-ruta" class="alcance-panel d-none">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.9rem;" for="codigoOrigen">Origen *</label>
                                                <select name="codigoOrigen" id="codigoOrigen" class="form-control" required disabled>
                                                    <option value="">Seleccioná origen</option>
                                                    <?php foreach ($origenesCEO as $o): ?>
                                                        <option value="<?= htmlspecialchars($o['codigoOrigen']) ?>">
                                                            <?= htmlspecialchars($o['origenVuelo']) ?> (<?= htmlspecialchars($o['codigoOrigen']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback" role="alert"></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.9rem;" for="codigoDestino">Destino *</label>
                                                <select name="codigoDestino" id="codigoDestino" class="form-control" required disabled>
                                                    <option value="">Primero elegí un origen</option>
                                                </select>
                                                <div class="invalid-feedback" role="alert"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: VUELO -->
                                    <div id="alcance-vuelo" class="alcance-panel d-none">
                                        <div>
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="idVuelo">Vuelo *</label>
                                            <select name="idVuelo" id="idVuelo" class="form-control" required disabled>
                                                <option value="">Seleccioná un vuelo</option>
                                                <?php foreach ($vuelosCEO as $v): ?>
                                                    <option value="<?= (int)$v['idVuelo'] ?>" data-fecha-salida="<?= date('Y-m-d', strtotime($v['fechaHoraSalidaVuelo'])) ?>">
                                                        #<?= (int)$v['idVuelo'] ?> · <?= htmlspecialchars($v['origenVuelo']) ?> → <?= htmlspecialchars($v['destinoVuelo']) ?>
                                                        · <?= date('d/m/Y H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                    </div>

                                    <!-- Campos comunes -->
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="nombrePromocion">Nombre de la promoción *</label>
                                        <input type="text" name="nombre" id="nombrePromocion" class="form-control" placeholder="Ej. Verano Sin Límites" maxlength="150" required>
                                        <div class="invalid-feedback" role="alert"></div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="descuentoPromocion">Descuento (%) *</label>
                                        <input type="number" step="0.01" min="1" max="100" name="descuentoPorcentaje" id="descuentoPromocion" class="form-control" placeholder="Ej. 20" required>
                                        <div class="invalid-feedback" role="alert"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="fechaInicio">Fecha Inicio *</label>
                                            <input type="date" name="fechaInicio" id="fechaInicio" class="form-control" required min="<?= date('Y-m-d') ?>">
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="fechaFin">Fecha Fin *</label>
                                            <input type="date" name="fechaFin" id="fechaFin" class="form-control" required min="<?= date('Y-m-d') ?>">
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="descripcionPromocion">Descripción / Alcance</label>
                                        <textarea name="descripcion" id="descripcionPromocion" class="form-control" rows="2" placeholder="Ej. Válido para todos los destinos nacionales."></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="condicionesPromocion">Condiciones</label>
                                        <textarea name="condiciones" id="condicionesPromocion" class="form-control" rows="2" placeholder="Ej. Compras anticipadas con al menos 7 días de antelación."></textarea>
                                    </div>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" name="acumulable" value="1" id="chkAcumulable" class="form-check-input">
                                        <label class="form-check-label small" for="chkAcumulable">
                                            <i class="bi bi-layers"></i> Acumular con otras promociones (vuelo/ruta/aerolínea)
                                        </label>
                                        <div class="form-text text-muted small">
                                            Si está activado, esta promo se suma a otras vigentes. Si no, tiene prioridad y bloquea las demás.
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Volver</button>
                                    <button type="submit" class="btn btn-primary btn-sm" id="btnEnviarPromocion">
                                        <span class="btn-enviar-texto">Enviar a Aprobación</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Modal Editar Promoción -->
                <div class="modal fade" id="modalEditarPromocion" tabindex="-1" role="dialog" aria-labelledby="modalEditarPromocionLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form action="<?= url('src/routes/promociones.php?accion=editar') ?>" method="POST" novalidate id="formEditarPromocion">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" id="editar_id">
                                <div class="modal-header border-0">
                                    <h2 class="modal-title fw-bold h5" id="modalEditarPromocionLabel">Editar Promoción</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <p class="small text-secondary mb-2">Los campos con <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">asterisco</span> son obligatorios.</p>
                                <div class="modal-body d-flex flex-column gap-3">
                                    <!-- Selector de alcance -->
                                    <div class="p-3 rounded-3" style="background:#f8f9fa;">
                                        <label class="form-label fw-semibold mb-2 d-block" style="font-size:0.9rem;">Alcance de la promoción *</label>
                                        <div class="d-flex flex-column flex-md-row gap-2" id="alcanceOptionsEditar">
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option" data-alcance="aerolinea">
                                                <input type="radio" name="alcance" value="aerolinea" checked class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-building fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Aerolínea completa</span>
                                                        <span class="d-block alcance-subtitle small">Aplica a todos los vuelos de tu aerolínea</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option" data-alcance="ruta">
                                                <input type="radio" name="alcance" value="ruta" class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-geo-alt fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Ruta específica</span>
                                                        <span class="d-block alcance-subtitle small">Origen → Destino (todos los vuelos de esa ruta)</span>
                                                    </span>
                                                </span>
                                            </label>
                                            <label class="btn btn-outline-secondary flex-fill text-start p-3 alcance-option" data-alcance="vuelo">
                                                <input type="radio" name="alcance" value="vuelo" class="visually-hidden">
                                                <span class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-airplane fs-4"></i>
                                                    <span class="d-block">
                                                        <span class="d-block fw-semibold alcance-title">Vuelo individual</span>
                                                        <span class="d-block alcance-subtitle small">Un solo vuelo específico</span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: AEROLÍNEA (default) -->
                                    <div id="alcance-aerolinea-editar" class="alcance-panel">
                                        <div class="text-muted small text-center py-2">
                                            La promoción se aplicará a <strong>todos los vuelos</strong> de tu aerolínea durante el período seleccionado.
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: RUTA -->
                                    <div id="alcance-ruta-editar" class="alcance-panel d-none">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.9rem;" for="codigoOrigenEditar">Origen *</label>
                                                <select name="codigoOrigen" id="codigoOrigenEditar" class="form-control" required disabled>
                                                    <option value="">Seleccioná origen</option>
                                                    <?php foreach ($origenesCEO as $o): ?>
                                                        <option value="<?= htmlspecialchars($o['codigoOrigen']) ?>">
                                                            <?= htmlspecialchars($o['origenVuelo']) ?> (<?= htmlspecialchars($o['codigoOrigen']) ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback" role="alert"></div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold" style="font-size:0.9rem;" for="codigoDestinoEditar">Destino *</label>
                                                <select name="codigoDestino" id="codigoDestinoEditar" class="form-control" required disabled>
                                                    <option value="">Primero elegí un origen</option>
                                                </select>
                                                <div class="invalid-feedback" role="alert"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos según alcance: VUELO -->
                                    <div id="alcance-vuelo-editar" class="alcance-panel d-none">
                                        <div>
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="idVueloEditar">Vuelo *</label>
                                            <select name="idVuelo" id="idVueloEditar" class="form-control" required disabled>
                                                <option value="">Seleccioná un vuelo</option>
                                                <?php foreach ($vuelosCEO as $v): ?>
                                                    <option value="<?= (int)$v['idVuelo'] ?>" data-fecha-salida="<?= date('Y-m-d', strtotime($v['fechaHoraSalidaVuelo'])) ?>">
                                                        #<?= (int)$v['idVuelo'] ?> · <?= htmlspecialchars($v['origenVuelo']) ?> → <?= htmlspecialchars($v['destinoVuelo']) ?>
                                                        · <?= date('d/m/Y H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                    </div>

                                    <!-- Campos comunes -->
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_nombre">Nombre de la promoción *</label>
                                        <input type="text" name="nombre" id="editar_nombre" class="form-control" placeholder="Ej. Verano Sin Límites" maxlength="150" required>
                                        <div class="invalid-feedback" role="alert"></div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_descuentoPorcentaje">Descuento (%) *</label>
                                        <input type="number" step="0.01" min="1" max="100" name="descuentoPorcentaje" id="editar_descuentoPorcentaje" class="form-control" placeholder="Ej. 20" required>
                                        <div class="invalid-feedback" role="alert"></div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_fechaInicio">Fecha Inicio *</label>
                                            <input type="date" name="fechaInicio" id="editar_fechaInicio" class="form-control" required min="<?= date('Y-m-d') ?>">
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_fechaFin">Fecha Fin *</label>
                                            <input type="date" name="fechaFin" id="editar_fechaFin" class="form-control" required min="<?= date('Y-m-d') ?>">
                                            <div class="invalid-feedback" role="alert"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_descripcion">Descripción / Alcance</label>
                                        <textarea name="descripcion" id="editar_descripcion" class="form-control" rows="2" placeholder="Ej. Válido para todos los destinos nacionales."></textarea>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold" style="font-size:0.9rem;" for="editar_condiciones">Condiciones</label>
                                        <textarea name="condiciones" id="editar_condiciones" class="form-control" rows="2" placeholder="Ej. Compras anticipadas con al menos 7 días de antelación."></textarea>
                                    </div>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" name="acumulable" value="1" id="editar_chkAcumulable" class="form-check-input">
                                        <label class="form-check-label small" for="editar_chkAcumulable">
                                            <i class="bi bi-layers"></i> Acumular con otras promociones (vuelo/ruta/aerolínea)
                                        </label>
                                        <div class="form-text text-muted small">
                                            Si está activado, esta promo se suma a otras vigentes. Si no, tiene prioridad y bloquea las demás.
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalConfirmarAccionPromo" tabindex="-1" aria-labelledby="modalConfirmarAccionPromoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h2 class="modal-title fw-bold h5" id="modalConfirmarAccionPromoLabel">Confirmar acción</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p id="modalConfirmarAccionPromoMensaje" class="text-muted m-0"></p>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <form method="POST" id="formConfirmarAccionPromo" data-loading-form>
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <input type="hidden" name="id" id="modalConfirmarAccionPromoId">
                                    <button type="submit" class="btn btn-danger btn-sm" id="modalConfirmarAccionPromoSubmit">Confirmar</button>
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

    const modalConfirmar = document.getElementById("modalConfirmarAccionPromo");
    if (modalConfirmar) {
        const formConfirmar = document.getElementById("formConfirmarAccionPromo");
        const tituloConfirmar = document.getElementById("modalConfirmarAccionPromoLabel");
        const mensajeConfirmar = document.getElementById("modalConfirmarAccionPromoMensaje");
        const idConfirmar = document.getElementById("modalConfirmarAccionPromoId");
        const submitConfirmar = document.getElementById("modalConfirmarAccionPromoSubmit");

        modalConfirmar.addEventListener("show.bs.modal", function (event) {
            const boton = event.relatedTarget;
            const accion = boton.dataset.promoAccion;
            const esCancelacion = accion === "cancelar";
            tituloConfirmar.textContent = boton.dataset.promoTitulo || "Confirmar acción";
            mensajeConfirmar.textContent = boton.dataset.promoMensaje || "";
            idConfirmar.value = boton.dataset.promoId || "";
            formConfirmar.action = `src/routes/promociones.php?accion=${encodeURIComponent(accion)}`;
            submitConfirmar.textContent = esCancelacion ? "Confirmar cancelación" : "Confirmar eliminación";
            submitConfirmar.className = "btn btn-danger btn-sm";
            submitConfirmar.dataset.loadingText = esCancelacion ? "Cancelando..." : "Eliminando...";
        });
    }

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
            },
            codigoOrigen() {
                const sel = formCrear.elements["codigoOrigen"];
                if (sel && !sel.disabled && !sel.value) return "Seleccioná el origen.";
                return "";
            },
            codigoDestino() {
                const sel = formCrear.elements["codigoDestino"];
                if (sel && !sel.disabled && !sel.value) return "Seleccioná el destino.";
                return "";
            },
            idVuelo() {
                const sel = formCrear.elements["idVuelo"];
                if (sel && !sel.disabled && !sel.value) return "Seleccioná un vuelo.";
                return "";
            }
        };

        // Elementos de alcance
        const alcanceRadios = formCrear.querySelectorAll('input[name="alcance"]');
        const alcancePanels = {
            aerolinea: document.getElementById('alcance-aerolinea'),
            ruta:      document.getElementById('alcance-ruta'),
            vuelo:     document.getElementById('alcance-vuelo'),
        };
        const alcanceSelects = {
            ruta:  [formCrear.elements["codigoOrigen"], formCrear.elements["codigoDestino"]],
            vuelo: [formCrear.elements["idVuelo"]],
        };
        const alcanceOptionLabels = document.querySelectorAll('.alcance-option');

        function activarAlcance(alcance) {
            // Actualizar radio buttons
            alcanceRadios.forEach(r => r.checked = r.value === alcance);
            // Actualizar estilos de botones: solo clase .active para sombreado sutil
            alcanceOptionLabels.forEach(l => {
                l.classList.toggle('active', l.dataset.alcance === alcance);
                // Todos quedan como outline-secondary; el activo usa .active para sombreado
                l.classList.remove('btn-outline-primary');
                l.classList.add('btn-outline-secondary');
            });
            // Mostrar/ocultar paneles
            Object.keys(alcancePanels).forEach(k => {
                alcancePanels[k].classList.toggle('d-none', k !== alcance);
            });
            // Habilitar/deshabilitar selects según alcance
            Object.keys(alcanceSelects).forEach(k => {
                alcanceSelects[k].forEach(sel => {
                    if (sel) {
                        sel.disabled = (k !== alcance);
                        sel.required = (k === alcance);
                    }
                });
            });
        }

        // Click en botones de alcance
        alcanceOptionLabels.forEach(label => {
            label.addEventListener('click', function() {
                activarAlcance(this.dataset.alcance);
            });
        });

        function validarCampo(input) {
            const msg = validadores[input.name] ? validadores[input.name]() : "";
            const fb  = input.parentElement?.querySelector(".invalid-feedback");
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

        // === Lógica dinámica según alcance ===
        const inpFechaInicio = document.getElementById('fechaInicio');
        const inpFechaFin    = document.getElementById('fechaFin');
        const selVuelo       = document.getElementById('idVuelo');
        const selOrigen      = document.getElementById('codigoOrigen');
        const selDestino     = document.getElementById('codigoDestino');
        const todayISO       = new Date().toISOString().split('T')[0];

        // Al cambiar alcance, resetear fechas y selects dependientes
        const originalActivarAlcance = activarAlcance;
        activarAlcance = function(alcance) {
            originalActivarAlcance(alcance);
            
            // Resetear max de fechas
            inpFechaFin.max = '';
            inpFechaInicio.max = '';
            
            // Resetear select destino si cambiamos de ruta
            if (alcance !== 'ruta' && selDestino) {
                selDestino.innerHTML = '<option value="">Primero elegí un origen</option>';
                selDestino.disabled = true;
            }
            
            // Si cambiamos a vuelo: limpiar origen/destino ocultos para que no se envíen
            if (alcance === 'vuelo') {
                if (selOrigen) selOrigen.value = '';
                if (selDestino) selDestino.value = '';
            }
            
            // Si cambiamos a vuelo, escuchar cambios en el select de vuelo
            if (alcance === 'vuelo' && selVuelo) {
                // Remover listener anterior si existe
                selVuelo.removeEventListener('change', onVueloChange);
                selVuelo.addEventListener('change', onVueloChange);
            }
            
            // Si cambiamos a ruta, escuchar cambios en el select de origen
            if (alcance === 'ruta' && selOrigen) {
                selOrigen.removeEventListener('change', onOrigenChange);
                selOrigen.addEventListener('change', onOrigenChange);
            }
        };

        // Cuando se selecciona un vuelo: limitar fechaFin y fechaInicio al día del vuelo
        function onVueloChange() {
            const opt = selVuelo.options[selVuelo.selectedIndex];
            const fechaVuelo = opt?.dataset?.fechaSalida; // YYYY-MM-DD
            
            if (fechaVuelo) {
                // La promo puede empezar como máximo el día del vuelo
                inpFechaInicio.max = fechaVuelo;
                // La promo puede terminar como máximo el día del vuelo
                inpFechaFin.max = fechaVuelo;
                
                // Si la fecha actual ya supera el max, limpiarla
                if (inpFechaInicio.value && inpFechaInicio.value > fechaVuelo) {
                    inpFechaInicio.value = '';
                }
                if (inpFechaFin.value && inpFechaFin.value > fechaVuelo) {
                    inpFechaFin.value = '';
                }
                
                // Asegurar que fechaFin.min sea al menos fechaInicio
                inpFechaFin.min = inpFechaInicio.value || todayISO;
            } else {
                inpFechaInicio.max = '';
                inpFechaFin.max = '';
                inpFechaFin.min = inpFechaInicio.value || todayISO;
            }
        }

        // Cuando se selecciona un origen: cargar destinos via AJAX
        function onOrigenChange() {
            const origen = selOrigen.value;
            
            // Resetear destino
            selDestino.innerHTML = '<option value="">Cargando destinos...</option>';
            selDestino.disabled = true;
            
            if (!origen) {
                selDestino.innerHTML = '<option value="">Primero elegí un origen</option>';
                return;
            }
            
            // Fetch a endpoint
            fetch(`src/routes/promociones.php?accion=destinos-por-origen&origen=${encodeURIComponent(origen)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        selDestino.innerHTML = '<option value="">No hay destinos para este origen</option>';
                        selDestino.disabled = true;
                        return;
                    }
                    let html = '<option value="">Seleccioná destino</option>';
                    data.forEach(d => {
                        html += `<option value="${d.codigoDestino}">${d.destinoVuelo} (${d.codigoDestino})</option>`;
                    });
                    selDestino.innerHTML = html;
                    selDestino.disabled = false;
                })
                .catch(() => {
                    selDestino.innerHTML = '<option value="">Error al cargar destinos</option>';
                    selDestino.disabled = true;
                });
        }

        // Se valida apenas se escribe o se elige un valor
        const camposBase = [inpNombre, inpDesc, inpInicio, inpFin];
        camposBase.forEach(function (c) {
            c.addEventListener("input", alCambiar);
            c.addEventListener("change", alCambiar);
        });

        // También validar selects de alcance cuando cambian
        ['codigoOrigen', 'codigoDestino', 'idVuelo'].forEach(name => {
            const el = formCrear.elements[name];
            if (el) {
                el.addEventListener('change', function() {
                    tocados.add(this.name);
                    validarCampo(this);
                });
            }
        });

        // Al enviar se validan todos los campos y se bloquea si hay errores
        formCrear.addEventListener("submit", function (e) {
            if (formCrear.dataset.enviando === "true") {
                e.preventDefault();
                return;
            }
            let primero = null;
            // Validar campos base
            camposBase.forEach(function (c) {
                tocados.add(c.name);
                if (!validarCampo(c) && !primero) primero = c;
            });
            // Validar campos de alcance activo
            const alcanceActivo = formCrear.querySelector('input[name="alcance"]:checked')?.value || 'aerolinea';
            if (alcanceActivo === 'ruta') {
                ['codigoOrigen', 'codigoDestino'].forEach(name => {
                    const el = formCrear.elements[name];
                    if (el) { tocados.add(name); if (!validarCampo(el) && !primero) primero = el; }
                });
            } else if (alcanceActivo === 'vuelo') {
                const el = formCrear.elements['idVuelo'];
                if (el) { tocados.add('idVuelo'); if (!validarCampo(el) && !primero) primero = el; }
            }
            if (primero) {
                e.preventDefault();
                primero.focus();
                return;
            }

            formCrear.dataset.enviando = "true";
            const botonEnviar = document.getElementById("btnEnviarPromocion");
            if (botonEnviar) {
                botonEnviar.disabled = true;
                botonEnviar.querySelector(".btn-enviar-texto").textContent = "Enviando...";
                botonEnviar.querySelector(".spinner-border").classList.remove("d-none");
            }
        });

        // Al cerrar el modal se limpia todo
        modalCrear.addEventListener("hidden.bs.modal", function () {
            formCrear.reset();
            formCrear.dataset.enviando = "false";
            tocados.clear();
            // Resetear alcance a aerolinea
            activarAlcance('aerolinea');
            camposBase.forEach(function (c) {
                c.classList.remove("is-invalid");
                const fb = c.parentElement?.querySelector(".invalid-feedback");
                if (fb) fb.textContent = "";
            });
            ['codigoOrigen', 'codigoDestino', 'idVuelo'].forEach(name => {
                const el = formCrear.elements[name];
                if (el) {
                    el.classList.remove("is-invalid");
                    const fb = el.parentElement?.querySelector(".invalid-feedback");
                    if (fb) fb.textContent = "";
                }
            });
            const botonEnviar = document.getElementById("btnEnviarPromocion");
            if (botonEnviar) {
                botonEnviar.disabled = false;
                botonEnviar.querySelector(".btn-enviar-texto").textContent = "Enviar a Aprobación";
                botonEnviar.querySelector(".spinner-border").classList.add("d-none");
            }
            inpFin.min = "";
            inpInicio.max = "";
        });
    }

    // ===== MODAL EDITAR PROMOCIÓN =====
    const modalEditar = document.getElementById('modalEditarPromocion');
    if (modalEditar) {
        const formEditar = modalEditar.querySelector('form');
        
        // Elementos del formulario editar
        const editarElements = {
            id: document.getElementById('editar_id'),
            nombre: document.getElementById('editar_nombre'),
            descuentoPorcentaje: document.getElementById('editar_descuentoPorcentaje'),
            fechaInicio: document.getElementById('editar_fechaInicio'),
            fechaFin: document.getElementById('editar_fechaFin'),
            descripcion: document.getElementById('editar_descripcion'),
            condiciones: document.getElementById('editar_condiciones'),
            acumulable: document.getElementById('editar_chkAcumulable'),
            codigoOrigen: document.getElementById('codigoOrigenEditar'),
            codigoDestino: document.getElementById('codigoDestinoEditar'),
            idVuelo: document.getElementById('idVueloEditar'),
        };

        // Elementos de alcance para editar
        const alcanceRadiosEditar = formEditar.querySelectorAll('input[name="alcance"]');
        const alcancePanelsEditar = {
            aerolinea: document.getElementById('alcance-aerolinea-editar'),
            ruta:      document.getElementById('alcance-ruta-editar'),
            vuelo:     document.getElementById('alcance-vuelo-editar'),
        };
        const alcanceSelectsEditar = {
            ruta:  [editarElements.codigoOrigen, editarElements.codigoDestino],
            vuelo: [editarElements.idVuelo],
        };
        const alcanceOptionLabelsEditar = modalEditar.querySelectorAll('.alcance-option');

        function activarAlcanceEditar(alcance) {
            alcanceRadiosEditar.forEach(r => r.checked = r.value === alcance);
            alcanceOptionLabelsEditar.forEach(l => {
                l.classList.toggle('active', l.dataset.alcance === alcance);
                l.classList.remove('btn-outline-primary');
                l.classList.add('btn-outline-secondary');
            });
            Object.keys(alcancePanelsEditar).forEach(k => {
                alcancePanelsEditar[k].classList.toggle('d-none', k !== alcance);
            });
            Object.keys(alcanceSelectsEditar).forEach(k => {
                alcanceSelectsEditar[k].forEach(sel => {
                    if (sel) {
                        sel.disabled = (k !== alcance);
                        sel.required = (k === alcance);
                    }
                });
            });
        }

        alcanceOptionLabelsEditar.forEach(label => {
            label.addEventListener('click', function() {
                activarAlcanceEditar(this.dataset.alcance);
            });
        });

        // Lógica dinámica para editar (similar a crear)
        const editarFechaInicio = editarElements.fechaInicio;
        const editarFechaFin    = editarElements.fechaFin;
        const editarSelVuelo    = editarElements.idVuelo;
        const editarSelOrigen   = editarElements.codigoOrigen;
        const editarSelDestino  = editarElements.codigoDestino;

        const originalActivarAlcanceEditar = activarAlcanceEditar;
        activarAlcanceEditar = function(alcance) {
            originalActivarAlcanceEditar(alcance);
            editarFechaFin.max = '';
            editarFechaInicio.max = '';
            if (alcance !== 'ruta' && editarSelDestino) {
                editarSelDestino.innerHTML = '<option value="">Primero elegí un origen</option>';
                editarSelDestino.disabled = true;
            }
            if (alcance === 'vuelo') {
                if (editarSelOrigen) editarSelOrigen.value = '';
                if (editarSelDestino) editarSelDestino.value = '';
            }
            if (alcance === 'vuelo' && editarSelVuelo) {
                editarSelVuelo.removeEventListener('change', onEditarVueloChange);
                editarSelVuelo.addEventListener('change', onEditarVueloChange);
            }
            if (alcance === 'ruta' && editarSelOrigen) {
                editarSelOrigen.removeEventListener('change', onEditarOrigenChange);
                editarSelOrigen.addEventListener('change', onEditarOrigenChange);
            }
        };

        function onEditarVueloChange() {
            const opt = editarSelVuelo.options[editarSelVuelo.selectedIndex];
            const fechaVuelo = opt?.dataset?.fechaSalida;
            if (fechaVuelo) {
                editarFechaInicio.max = fechaVuelo;
                editarFechaFin.max = fechaVuelo;
                if (editarFechaInicio.value && editarFechaInicio.value > fechaVuelo) editarFechaInicio.value = '';
                if (editarFechaFin.value && editarFechaFin.value > fechaVuelo) editarFechaFin.value = '';
                editarFechaFin.min = editarFechaInicio.value || todayISO;
            } else {
                editarFechaInicio.max = '';
                editarFechaFin.max = '';
                editarFechaFin.min = editarFechaInicio.value || todayISO;
            }
        }

        function onEditarOrigenChange() {
            const origen = editarSelOrigen.value;
            editarSelDestino.innerHTML = '<option value="">Cargando destinos...</option>';
            editarSelDestino.disabled = true;
            if (!origen) {
                editarSelDestino.innerHTML = '<option value="">Primero elegí un origen</option>';
                return;
            }
            fetch(`src/routes/promociones.php?accion=destinos-por-origen&origen=${encodeURIComponent(origen)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.length === 0) {
                        editarSelDestino.innerHTML = '<option value="">No hay destinos para este origen</option>';
                        editarSelDestino.disabled = true;
                        return;
                    }
                    let html = '<option value="">Seleccioná destino</option>';
                    data.forEach(d => {
                        html += `<option value="${d.codigoDestino}">${d.destinoVuelo} (${d.codigoDestino})</option>`;
                    });
                    editarSelDestino.innerHTML = html;
                    editarSelDestino.disabled = false;
                })
                .catch(() => {
                    editarSelDestino.innerHTML = '<option value="">Error al cargar destinos</option>';
                    editarSelDestino.disabled = true;
                });
        }

        // Cargar datos al abrir modal editar
        document.querySelectorAll('.btn-editar-promo').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                // Fetch promo data
                fetch(`src/routes/promociones.php?accion=obtener&id=${encodeURIComponent(id)}`)
                    .then(r => r.json())
                    .then(promo => {
                        if (!promo || !promo.idPromocion) return;
                        
                        // Llenar formulario
                        editarElements.id.value = promo.idPromocion;
                        editarElements.nombre.value = promo.nombre || '';
                        editarElements.descuentoPorcentaje.value = promo.descuentoPorcentaje || '';
                        editarElements.fechaInicio.value = promo.fechaInicio || '';
                        editarElements.fechaFin.value = promo.fechaFin || '';
                        editarElements.descripcion.value = promo.descripcion || '';
                        editarElements.condiciones.value = promo.condiciones || '';
                        editarElements.acumulable.checked = promo.acumulable == 1;
                        
                        // Alcance
                        let alcance = 'aerolinea';
                        if (promo.idVuelo) alcance = 'vuelo';
                        else if (promo.codigoOrigen && promo.codigoDestino) alcance = 'ruta';
                        
                        activarAlcanceEditar(alcance);
                        
                        // Llenar selects según alcance
                        if (alcance === 'vuelo' && promo.idVuelo) {
                            editarSelVuelo.value = promo.idVuelo;
                            // Disparar change para setear max fechas
                            editarSelVuelo.dispatchEvent(new Event('change'));
                        } else if (alcance === 'ruta') {
                            editarSelOrigen.value = promo.codigoOrigen || '';
                            // Cargar destinos y luego seleccionar
                            fetch(`src/routes/promociones.php?accion=destinos-por-origen&origen=${encodeURIComponent(promo.codigoOrigen)}`)
                                .then(r => r.json())
                                .then(data => {
                                    let html = '<option value="">Seleccioná destino</option>';
                                    data.forEach(d => {
                                        html += `<option value="${d.codigoDestino}">${d.destinoVuelo} (${d.codigoDestino})</option>`;
                                    });
                                    editarSelDestino.innerHTML = html;
                                    editarSelDestino.disabled = false;
                                    editarSelDestino.value = promo.codigoDestino || '';
                                });
                        }
                    });
            });
        });

        // Reset al cerrar modal editar
        modalEditar.addEventListener('hidden.bs.modal', function() {
            formEditar.reset();
            activarAlcanceEditar('aerolinea');
            // Limpiar validaciones
            Object.values(editarElements).forEach(el => {
                if (el && el.classList) {
                    el.classList.remove('is-invalid');
                    const fb = el.parentElement?.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = '';
                }
            });
            editarFechaFin.min = '';
            editarFechaInicio.max = '';
            editarFechaFin.max = '';
        });
    }

    // Buscador en tiempo real + Filtro por estado
    const inputBuscador = document.getElementById("buscadorPromocion");
    const selectFiltro = document.getElementById("filtroEstadoPromo");
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    function aplicarFiltros() {
        const filtroTexto = inputBuscador ? inputBuscador.value.toLowerCase() : '';
        const filtroEstado = selectFiltro ? selectFiltro.value : 'pendientes_aprobadas_vigentes';
        const filas = tbody.querySelectorAll("tr.fila-tabla");
        let filasVisibles = 0;

        filas.forEach(function (fila) {
            const texto = fila.textContent.toLowerCase();
            const coincideTexto = !filtroTexto || texto.includes(filtroTexto);

            // Usar data-attributes para filtrado confiable
            const estado = fila.dataset.estado || '';
            const vigencia = fila.dataset.vigencia || '';

            let coincideEstado = true;
            switch (filtroEstado) {
                case 'pendientes_aprobadas_vigentes':
                    // Pendientes siempre + Aprobadas que no estén vencidas
                    if (estado === 'Pendiente') {
                        coincideEstado = true;
                    } else if (estado === 'Aprobada' && vigencia !== 'vencida') {
                        coincideEstado = true;
                    } else {
                        coincideEstado = false;
                    }
                    break;
                case 'pendientes':
                    coincideEstado = estado === 'Pendiente';
                    break;
                case 'todas':
                    coincideEstado = true;
                    break;
                case 'canceladas':
                    coincideEstado = estado === 'Denegada' || estado === 'Cancelada';
                    break;
                case 'en_vigencia':
                    coincideEstado = estado === 'Aprobada' && vigencia !== 'vencida';
                    break;
                case 'vencidas':
                    coincideEstado = vigencia === 'vencida';
                    break;
                case 'denegadas':
                    coincideEstado = estado === 'Denegada';
                    break;
                default:
                    coincideEstado = true;
            }

            const coincide = coincideTexto && coincideEstado;
            fila.style.display = coincide ? "" : "none";
            if (coincide) filasVisibles++;
            if (!coincide) {
                const detalle = document.getElementById("detalle-" + fila.dataset.id);
                if (detalle) detalle.classList.add("d-none");
            }
        });

        const mensajeSinResultados = document.getElementById("mensajeSinResultadosPromos");
        if (filas.length > 0 && filasVisibles === 0) {
            if (!mensajeSinResultados) {
                const filaVacia = document.createElement("tr");
                filaVacia.id = "mensajeSinResultadosPromos";
                filaVacia.innerHTML = '<td colspan="8" class="text-center py-4 text-muted">No hay promociones que coincidan con la búsqueda o los filtros seleccionados.</td>';
                tbody.appendChild(filaVacia);
            }
        } else if (mensajeSinResultados) {
            mensajeSinResultados.remove();
        }
    }

    if (inputBuscador) {
        inputBuscador.addEventListener("keyup", aplicarFiltros);
    }
    if (selectFiltro) {
        selectFiltro.addEventListener("change", aplicarFiltros);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<?php require __DIR__ . '/../components/menuMobileAdmin.php'; ?>
</body>
</html>