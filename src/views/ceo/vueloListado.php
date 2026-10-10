<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/VueloController.php';

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) redirect('index.php?pagina=login'); // revisarlo mejor seria otra cosa como un mensaje o no se

$ctrl = new VueloController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina      = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina   = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;
$busqueda    = trim($_GET['busqueda'] ?? '');

$resultado    = $ctrl->listarVuelosPaginado($idAerolinea, $pagina, $porPagina, $busqueda);
$vuelos       = $resultado['data'];
$total        = $resultado['total'];
$totalPaginas = $resultado['totalPaginas'];

$desde = $total === 0 ? 0 : ($pagina - 1) * $porPagina + 1;
$hasta = min($pagina * $porPagina, $total);

function urlPaginaVuelo(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    unset($params['pagina']); 
    $params['pag'] = $pag;
    if ($porPagina !== null) $params['porPagina'] = $porPagina;
    return 'index.php?pagina=vuelos&' . http_build_query($params); 
}


?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vuelos</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
        
        <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    </head>
    <body>
        <div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">
            


            <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1 ">
                <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
            </aside>

            <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden ">

                <header class="admin-header">
                    <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
                </header>

                <main class="container-fluid d-flex flex-column gap-3 p-2 admin-content ">
                    <?php require __DIR__ . '/../components/alertToast.php';  ?>
                    <div class="d-flex flex-column gap-2">
                        <h2 class="m-0 p-0 fs-2 fw-bold">Gestión de Vuelos</h2>
                        <p class="m-0 p-0 mb-2 subt">Administrá los vuelos de tu aerolínea.</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="table-responsive bg-white shadow rounded-3 pt-3 ">
                                
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
                                        <a href="<?= url('index.php?pagina=vuelo') ?>" class="btn btn-primary text-nowrap w-100">
                                            + Crear Vuelo
                                        </a>
                                    </div>
                                </div>
                                <table class="table table-hover align-middle border-top mt-2  tabla-vuelos">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="fw-bold" >ORIGEN</th>
                                            <th scope="col" class=" fw-bold ">DESTINO</th>
                                            <th scope="col" class=" fw-bold d-none d-md-table-cell">FECHA Y HORA</th> 
                                            <th scope="col" class=" fw-bold d-none d-sm-table-cell">ASIENTOS</th>
                                            <th scope="col" class="fw-bold ">PRECIO</th>
                                            <th scope="col" class="fw-bold ">ACCIONES</th>
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
                                        <tr class="fila-tabla" data-id="<?= $v['idVuelo'] ?>" style="cursor:pointer;">
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                        style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['origenVuelo']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                        style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['destinoVuelo']) ?></span>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= date('d/m/Y', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                <span class="text-muted ms-1" style="font-size:0.85rem;">
                                                    <?= date('H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell "><?= $v['asientosDisponibles'] ?></td>
                                            <td class="fw-semibold">
                                                $ <?= number_format($v['precioVuelo'], 2, ',', '.') ?>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-evenly w-100">
                                                    <a href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>"
                                                    class="p-0 bg-transparent border-0"
                                                    title="Editar vuelo">
                                                        <img src="<?= url('public/img/icons/lapiz-editar.png') ?>"
                                                            class="img-fluid btn-tabla" alt="editar">
                                                    </a>
                                                    <button type="button"
                                                            class="p-0 bg-transparent border-0 btn-eliminar-vuelo"
                                                            data-id="<?= $v['idVuelo'] ?>"
                                                            data-ruta="<?= htmlspecialchars($v['origenVuelo'] . ' → ' . $v['destinoVuelo']) ?>"
                                                            title="Eliminar vuelo">
                                                        <img src="<?= url('public/img/icons/trash.png') ?>"
                                                            class="img-fluid btn-tabla" alt="eliminar">
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="fila-detalle d-none" id="detalle-<?= $v['idVuelo'] ?>">
                                            <td colspan="6" class="p-0">
                                                <div class="detalle-aerolinea px-4 py-3">
                                                    <div class="row g-3 align-items-start">

                                                        <!-- Col 1: ruta + datos -->
                                                        <div class="col-12 col-md-4">
                                                            <p class="fw-bold m-0" style="font-size:0.95rem;">
                                                                <?= htmlspecialchars($v['origenVuelo']) ?> → <?= htmlspecialchars($v['destinoVuelo']) ?>
                                                            </p>
                                                            <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                                <div>
                                                                    <span class="text-muted">Fecha y hora:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        <?= date('d/m/Y H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <span class="text-muted">Asientos disponibles:</span>
                                                                    <span class="text-primary fw-medium ms-1"><?= $v['asientosDisponibles'] ?></span>
                                                                </div>
                                                                <div>
                                                                    <span class="text-muted">Precio:</span>
                                                                    <span class="text-primary fw-medium ms-1">
                                                                        $ <?= number_format($v['precioVuelo'], 2, ',', '.') ?>
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

                                                        <!-- Col 2: vacía / estado futuro -->
                                                        <div class="col-12 col-md-4"></div>

                                                        <!-- Col 3: acciones -->
                                                        <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                                            <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>
                                                            <a href="<?= url('index.php?pagina=vuelo&id=' . $v['idVuelo']) ?>"
                                                            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                                                                Editar vuelo
                                                            </a>
                                                            <button type="button"
                                                                    class="btn btn-outline-danger btn-sm d-flex align-items-center gap-2 btn-eliminar-vuelo"
                                                                    data-id="<?= $v['idVuelo'] ?>"
                                                                    data-ruta="<?= htmlspecialchars($v['origenVuelo'] . ' → ' . $v['destinoVuelo']) ?>">
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
                                
                                <div class="border-top px-3 py-3">
                                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                                        <span class="text-muted" style="font-size:0.8rem;">
                                            <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> vuelos
                                        </span>

                                        


                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                            <select
                                                class="form-select form-select-sm border-0 bg-light rounded-2"
                                                style="width:auto; font-size:0.8rem; cursor:pointer;"
                                                onchange="window.location.href=this.value"
                                            >
                                                <?php foreach ([5, 10, 25, 50] as $op): ?>
                                                    <option value="<?= urlPaginaVuelo(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
                                                        <?= $op ?>
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
        <div class="modal fade" id="modalEliminarVuelo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Eliminar vuelo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="modalEliminarTexto" class="text-muted m-0"></p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <form method="POST" action="<?= url('src/routes/vuelo.php?accion=eliminar') ?>">
                            <input type="hidden" name="id" id="modalEliminarId">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        

        <?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?=  url('public/js/admin/sidebar.js') ?>"></script>
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
                    if (e.target.closest(".btn-eliminar-vuelo")) return;
                    if (e.target.closest("a")) return;

                    const fila = e.target.closest(".fila-tabla");
                    if (!fila) return;

                    const id = fila.dataset.id;
                    const detalle = document.getElementById("detalle-" + id);
                    if (!detalle) return;

                    document.querySelectorAll(".fila-detalle").forEach(f => {
                        if (f !== detalle) f.classList.add("d-none");
                    });
                    detalle.classList.toggle("d-none");
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
    </body>
</html>