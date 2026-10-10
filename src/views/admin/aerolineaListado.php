<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina    = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;

$busqueda = trim($_GET['busqueda'] ?? '');
$estado   = $_GET['estado']  ?? '';
$pais     = $_GET['pais']    ?? '';
$conCeo   = $_GET['conCeo']  ?? '';

$resultado    = $ctrl->listarAerolineasPaginado($pagina, $porPagina, $estado, $pais, $conCeo, $busqueda);
$aerolineas   = $resultado['data'];
$total        = $resultado['total'];
$totalPaginas = $resultado['totalPaginas'];

function urlPagina(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pag'] = $pag;
    if ($porPagina !== null) $params['porPagina'] = $porPagina;
    return 'index.php?' . http_build_query($params);
}

$desde = $total === 0 ? 0 : ($pagina - 1) * $porPagina + 1;
$hasta = min($pagina * $porPagina, $total);

// cards listado

$stats = $ctrl->obtenerEstadisticasListado();
$diff  = $stats['diff'];

if ($diff > 0) {
    $flechaImg = 'flecha-up.png';
    $flechaTexto = '+' . $diff . ' vs mes anterior';
    $flechaColor = 'text-success';
} elseif ($diff < 0) {
    $flechaImg = 'flecha-down.png';
    $flechaTexto = $diff . ' vs mes anterior';
    $flechaColor = 'text-danger';
} else {
    $flechaImg = 'simbolo-igual.png';
    $flechaTexto = 'Igual que el mes anterior';
    $flechaColor = 'text-muted';
}


?>

