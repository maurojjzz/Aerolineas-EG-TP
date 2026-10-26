<?php

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/conexion.php';

$rol = rolActual();
$usuario = usuarioActual();

require_once __DIR__ . '/../../controllers/UsuarioController.php';
if (isset($link) && $link instanceof mysqli) {
    $notifCtrl = new UsuarioController($link);
    $notificaciones = $notifCtrl->obtenerNotificacionesPendientes();
    $totalNotif = $notificaciones['total'] ?? 0;
} else {
    $notificaciones = ['ceos' => [], 'promociones' => [], 'total' => 0];
    $totalNotif = 0;
}

?>
<div id="anuncioGlobal" class="visually-hidden" role="status" aria-live="polite"></div>

<div class="container-header d-flex align-items-center bg-light row border-bottom border-2 w-100 m-0 p-0 g-0">
    <button type="button" class=" col-1 btn ctm-btn-header d-none d-md-block" id="sidebarToggleHamburguesa" aria-label="Mostrar u ocultar el menú lateral" aria-controls="sidebarAdmin" aria-expanded="true">
        <img src="<?= url('public/img/icons/menu-hamburguesa.png') ?>" alt="Icono de menú" class="logo-header-admin opacity-75">
    </button>

    <!-- menu hamburguesa para mobile -->
    <button
        type="button"
        class=" col-1 btn ctm-btn-header d-md-none"
        id="sidebarToggleHamburguesaMobile"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasAdmin"
        aria-controls="offcanvasAdmin"
        aria-expanded="false"
        aria-label="Abrir menú de navegación"
    >
            <img src="<?= url('public/img/icons/menu-hamburguesa.png') ?>" alt="Icono de menú" class="logo-header-admin opacity-75">
    </button>

    <p class="col-6 col-sm m-0 p-0 h5">
        <span class="d-none d-sm-block">
            <?php if ($rol === 'ceo') { echo 'Panel de CEO'; } else { echo 'Panel de Administración'; } ?>
        </span>
        <span class="d-sm-none ms-3">
            <?php if ($rol === 'ceo') { echo 'CEO'; } else { echo 'Admin'; } ?>
        </span>

    </p>

    <div class="col ctm-user-info d-flex justify-content-end align-items-center gap-2 pe-4 position-relative">
        <!-- Dropdown de Notificaciones / Campana -->
        <?php if (isset($rol) && $rol === 'admin' && $rol !== 'ceo'): ?>
        <div class="dropdown me-3 d-none d-sm-block">
                <button class="btn btn-bell position-relative p-0 m-0 bg-transparent border-0 d-flex align-items-center justify-content-center" 
                        type="button" 
                        id="dropdownNotificaciones" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false"
                        aria-haspopup="true"
                        aria-label="Notificaciones y pendientes<?= $totalNotif > 0 ? ': ' . $totalNotif . ' sin leer' : '' ?>"
                        style="width: 40px; height: 40px;">
                    <img src="<?= url('public/img/icons/bell.png') ?>" alt="Icono de campana" class="bell-noti opacity-75" style="width: 22px; height: 22px;">
                    <?php if ($totalNotif > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?= $totalNotif ?>
                            <span class="visually-hidden">mensajes no leídos</span>
                        </span>
                    <?php endif; ?>
                </button>
            <span id="notificacionesEstado" class="visually-hidden" role="status" aria-live="polite"></span>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" aria-labelledby="dropdownNotificaciones" style="width: 320px; max-height: 400px; overflow-y: auto;">
                <li>
                    <div class="dropdown-header fw-bold text-dark border-bottom pb-2 px-2">
                        Notificaciones y Pendientes
                    </div>
                </li>

                <?php if ($totalNotif === 0): ?>
                    <li><span class="dropdown-item-text text-center text-muted py-3 small">No hay notificaciones pendientes.</span></li>
                <?php else: ?>

                    <!-- Listado de CEOs Pendientes -->
                    <?php foreach ($notificaciones['ceos'] as $ceo): ?>
                        <li>
                            <a  class="dropdown-item rounded-2 py-2 border-bottom text-wrap d-flex flex-column gap-1" 
                                href="<?= url('index.php?pagina=ceos&seccion=listado') ?>"
                                data-tipo="ceo" 
                                data-id="<?= $ceo['id'] ?>"
                            >
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge text-bg-warning text-dark" style="font-size: 0.7rem;">Nuevo CEO</span>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= date('d/m/Y', strtotime($ceo['fechaCreacion'])) ?></small>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                    <?= htmlspecialchars($ceo['nombre'] . ' ' . $ceo['apellido']) ?>
                                </span>
                                <span class="text-muted text-truncate" style="font-size: 0.75rem;">Requiere habilitación en el sistema.</span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <!-- Listado de Promociones Pendientes -->
                    <?php foreach ($notificaciones['promociones'] as $promo): ?>
                        <li>
                            <a  class="dropdown-item rounded-2 py-2 border-bottom text-wrap d-flex flex-column gap-1" 
                                href="<?= url('index.php?pagina=promociones&seccion=listado') ?>"
                                data-tipo="promocion" 
                                data-id="<?= $promo['id'] ?>"
                            >
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge text-bg-info text-white" style="font-size: 0.7rem;">Promoción</span>
                                    <span class="text-success fw-bold" style="font-size: 0.75rem;"><?= htmlspecialchars($promo['codigo']) ?></span>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">
                                    <?= htmlspecialchars($promo['nombre']) ?>
                                </span>
                                <span class="text-muted text-truncate" style="font-size: 0.75rem;">Aerolínea: <?= htmlspecialchars($promo['nombreAerolinea']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>

                <?php endif; ?>
            </ul>
        </div>

            <?php endif; ?>
        <!-- Dropdown de Usuario -->
        <div class="dropdown">
            <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2"
                    type="button"
                    id="dropdownUsuarioAdmin"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-haspopup="true"
                    aria-label="Abrir menú de usuario">

                <span class="pfp-icon d-flex align-items-center justify-content-center rounded-circle" aria-hidden="true">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'A', 0, 1) . substr($usuario['apellido'] ?? 'J', 0, 1)) ?>
                </span>

                <span class="d-none d-md-flex flex-column align-items-start justify-content-center gap-0 ctm-user-text">
                    <span class="d-block m-0 p-0 ctm-rol"><?= htmlspecialchars(ucfirst($rol)) ?></span>
                    <span class="d-block m-0 p-0 ctm-name">
                        <?= htmlspecialchars($usuario['nombre'] ?? '') . ' ' . htmlspecialchars($usuario['apellido'] ?? '') ?>
                    </span>
                </span>

                <span class="down-arrow" aria-hidden="true" style="font-size:1.5rem;font-weight:500;">⌄</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="dropdownUsuarioAdmin">
                <li>
                    <div class="px-3 py-2 border-bottom">
                        <p class="m-0 fw-semibold" style="font-size:0.9rem;">
                            <?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?>
                        </p>
                        <p class="m-0 text-muted" style="font-size:0.8rem;">
                            <?= htmlspecialchars($usuario['email'] ?? '') ?>
                        </p>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= $rol === 'ceo' ? url('index.php?pagina=ceo') : url('index.php?pagina=aerolinea&seccion=listado') ?>">
                        Mi Panel
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('index.php?pagina=perfil') ?>">
                        Mi perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= url('index.php?pagina=ayuda') ?>">
                        Ayuda
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?= url('src/routes/usuarios.php?accion=logout') ?>">
                        Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>

</div>

<script>
    const URL_NOTIFICACIONES = "<?= url('src/routes/notificaciones.php') ?>";
    const URL_MARCAR_NOTIFICACION = "<?= url('src/routes/marcar-notificacion.php') ?>";
    const URL_LISTADO_CEOS = "<?= url('index.php?pagina=ceos&seccion=listado') ?>";
    const URL_LISTADO_PROMOCIONES = "<?= url('index.php?pagina=promociones&seccion=listado') ?>";
</script>
<script src="<?= url('public/js/admin/notificaciones.js') ?>"></script>
<script src="<?= url('public/js/accesibilidad.js') ?>" defer></script>
<script src="<?= url('public/js/admin/actionLoading.js') ?>"></script>
