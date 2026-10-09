<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$porPaginaPermitidos = [5, 10, 25, 50];
$pagina       = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina    = in_array($porPaginaRaw, $porPaginaPermitidos) ? $porPaginaRaw : 10;

$busqueda = trim($_GET['busqueda'] ?? '');
$estado   = $_GET['estado'] ?? '';

$resultado    = $ctrl->listarUsuariosPaginado($pagina, $porPagina, $estado, $busqueda);
$usuarios     = $resultado['data'];
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

// Cálculo de métricas y tendencias
$stats = $ctrl->obtenerEstadisticasUsuarios();
$diff  = $stats['diff'] ?? 0;

if ($diff > 0) {
    $flechaImg   = 'flecha-up.png';
    $flechaTexto = '+' . $diff . ' vs mes anterior';
    $flechaColor = 'text-success';
} elseif ($diff < 0) {
    $flechaImg   = 'flecha-down.png';
    $flechaTexto = $diff . ' vs mes anterior';
    $flechaColor = 'text-danger';
} else {
    $flechaImg   = 'simbolo-igual.png';
    $flechaTexto = 'Igual que el mes anterior';
    $flechaColor = 'text-muted';
}
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="#">Usuarios</a></li>
            <li class="breadcrumb-item active" aria-current="page">Listado de Usuarios</li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h2 class="m-0 p-0 fs-2 fw-bold">Gestión de Usuarios</h2>
        <p class="m-0 p-0 mb-1 subt">Administrá los usuarios registrados en el sistema.</p>

        <!-- Tarjetas de métricas -->
        <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 py-2 px-4">
                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                            <i class="bi bi-people-fill text-primary fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Total Usuarios</p>
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
                            <i class="bi bi-person-plus-fill text-primary fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Nuevos este mes</p>
                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $stats['nuevosEsteMes'] ?></p>
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
                            <i class="bi bi-person-badge-fill text-primary fs-3"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Clientes / CEOs / Admins</p>
                            <p class="fs-5 fw-bold text-dark m-0 p-0"><?= $stats['clientes'] ?> / <?= $stats['ceos'] ?> / <?= $stats['admins'] ?></p>
                        </div>
                    </div>
                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                        <img src="<?= url('public/img/icons/placeholdersCards/simbolo-igual.png') ?>" class="img-fluid" alt="igual" style="width:20px;height:20px;object-fit:contain;">
                        <p class="fw-normal m-0 p-0 text-muted" style="font-size:14px;">Distribución por roles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h3 class="m-0 p-0 fs-4 fw-bold">Listado de Usuarios</h3>
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <form method="GET" action="index.php" class="d-flex gap-2">
                            <input type="hidden" name="pagina" value="usuarios">
                            <input type="hidden" name="seccion" value="listado">
                            <input 
                                type="text" 
                                name="busqueda" 
                                id="buscadorUsuario" 
                                class="form-control ctm-inp" 
                                placeholder="Buscar..." 
                                value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>"
                                style="max-width: 240px;"
                            >
                            <button type="submit" class="btn btn-sm btn-outline-primary">Buscar</button>
                        </form>
                    </div>
                </div>

                <table class="table table-hover align-middle border-top mt-2 tabla-usuarios">
                    <thead>
                        <tr>
                            <th scope="col" class="fw-bold">USUARIO</th>
                            <th scope="col" class="fw-bold d-none d-md-table-cell">DOCUMENTO</th>
                            <th scope="col" class="fw-bold">EMAIL</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ROL</th>
                            <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                            <th scope="col" class="text-center fw-bold">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="usuarioTableBody">
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No hay usuarios registrados todavía.
                                </td>
                            </tr>
                        <?php else: 
                            foreach ($usuarios as $u): ?>
                            <tr class="fila-tabla" data-id="<?= $u['idUsuario'] ?>" style="cursor:pointer;">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:30px; height:30px; flex-shrink:0;">
                                            <span class="text-white fw-bold text-uppercase" style="font-size:0.65rem;">
                                                <?= htmlspecialchars(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)) ?>
                                            </span>
                                        </div>
                                        <span class="fw-medium"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></span>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><?= htmlspecialchars($u['tipoDocumento'] . ' ' . $u['nroDocumento']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td class="d-none d-sm-table-cell">
                                    <span class="badge text-bg-info text-capitalize"><?= htmlspecialchars($u['rol']) ?></span>
                                </td>
                                <td class="d-none d-sm-table-cell"> 
                                    <?php if ($u['activo']): ?>
                                        <span class="badge text-bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-evenly w-100 align-items-center">
                                        <!-- Ver detalle del usuario -->
                                        <a 
                                            href="<?= url('index.php?pagina=usuario&seccion=detalle&id=' . $u['idUsuario']) ?>" 
                                            class="p-0 text-start bg-transparent border-0 btn-ver-detalle"
                                            title="Ver Detalle"
                                        >
                                            <img src="<?= url('public/img/icons/readTabla.png') ?>" class="img-fluid btn-tabla" alt="icono ver detalle">
                                        </a>

                                        <!-- Editar usuario -->
                                        <a 
                                            href="<?= url('index.php?pagina=usuario&seccion=editar&id=' . $u['idUsuario']) ?>" 
                                            class="p-0 text-start bg-transparent border-0"
                                            title="Editar Usuario"
                                        >
                                            <img src="<?= url('public/img/icons/lapiz-editar.png') ?>" class="img-fluid btn-tabla" alt="icono editar usuario">
                                        </a>

                                        <!-- Activar / Desactivar -->
                                        <button 
                                            type="button"
                                            class="p-0 text-start bg-transparent border-0 btn-toggle-estado"
                                            data-id="<?= $u['idUsuario'] ?>"
                                            data-activo="<?= $u['activo'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>"
                                            title="<?= $u['activo'] ? 'Desactivar Usuario' : 'Activar Usuario' ?>"
                                        >
                                            <img src="<?= url($u['activo'] ? 'public/img/icons/trash.png' : 'public/img/icons/redo.png') ?>" class="img-fluid btn-tabla" alt="<?= $u['activo'] ? 'desactivar' : 'activar' ?>">
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Fila desplegable de detalles -->
                            <tr class="fila-detalle d-none" id="detalle-<?= $u['idUsuario'] ?>">
                                <td colspan="6" class="p-0">
                                    <div class="detalle-aerolinea px-4 py-3">
                                        <div class="row g-3 align-items-start">
                                            <!-- Columna 1: avatar + datos -->
                                            <div class="col-12 col-md-4 d-flex gap-3">
                                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;">
                                                    <span class="text-white fw-bold text-uppercase" style="font-size:1rem;">
                                                        <?= htmlspecialchars(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)) ?>
                                                    </span>
                                                </div>

                                                <div>
                                                    <p class="fw-bold m-0" style="font-size:0.95rem;"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></p>
                                                    <p class="text-muted m-0" style="font-size:0.8rem;"><?= htmlspecialchars($u['email']) ?></p>

                                                    <div class="mt-2 d-flex flex-column gap-1" style="font-size:0.8rem;">
                                                        <div><span class="text-muted">Documento:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($u['tipoDocumento'] . ' ' . $u['nroDocumento']) ?></span></div>
                                                        <div><span class="text-muted">Teléfono:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($u['telefono'] ?? '—') ?></span></div>
                                                        <div><span class="text-muted">Rol:</span> <span class="text-primary fw-medium ms-1 text-capitalize"><?= htmlspecialchars($u['rol']) ?></span></div>
                                                        <?php if (!empty($u['nombreAerolinea'])): ?>
                                                            <div><span class="text-muted">Aerolínea asociada:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars($u['nombreAerolinea']) ?></span></div>
                                                        <?php endif; ?>
                                                        <div><span class="text-muted">Fecha registro:</span> <span class="text-primary fw-medium ms-1"><?= htmlspecialchars(date('d/m/Y', strtotime($u['fechaCreacion']))) ?></span></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Columna 2: estado -->
                                            <div class="col-12 col-md-4 d-flex flex-column gap-2" style="font-size:0.85rem;">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Estado</span>
                                                    <span class="fw-medium <?= $u['activo'] ? 'text-success' : 'text-secondary' ?>">
                                                        <?= $u['activo'] ? '● Activo' : '● Inactivo' ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Columna 3: acciones -->
                                            <div class="col-12 col-md-4 d-flex flex-column gap-2">
                                                <p class="fw-semibold m-0 mb-1" style="font-size:0.85rem;">Acciones disponibles</p>
                                                
                                                <a href="<?= url('index.php?pagina=usuario&seccion=editar&id=' . $u['idUsuario']) ?>" 
                                                class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-2">
                                                    Editar usuario
                                                </a>

                                                <button type="button"
                                                    class="btn btn-sm d-flex align-items-center justify-content-center gap-2 btn-toggle-estado
                                                        <?= $u['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                                    data-id="<?= $u['idUsuario'] ?>"
                                                    data-activo="<?= $u['activo'] ?>"
                                                    data-nombre="<?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>">
                                                    <?= $u['activo'] ? 'Desactivar usuario' : 'Activar usuario' ?>
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

                <!-- Paginador -->
                <div class="border-top px-3 py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                        <span class="text-muted" style="font-size:0.8rem;">
                            Mostrando <strong><?= $desde ?></strong> a <strong><?= $hasta ?></strong> de <strong><?= $total ?></strong> usuarios
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
                                $inicio = max(1, $pagina - $rango);
                                $fin    = min($totalPaginas, $pagina + $rango);
                                ?>

                                <?php if ($inicio > 1): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                    <li class="page-item">
                                        <a class="page-link rounded-2 border-0 <?= $i === $pagina ? 'active-pag' : 'text-secondary' ?>" href="<?= urlPagina($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" href="<?= $pagina < $totalPaginas ? urlPagina($pagina + 1) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pagina >= $totalPaginas ? 'text-muted' : '' ?>" href="<?= $pagina < $totalPaginas ? urlPagina($totalPaginas) : '#' ?>">»</a>
                                </li>
                            </ul>
                        </nav>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                            <select class="form-select form-select-sm border-0 bg-light rounded-2" style="width:auto; font-size:0.8rem; cursor:pointer;" onchange="window.location.href=this.value">
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

    <!-- Modal Toggle Estado -->
    <div class="modal fade" id="modalToggleEstadoUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="modalToggleTituloUsuario"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="modalToggleTextoUsuario" class="text-muted m-0"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" action="<?= url('src/routes/usuarios.php?accion=toggleEstado') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <input type="hidden" name="id" id="modalToggleIdUsuario">
                        <button type="submit" class="btn btn-sm" id="modalToggleConfirmarUsuario">Confirmar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Desplegar fila de detalle al hacer clic en la fila
    document.querySelectorAll(".fila-tabla").forEach(function(row) {
        row.addEventListener("click", function(e) {
            // Ignorar clics si son sobre un enlace o botón interno (editar, ver detalle o toggle)
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.btn-toggle-estado')) return;

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

    // Modal de confirmación toggle estado
    const modalEl     = document.getElementById("modalToggleEstadoUsuario");
    const modalTitulo = document.getElementById("modalToggleTituloUsuario");
    const modalTexto  = document.getElementById("modalToggleTextoUsuario");
    const modalId     = document.getElementById("modalToggleIdUsuario");
    const modalBtn    = document.getElementById("modalToggleConfirmarUsuario");
    
    if (modalEl) {
        const bsModal = new bootstrap.Modal(modalEl);

        document.querySelectorAll(".btn-toggle-estado").forEach(function(btn) {
            btn.addEventListener("click", function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id     = this.dataset.id;
                const activo = this.dataset.activo === "1";
                const nombre = this.dataset.nombre || "este usuario";

                modalId.value = id;

                if (activo) {
                    modalTitulo.textContent = "Desactivar usuario";
                    modalTexto.textContent  = '¿Seguro que querés desactivar al usuario "' + nombre + '"?';
                    modalBtn.textContent    = "Desactivar";
                    modalBtn.className      = "btn btn-danger btn-sm";
                } else {
                    modalTitulo.textContent = "Activar usuario";
                    modalTexto.textContent  = '¿Seguro que querés activar al usuario "' + nombre + '"?';
                    modalBtn.textContent    = "Activar";
                    modalBtn.className      = "btn btn-success btn-sm";
                }

                bsModal.show();
            });
        });
    }
});
</script>