<?php require __DIR__ . '/../components/alertToast.php';  ?>
<div >
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="#">Aerolínea</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Aerolíneas</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content  h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Gestion de Aerolíneas</h2>
        <p class="m-0 p-0 mb-1 subt ">Administra las aerolineas del sistema.</p>

        <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" class="img-fluid" alt="avion" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Aerolíneas registradas</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $total ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $flechaTexto ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" class="img-fluid" alt="etiqueta" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Nuevas este mes</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['nuevasEsteMes'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $flechaTexto ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" class="img-fluid" alt="ceo" style="width:32px;height:32px;object-fit:contain;">
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Total CEOs asociados</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['ceos'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">Dato estático por ahora</p>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Aerolíneas</h3>
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <input
                            type="text"
                            id="buscadorAerolinea"
                            class="form-control ctm-inp"
                            placeholder="Buscar en la tabla..."
                            autocomplete="off"
                            style="max-width: 240px;"
                        >
                        <a href="<?= url('index.php?pagina=aerolinea&seccion=alta') ?>" class="btn btn-primary text-nowrap w-100">
                            + Crear Aerolínea
                        </a>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas">
                    <thead>
                        <tr>
                            <th scope="col" class=" text-center  fw-bold " >CODIGO</th>
                            <th scope="col" class="fw-bold ">NOMBRE</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">PAIS</th> 
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                            <th scope="col" class="fw-bold ">CEO ASOCIADO</th>
                            <th scope="col" class=" text-center fw-bold ">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="aerolineaTableBody">
                        <?php if (empty($aerolineas)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay aerolíneas registradas todavía.
                                </td>
                            </tr>
                        <?php else: 
                            foreach ($aerolineas as $aero): ?>
                        <tr class="fila-tabla" data-id="<?= $aero['idAerolinea'] ?>" style="cursor:pointer;">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($aero['logoUrl']): ?>
                                        <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border" alt="logo" style="width:30px; height:30px; object-fit:contain;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:30px; height:30px; flex-shrink:0;">
                                            <span class="text-white fw-bold text-uppercase" style="font-size:0.6rem;"><?= htmlspecialchars(substr($aero['codigoIATA'], 0, 2)) ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="text-uppercase fw-medium"><?= htmlspecialchars($aero['codigoIATA']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($aero['nombreAerolinea']) ?></td>
                            <td class="d-none d-md-table-cell"><?= htmlspecialchars($aero['codPais']) ?></td>
                            <td class="d-none d-sm-table-cell"> 
                                <?php if ($aero['activo']): ?>
                                    <span class="badge text-bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($aero['ceoNombre']): ?>
                                    <?= htmlspecialchars($aero['ceoNombre'] . ' ' . $aero['ceoApellido']) ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td >
                                <div class="d-flex justify-content-evenly w-100">
                                    <a 
                                        href="<?= url('index.php?pagina=aerolinea&seccion=detalle&id=' . $aero['idAerolinea']) ?>"
                                        type="button"
                                        id="btnVerAerolinea"
                                        class=" p-0 text-start bg-transparent border-0"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Ver Aerolínea"
                                        data-bs-trigger="hover"
                                        data-id="<?= $aero['idAerolinea'] ?>"
                                        onclick=""
                                    >
                                        <img
                                            src="<?= url('public/img/icons/readTabla.png') ?>"
                                            class= "img-fluid btn-tabla"
                                            alt="icono ver aerolinea"
                                        >
                                    </a>
                                    <a 
                                        href="<?= url('index.php?pagina=aerolinea&seccion=editar&id=' . $aero['idAerolinea']) ?>" 
                                        type="button"
                                        id="btnEditarAerolinea"
                                        class=" p-0 text-start bg-transparent border-0"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        data-bs-title="Editar Aerolínea"
                                        data-bs-trigger="hover"
                                        data-id="<?= $aero['idAerolinea'] ?>"

                                    >
                                        <img
                                            src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                            class= "img-fluid btn-tabla"
                                            alt="icono editar aerolinea"
                                        >
                                    </a>
                                    <button 
                                        type="button"
                                        class="p-0 text-start bg-transparent border-0 btn-toggle-estado"
                                        data-id="<?= $aero['idAerolinea'] ?>"
                                        data-activo="<?= $aero['activo'] ?>"
                                        data-nombre="<?= htmlspecialchars($aero['nombreAerolinea']) ?>"
                                        title="<?= $aero['activo'] ? 'Desactivar Aerolínea' : 'Activar Aerolínea' ?>"
                                    >
                                        <img
                                            src="<?= url($aero['activo'] ? 'public/img/icons/trash.png' : 'public/img/icons/redo.png') ?>"
                                            class="img-fluid btn-tabla"
                                            alt="<?= $aero['activo'] ? 'desactivar' : 'activar' ?>"
                                        >
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="fila-detalle d-none" id="detalle-<?= $aero['idAerolinea'] ?>">
                            <td colspan="6" class="p-0">
                                <div class="detalle-aerolinea px-4 py-3">

                                    <div class="row g-3 align-items-start">

                                        <!-- Columna 1: logo + nombre + descripcion -->
                                        <div class="col-12 col-md-4 d-flex gap-3">
                                            <?php if ($aero['logoUrl']): ?>
                                                <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border flex-shrink-0" alt="logo" style="width:55px;height:55px;object-fit:contain;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;">
                                                    <span class="text-white fw-bold text-uppercase" style="font-size:0.9rem;"><?= htmlspecialchars(substr($aero['codigoIATA'], 0, 2)) ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <div>
                                                <p class="fw-bold m-0 text-uppercase" style="font-size:0.95rem;"><?= htmlspecialchars($aero['codigoIATA']) ?></p>
                                                <p class="text-muted m-0" style="font-size:0.8rem;"><?= htmlspecialchars($aero['nombreAerolinea']) ?></p>
                                                <?php if (!empty($aero['descripcion'])): ?>
                                                    <p class="m-0 mt-2 text-muted" style="font-size:0.8rem; line-height:1.4;"><?= htmlspecialchars($aero['descripcion']) ?></p>
                                                <?php endif; ?>

                                                <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                    <div><span class="text-muted">País:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($aero['codPais']) ?></span></div>
                                                    <div><span class="text-muted">Código IATA:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($aero['codigoIATA']) ?></span></div>
                                                    <div><span class="text-muted">CEO asociado:</span> <span class="text-primary fw-medium ms-1"><?= $aero['ceoNombre'] ? htmlspecialchars($aero['ceoNombre'] . ' ' . $aero['ceoApellido']) : '—' ?></span></div>
                                                    <div><span class="text-muted">Correo:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($aero['email'] ?? '—') ?></span></div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Columna 2: estado -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2" style="font-size:0.85rem;">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted">Estado</span>
                                                <span class="fw-medium <?= $aero['activo'] ? 'text-success' : 'text-secondary' ?>">
                                                    <?= $aero['activo'] ? '● Activa' : '● Inactiva' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Columna 3: acciones -->
                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>
                                            <a href="<?= url('index.php?pagina=aerolinea&seccion=detalle&id=' . $aero['idAerolinea']) ?>" 
                                            class="btn btn-outline-primary btn-sm d-flex align-items-center gap-2">
                                                Ver detalle
                                            </a>
                                            <a href="<?= url('index.php?pagina=aerolinea&seccion=editar&id=' . $aero['idAerolinea']) ?>" 
                                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                                                Editar aerolínea
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm d-flex align-items-center gap-2 btn-toggle-estado
                                                    <?= $aero['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                data-id="<?= $aero['idAerolinea'] ?>"
                                                data-activo="<?= $aero['activo'] ?>"
                                                data-nombre="<?= htmlspecialchars($aero['nombreAerolinea']) ?>">
                                                <?= $aero['activo'] ? 'Desactivar aerolínea' : 'Activar aerolínea' ?>
                                            </button>
                                        </div>

                                    </div>

                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="border-top px-3 py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">

                        <span class="text-muted" style="font-size:0.8rem;">
                            Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> aerolíneas
                        </span>

                        <nav aria-label="Paginación">
                            <ul class="pagination pagination-sm m-0 gap-1">

                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>" 
                                    href="<?= $pagina > 1 ? urlPagina(1) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>" 
                                    href="<?= $pagina > 1 ? urlPagina($pagina - 1) : '#' ?>">‹</a>
                                </li>

                                <?php
                                $rango  = 2;
                                $inicio = max(1, $pagina - $rango);
                                $fin    = min($totalPaginas, $pagina + $rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i === $pagina ? 'active-pag' : 'text-secondary' ?>" 
                                        href="<?= urlPagina($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link border-0 text-muted rounded-2">…</span>
                                    </li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" 
                                    href="<?= $pagina < $totalPaginas ? urlPagina($pagina + 1) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" 
                                    href="<?= $pagina < $totalPaginas ? urlPagina($totalPaginas) : '#' ?>">»</a>
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
                                <?php foreach ([5, 10, 25, 50] as $op): ?>
                                    <option value="<?= urlPagina(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
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

    <div class="modal fade" id="modalToggleEstado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalToggleTitulo"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="modalToggleTexto" class="text-muted m-0"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="<?= url('src/routes/aerolinea.php?accion=toggleEstado') ?>">
                        <input type="hidden" name="id" id="modalToggleId">
                        <button type="submit" class="btn btn-sm" id="modalToggleConfirmar">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="<?=  url('public/js/admin/aerolineaListado.js') ?>"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalEl     = document.getElementById("modalToggleEstado");
    const modalTitulo = document.getElementById("modalToggleTitulo");
    const modalTexto  = document.getElementById("modalToggleTexto");
    const modalId     = document.getElementById("modalToggleId");
    const modalBtn    = document.getElementById("modalToggleConfirmar");
    const bsModal     = new bootstrap.Modal(modalEl);

    document.querySelectorAll(".btn-toggle-estado").forEach(function(btn) {
        btn.addEventListener("click", function(e) {
            e.preventDefault();
            e.stopPropagation();

            const id     = this.dataset.id;
            const activo = this.dataset.activo === "1";
            const nombre = this.dataset.nombre;

            modalId.value = id;

            if (activo) {
                modalTitulo.textContent = "Desactivar aerolínea";
                modalTexto.textContent  = `¿Seguro que querés desactivar "${nombre}"?`;
                modalBtn.textContent    = "Desactivar";
                modalBtn.className      = "btn btn-danger btn-sm";
            } else {
                modalTitulo.textContent = "Activar aerolínea";
                modalTexto.textContent  = `¿Seguro que querés activar "${nombre}"?`;
                modalBtn.textContent    = "Activar";
                modalBtn.className      = "btn btn-success btn-sm";
            }

            bsModal.show();
        });
    });
});
</script>
