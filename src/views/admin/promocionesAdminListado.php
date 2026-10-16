<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

$ctrl  = new PromocionController($link);
$todas = $ctrl->listarTodas();   // promociones de TODOS los CEOs

// ---------- KPIs (sobre el total, sin filtro) ----------
$totalGeneral = count($todas);
$pendientes   = count(array_filter($todas, fn($p) => $p['estado'] === 'Pendiente'));
$aprobadas    = count(array_filter($todas, fn($p) => $p['estado'] === 'Aprobada'));
$denegadas    = count(array_filter($todas, fn($p) => $p['estado'] === 'Denegada'));

// ---------- Filtro por estado ----------
$estadosValidos = ['Pendiente', 'Aprobada', 'Denegada'];
$estadoFiltro   = in_array($_GET['estado'] ?? '', $estadosValidos, true) ? $_GET['estado'] : '';

$lista = $estadoFiltro !== ''
    ? array_values(array_filter($todas, fn($p) => $p['estado'] === $estadoFiltro))
    : $todas;

// Pendientes primero
usort($lista, fn($a, $b) => ($a['estado'] === 'Pendiente' ? 0 : 1) <=> ($b['estado'] === 'Pendiente' ? 0 : 1));

// ---------- Paginación ----------
$porPaginaPermitidos = [5, 10, 25, 50];
$pagActual    = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;

$total        = count($lista);
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagActual    = min($pagActual, $totalPaginas);

$desde = $total === 0 ? 0 : ($pagActual - 1) * $porPagina + 1;
$hasta = min($pagActual * $porPagina, $total);
$promociones = array_slice($lista, ($pagActual - 1) * $porPagina,$porPagina);

function urlPaginaAdminPromos(int $pag, ?int $porPagina = null, ?string $estado = null): string {
    $params =$_GET;
    $params['pag'] =$pag;
    if ($porPagina !== null) $params['porPagina'] =$porPagina;
    if ($estado !== null) {
        if ($estado === '') unset($params['estado']); else $params['estado'] =$estado;
    }
    return 'index.php?' . http_build_query($params);
}

