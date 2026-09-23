<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina    = max(1, (int)($_GET['pag'] ?? 1));
// ✅ correcto: guardás el valor primero, después validás
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
?>

<?php require __DIR__ . '/../components/alertToast.php';  ?>
<div class="" >
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
        
        <div class="d-flex flex-column gap-2">
            

            <div class="table-responsive bg-white shadow rounded-3 pt-3 ">
                <div class="d-flex justify-content-between align-items-center px-2 mb-3">
                    <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Aerolíneas</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <input
                            type="text"
                            id="buscadorAerolinea"
                            class="form-control ctm-inp"
                            placeholder="Buscar en la tabla..."
                            autocomplete="off"
                            style="max-width: 240px;"
                        >
                        <a href="<?= url('index.php?pagina=aerolinea&seccion=alta') ?>" class="btn btn-primary text-nowrap">
                            + Crear Aerolínea
                        </a>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-aerolineas">
                    <thead class="">
                        <tr>
                            <th scope="col" class=" text-center  fw-bold " >CODIGO</th>
                            <th scope="col" class="fw-bold ">NOMBRE</th>
                            <th scope="col" class="fw-bold ">PAIS</th>
                            <th scope="col" class="fw-bold ">ESTADO</th>
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
                        <tr>
                            <td><?= htmlspecialchars($aero['codigoIATA']) ?></td>
                            <td><?= htmlspecialchars($aero['nombreAerolinea']) ?></td>
                            <td><?= htmlspecialchars($aero['codPais']) ?></td>
                            <td>
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
                            <td class="d-flex justify-content-evenly w-100">
                                <button 
                                    type="button"
                                    id="btnVerAerolinea"
                                    class=" p-0 text-start bg-transparent border-0"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="Ver Aerolínea"
                                    data-bs-trigger="hover"
                                >
                                    <img
                                        src="<?= url('public/img/icons/readTabla.png') ?>"
                                        class= "img-fluid btn-tabla"
                                        alt="icono ver aerolinea"
                                    >
                                </button>
                                <button 
                                    type="button"
                                    id="btnEditarAerolinea"
                                    class=" p-0 text-start bg-transparent border-0"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="Editar AAdemas erolínea"
                                    data-bs-trigger="hover"
                                >
                                    <img
                                        src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                        class= "img-fluid btn-tabla"
                                        alt="icono editar aerolinea"
                                    >
                                </button>
                                <button 
                                    type="button"
                                    id="btnEliminarAerolinea"
                                    class=" p-0 text-start bg-transparent border-0"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    data-bs-title="Desactivar Aerolínea"
                                    data-bs-trigger="hover"
                                >
                                    <img
                                        src="<?= url('public/img/icons/trash.png') ?>"
                                        class= "img-fluid btn-tabla"
                                        alt="icono desactivar aerolinea"
                                    >
                                </button>
                                
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="py-3 px-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                                    <!-- Info -->
                                    <span class="text-muted" style="font-size:0.8rem;">
                                        Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> aerolíneas
                                    </span>

                                    <!-- Paginación -->
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

                                    <!-- Por página -->
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
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

</div>
