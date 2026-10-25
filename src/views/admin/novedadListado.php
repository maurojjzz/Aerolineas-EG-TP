<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/NovedadController.php';

$ctrl = new NovedadController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina       = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ?$porPaginaRaw : 10;

$busqueda  = trim($_GET['busqueda'] ?? '');
$estado    =$_GET['estado'] ?? '';
$categoria =$_GET['categoria'] ?? '';

$resultado    = $ctrl->listarPaginado($pagina, $porPagina,$estado, $categoria,$busqueda);
$novedades    =$resultado['data'] ?? [];
$total        =$resultado['total'] ?? 0;
$totalPaginas =$resultado['totalPaginas'] ?? 1;

function urlPagina(int $pag, ?int $porPagina = null): string {
    $params =$_GET;
    $params['pag'] =$pag;
    if ($porPagina !== null) $params['porPagina'] =$porPagina;
    return 'index.php?' . http_build_query($params);
}

$desde =$total === 0 ? 0 : ($pagina - 1) *$porPagina + 1;
$hasta = min($pagina * $porPagina,$total);

$stats =$ctrl->obtenerEstadisticas();
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- <li class="breadcrumb-item"><a href="#">Inicio</a></li> -->
            <li class="breadcrumb-item active" aria-current="page">Novedades</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h1 class="m-0 p-0 fs-2 fw-bold">Gestión de Novedades</h1>
        <p class="m-0 p-0 mb-1 subt">Publicá y administrá avisos, promociones o novedades de vuelos para los pasajeros.</p>

        <!-- Tarjetas de métricas -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <i class="bi bi-megaphone-fill text-primary fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Total Novedades</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['total'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E8F5E9;">
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Publicadas</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['publicadas'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#FFF3E0;">
                            <i class="bi bi-file-earmark-text-fill text-warning fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Borradores</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['borradores'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h2 class="m-0 p-0 fs-4 fw-bold">Listado de Novedades</h2>
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-center">
                        <form method="GET" action="index.php" class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="hidden" name="pagina" value="novedades">
                            <input type="hidden" name="seccion" value="listado">
                            
                            <select name="categoria" class="form-select form-select-sm ctm-inp" aria-label="Filtrar por categoría" style="width:auto;">
                                <option value="">Todas las categorías</option>
                                <option value="general" <?= $categoria === 'general' ? 'selected' : '' ?>>General</option>
                                <option value="promocion" <?= $categoria === 'promocion' ? 'selected' : '' ?>>Promoción</option>
                                <option value="vuelo" <?= $categoria === 'vuelo' ? 'selected' : '' ?>>Vuelo</option>
                                <option value="importante" <?= $categoria === 'importante' ? 'selected' : '' ?>>Importante</option>
                            </select>

                            <select name="estado" class="form-select form-select-sm ctm-inp" aria-label="Filtrar por estado" style="width:auto;">
                                <option value="">Todos los estados</option>
                                <option value="publicada" <?= $estado === 'publicada' ? 'selected' : '' ?>>Publicadas</option>
                                <option value="borrador" <?= $estado === 'borrador' ? 'selected' : '' ?>>Borradores</option>
                                <option value="archivada" <?= $estado === 'archivada' ? 'selected' : '' ?>>Archivadas</option>
                            </select>

                            <input type="text" name="busqueda" id="buscadorNovedad" class="form-control form-control-sm ctm-inp" placeholder="Buscar..." aria-label="Buscar novedad por título" value="<?= htmlspecialchars($busqueda) ?>" style="max-width: 160px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Aplicar filtros</button>
                        </form>

                        <a href="<?= url('index.php?pagina=novedades&seccion=crear') ?>" class="btn btn-primary text-nowrap w-40">
                            + Nueva Novedad
                        </a>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2">
                    <caption class="visually-hidden">Listado de novedades</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="fw-bold">TÍTULO</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">CATEGORÍA</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">PRIORIDAD</th>
                            <th scope="col" class="fw-bold">ESTADO</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">PUBLICACIÓN</th>
                            <th scope="col" class="text-center fw-bold">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="novedadTableBody">
                        <?php if (empty($novedades)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Todavía no hay novedades cargadas.</td>
                            </tr>
                        <?php else: 
                            foreach ($novedades as $n): ?>
                            <tr class="fila-tabla" data-id="<?= $n['idNovedad'] ?>" style="cursor:pointer;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($n['imagen'])): ?>
                                            <img src="<?= htmlspecialchars($n['imagen']) ?>" class="rounded border flex-shrink-0" alt="miniatura" style="width:40px; height:40px; object-fit:cover;">
                                        <?php else: ?>
                                            <div class="rounded bg-light border d-flex align-items-center justify-content-center flex-shrink-0 text-primary" style="width:40px; height:40px;">
                                                <i class="bi bi-megaphone"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-medium text-dark"><?= htmlspecialchars($n['titulo']) ?></div>
                                            <small class="text-muted d-block"><?= htmlspecialchars($n['subtitulo'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge text-bg-light text-capitalize border"><?= htmlspecialchars($n['categoria']) ?></span>
                                </td>
                                <td class="d-none d-sm-table-cell">
                                    <?php 
                                    $pColor = $n['prioridad'] === 'alta' ? 'danger' : ($n['prioridad'] === 'media' ? 'warning' : 'secondary');
                                    ?>
                                    <span class="badge text-bg-<?= $pColor ?> text-capitalize"><?= htmlspecialchars($n['prioridad']) ?></span>
                                </td>
                                <td>
                                    <?php if ($n['estado'] === 'publicada'): ?>
                                        <span class="badge text-bg-success">Publicada</span>
                                    <?php elseif ($n['estado'] === 'borrador'): ?>
                                        <span class="badge text-bg-warning">Borrador</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Archivada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-md-table-cell" style="font-size:0.85rem;">
                                    <?= date('d/m/Y H:i', strtotime($n['fechaPublicacion'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <a href="<?= url('index.php?pagina=novedades&seccion=editar&id=' . $n['idNovedad']) ?>" class="p-0 text-start bg-transparent border-0" title="Editar">
                                            <img src="<?= url('public/img/icons/lapiz-editar.png') ?>" class="img-fluid btn-tabla" alt="editar">
                                        </a>
                                        <button type="button" class="p-0 text-start bg-transparent border-0 btn-eliminar-novedad" data-id="<?= $n['idNovedad'] ?>" data-titulo="<?= htmlspecialchars($n['titulo']) ?>" title="Eliminar">
                                            <img src="<?= url('public/img/icons/trash.png') ?>" class="img-fluid btn-tabla" alt="eliminar">
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="fila-detalle d-none" id="detalle-<?= $n['idNovedad'] ?>">
                                <td colspan="6" class="p-3 bg-light">
                                    <div class="px-3 py-2 bg-white rounded border">
                                        <h3 class="h6 fw-bold mb-1">Contenido completo:</h3>
                                        <p class="m-0 text-secondary" style="font-size: 0.9rem; white-space: pre-line;"><?= nl2br(htmlspecialchars($n['contenido'])) ?></p>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; 
                        endif; ?>
                    </tbody>
                </table>

                <!-- Paginación estandarizada -->
                <div class="border-top px-3 py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                        <span class="text-muted" style="font-size:0.8rem;">
                            Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> novedades
                        </span>

                        <nav aria-label="Paginación">
                            <ul class="pagination pagination-sm m-0 gap-1">
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>" href="<?= $pagina > 1 ? urlPagina(1) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina <= 1 ? 'text-muted' : '' ?>" href="<?= $pagina > 1 ? urlPagina($pagina - 1) : '#' ?>">‹</a>
                                </li>

                                <?php
                                $rango  = 2;
                                $inicio = max(1, $pagina -$rango);
                                $fin    = min($totalPaginas, $pagina +$rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i ===$pagina ? 'active-pag' : 'text-secondary' ?>" href="<?= urlPagina($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin <$totalPaginas): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagina >=$totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" href="<?= $pagina < $totalPaginas ? urlPagina($pagina + 1) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagina >=$totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" href="<?= $pagina < $totalPaginas ? urlPagina($totalPaginas) : '#' ?>">»</a>
                                </li>
                            </ul>
                        </nav>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                            <select class="form-select form-select-sm border-0 bg-light rounded-2" style="width:auto; font-size:0.8rem; cursor:pointer;" onchange="window.location.href=this.value" aria-label="Resultados por página">
                                <?php foreach ([5, 10, 25, 50] as $op): ?>
                                    <option value="<?= urlPagina(1, $op) ?>" <?= $op ===$porPagina ? 'selected' : '' ?>>
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

    <!-- Modal para eliminar novedad -->
    <div class="modal fade" id="modalEliminarNovedad" tabindex="-1" role="dialog" aria-labelledby="modalEliminarNovedadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title fw-bold h5" id="modalEliminarNovedadLabel">Eliminar Novedad</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="modalEliminarTexto" class="text-muted m-0"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="<?= url('src/routes/novedades.php?accion=eliminar') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="idNovedad" id="modalEliminarId">
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const buscadorNovedad = document.getElementById("buscadorNovedad");
    const novedadesBody = document.getElementById("novedadTableBody");
    if (buscadorNovedad && novedadesBody) {
        buscadorNovedad.addEventListener("input", function () {
            const texto = this.value.toLowerCase().trim();
            novedadesBody.querySelectorAll("tr.fila-tabla").forEach(function (fila) {
                fila.style.display = !texto || fila.textContent.toLowerCase().includes(texto) ? "" : "none";
            });
        });
    }

    // Evitar propagación en botones y enlaces de la fila
    document.querySelectorAll(".fila-tabla a, .fila-tabla button").forEach(function(el) {
        el.addEventListener("click", function(e) {
            e.stopPropagation();
        });
    });

    // Desplegar contenido detallado
    document.querySelectorAll(".fila-tabla").forEach(function(row) {
        row.addEventListener("click", function(e) {
            const id = this.dataset.id;
            const detalleRowTarget = document.getElementById("detalle-" + id);

            if (detalleRowTarget) {
                const estaAbierto = !detalleRowTarget.classList.contains("d-none");
                document.querySelectorAll(".fila-detalle").forEach(function(det) {
                    det.classList.add("d-none");
                });

                if (!estaAbierto) {
                    detalleRowTarget.classList.remove("d-none");
                }
            }
        });
    });

    // Lógica del modal de eliminación
    const modalEl = document.getElementById("modalEliminarNovedad");
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);
        const modalTexto = document.getElementById("modalEliminarTexto");
        const modalId = document.getElementById("modalEliminarId");

        document.querySelectorAll(".btn-eliminar-novedad").forEach(function(btn) {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                modalId.value = this.dataset.id;
                modalTexto.textContent = `¿Seguro que querés eliminar la novedad "${this.dataset.titulo}"?`;
                bsModal.show();
            });
        });
    }
});
</script>