function badgeEstadoPromoAdmin(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>
<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promociones</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Gestión de Promociones</h2>
        <p class="m-0 p-0 mb-1 subt">Revisa, aprueba o deniega las promociones cargadas por los CEOs.</p>

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
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $totalGeneral ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">De todos los CEOs</p>
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
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pendientes de revisión</p>
                            <p class="fs-3 fw-bold text-warning m-0 p-0"><?= $pendientes ?></p>
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
                            <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" class="img-fluid" alt="aprobadas" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Aprobadas</p>
                            <p class="fs-3 fw-bold text-success m-0 p-0"><?= $aprobadas ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;"><?= $denegadas ?> denegada<?= $denegadas === 1 ? '' : 's' ?></p>
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
                        <select
                            class="form-select ctm-inp"
                            style="max-width: 190px; cursor:pointer;"
                            aria-label="Filtrar por estado"
                            onchange="window.location.href=this.value"
                        >
                            <option value="<?= urlPaginaAdminPromos(1, $porPagina, '') ?>" <?= $estadoFiltro === '' ? 'selected' : '' ?>>Todos los estados</option>
                            <?php foreach ($estadosValidos as$est): ?>
                                <option value="<?= urlPaginaAdminPromos(1, $porPagina,$est) ?>" <?= $estadoFiltro ===$est ? 'selected' : '' ?>>
                                    <?= $est ?>s
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input
                            type="text"
                            id="buscadorPromocion"
                            class="form-control ctm-inp"
                            placeholder="Buscar en la tabla..."
                            autocomplete="off"
                            style="max-width: 240px;"
                        >
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas">
                    <thead>
                        <tr>
                            <th scope="col" class="text-center fw-bold">CODIGO</th>
                            <th scope="col" class="fw-bold">NOMBRE</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">AEROLINEA / CEO</th>
                            <th scope="col" class="fw-bold text-center">DESCUENTO</th>
                            <th scope="col" class="fw-bold d-none d-lg-table-cell">VIGENCIA</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                            <th scope="col" class="text-center fw-bold" style="width: 130px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="promocionTableBody">
                        <?php if (empty($promociones)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No hay promociones<?= $estadoFiltro !== '' ? ' con estado "' . htmlspecialchars($estadoFiltro) . '"' : ' cargadas todavía' ?>.
                                </td>
                            </tr>
                        <?php else:
                            foreach ($promociones as $p):$idPromo   = $p['idPromocion'] ?? $p['codigo'];
                                $fInicio   = date('d/m/Y', strtotime($p['fechaInicio']));
                                $fFin      = date('d/m/Y', strtotime($p['fechaFin']));
                                $aerolinea =$p['nombreAerolinea'] ?? ($p['codigoIATA'] ?? '—');$ceo       = trim(($p['ceoNombre'] ?? '') . ' ' . ($p['ceoApellido'] ?? ''));
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
                            <td class="d-none d-md-table-cell">
                                <div><?= htmlspecialchars($aerolinea) ?></div>
                                <?php if ($ceo !== ''): ?>
                                    <div class="text-muted" style="font-size:0.75rem;"><?= htmlspecialchars($ceo) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold text-success"><?= (float)$p['descuentoPorcentaje'] ?>%</td>
                            <td class="d-none d-lg-table-cell text-muted" style="font-size:0.85rem;"><?= $fInicio ?> - <?= $fFin ?></td>
                            <td class="d-none d-sm-table-cell">
                                <span class="badge <?= badgeEstadoPromoAdmin($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center w-100 gap-2">
                                    <a
                                        href="<?= url('index.php?pagina=promociones&seccion=detalle&id=' . htmlspecialchars((string)$idPromo)) ?>"
                                        class="p-0 bg-transparent border-0 d-flex align-items-center justify-content-center text-decoration-none btn-ver-promo"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Ver detalle"
                                        data-bs-trigger="hover"
                                        style="width: 24px; height: 24px;"
                                    >
                                        <img src="<?= url('public/img/icons/readTabla.png') ?>" class="img-fluid" alt="ver promoción" style="width: 20px; height: 20px; object-fit: contain;">
                                    </a>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-success py-0 px-2 btn-resolver-promo"
                                        data-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                        data-accion="aprobar"
                                        data-bs-toggle="tooltip" data-bs-title="Aprobar" data-bs-trigger="hover">✓</button>
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger py-0 px-2 btn-resolver-promo"
                                        data-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                        data-accion="denegar"
                                        data-bs-toggle="tooltip" data-bs-title="Denegar" data-bs-trigger="hover">✕</button>
                                </div>
                            </td>
                        </tr>

                        <tr class="fila-detalle d-none" id="detalle-<?= htmlspecialchars((string)$idPromo) ?>">
                            <td colspan="7" class="p-0">
                                <div class="detalle-aerolinea px-4 py-3">
                                    <div class="row g-3 align-items-start">
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
                                                <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                    <div><span class="text-muted">Aerolínea:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($aerolinea) ?></span></div>
                                                    <div><span class="text-muted">CEO:</span> <span class="text-primary fw-medium ms-1"><?= $ceo !== '' ? htmlspecialchars($ceo) : '—' ?></span></div>
                                                </div>
                                            </div>
                                        </div>

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
                                                <span class="fw-medium"><?= $fInicio ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Fecha fin</span>
                                                <span class="fw-medium"><?= $fFin ?></span>
                                            </div>
                                            <div class="mt-1">
                                                <span class="text-muted d-block mb-1">Condiciones</span>
                                                <span class="text-muted" style="font-size:0.8rem; line-height:1.4;">
                                                    <?= !empty($p['condiciones']) ? htmlspecialchars($p['condiciones']) : 'Sin condiciones especificadas.' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>
                                            <button type="button"
                                                class="btn btn-outline-success btn-sm btn-resolver-promo"
                                                data-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                                data-accion="aprobar">Aprobar promoción</button>
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm btn-resolver-promo"
                                                data-id="<?= htmlspecialchars((string)$idPromo) ?>"
                                                data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                                                data-accion="denegar">Denegar promoción</button>
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
                                       href="<?= $pagActual > 1 ? urlPaginaAdminPromos(1, $porPagina) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pagActual <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual > 1 ? urlPaginaAdminPromos($pagActual - 1,$porPagina) : '#' ?>">‹</a>
                                </li>

                                <?php
                                $rango  = 2;
                                $inicio = max(1, $pagActual -$rango);
                                $fin    = min($totalPaginas, $pagActual +$rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i ===$pagActual ? 'active-pag' : 'text-secondary' ?>"
                                           href="<?= urlPaginaAdminPromos($i, $porPagina) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin <$totalPaginas): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagActual >=$totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual >=$totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual <$totalPaginas ? urlPaginaAdminPromos($pagActual + 1,$porPagina) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagActual >=$totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagActual >=$totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pagActual <$totalPaginas ? urlPaginaAdminPromos($totalPaginas,$porPagina) : '#' ?>">»</a>
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
                                <?php foreach ($porPaginaPermitidos as$op): ?>
                                    <option value="<?= urlPaginaAdminPromos(1, $op) ?>" <?= $op ===$porPagina ? 'selected' : '' ?>>
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
    <div class="modal fade" id="modalResolverPromo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalResolverTitulo"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="modalResolverTexto" class="text-muted m-0"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="formResolverPromo"
                          data-url-aprobar="<?= url('src/routes/promociones.php?accion=aprobar') ?>"
                          data-url-denegar="<?= url('src/routes/promociones.php?accion=denegar') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="id" id="modalResolverId">
                        <button type="submit" class="btn btn-sm" id="modalResolverConfirmar">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tbody = document.getElementById("promocionTableBody");

        // Tooltips
        if (typeof bootstrap !== "undefined") {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }

        // Expandir / colapsar detalle al hacer click en la fila (ignora acciones y el botón del ojo)
        tbody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
            fila.addEventListener("click", function (e) {
                if (e.target.closest(".btn-resolver-promo") || e.target.closest(".btn-ver-promo")) return;
                const detalle = document.getElementById("detalle-" + this.dataset.id);
                if (detalle) detalle.classList.toggle("d-none");
            });
        });

        // Control del botón del ojo: detiene la propagación para que navegue directo mediante su href
        tbody.querySelectorAll(".btn-ver-promo").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.stopPropagation(); // Evita que se abra el acordeón de la fila
            });
        });

        // Modal de confirmación aprobar / denegar
        const modalEl     = document.getElementById("modalResolverPromo");
        const form        = document.getElementById("formResolverPromo");
        const modalTitulo = document.getElementById("modalResolverTitulo");
        const modalTexto  = document.getElementById("modalResolverTexto");
        const modalId     = document.getElementById("modalResolverId");
        const modalBtn    = document.getElementById("modalResolverConfirmar");
        const bsModal     = new bootstrap.Modal(modalEl);

        document.querySelectorAll(".btn-resolver-promo").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();

                const aprobar = this.dataset.accion === "aprobar";
                modalId.value = this.dataset.id;
                form.action   = aprobar ? form.dataset.urlAprobar : form.dataset.urlDenegar;

                if (aprobar) {
                    modalTitulo.textContent = "Aprobar promoción";
                    modalTexto.textContent  = `¿Seguro que querés aprobar "${this.dataset.nombre}"? Quedará activa para su vigencia.`;
                    modalBtn.textContent    = "Aprobar";
                    modalBtn.className      = "btn btn-success btn-sm";
                } else {
                    modalTitulo.textContent = "Denegar promoción";
                    modalTexto.textContent  = `¿Seguro que querés denegar "${this.dataset.nombre}"?`;
                    modalBtn.textContent    = "Denegar";
                    modalBtn.className      = "btn btn-danger btn-sm";
                }
                bsModal.show();
            });
        });

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