<?php
require_once __DIR__ . '/../components/navLinks.php';
?>

<a class="skip-link" href="#contenido">Saltar al contenido</a>
<div id="anuncioGlobal" class="visually-hidden" role="status" aria-live="polite"></div>

<header class="container-xxl">
    <div class="fila_head row mt-3 d-flex align-items-center justify-content-between g-0">

        <div class="col-2 d-flex align-items-center gap-2">
            <div class="d-flex d-lg-none justify-content-center align-items-center">
                <?php require __DIR__ . '/../components/menuMobile.php'; ?>
            </div>

            <a class="navbar-brand logoLink m-0" href="<?= htmlspecialchars($urlInicio) ?>">
                <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Volaré">
            </a>
        </div>

        <div class="col d-none d-lg-flex align-items-center justify-content-center">
            <nav aria-label="Navegación principal">
                <ul class="nav navbar-custom d-flex overflow-hidden rounded-2">
                    <?php foreach ($navLinks as $navLink): ?>
                        <li class="nav-item <?= $navLink['active'] ? 'active' : '' ?>">
                            <a href="<?= htmlspecialchars($navLink['href']) ?>"
                               class="nav-link text-white"
                               <?= $navLink['active'] ? 'aria-current="page"' : '' ?>>
                                <?= htmlspecialchars($navLink['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>

        <div class="col-auto d-flex justify-content-center align-items-center gap-2">

            <!-- CAMPANITA DE NOTIFICACIONES (SOLO PARA ADMIN) -->
            <?php if ($isLoggedIn && isset($rol) && $rol === 'admin'): ?>
                <div class="dropdown">
                    <button type="button" class="btn btn-custom border-0 position-relative rounded-circle p-2 d-flex align-items-center justify-content-center text-decoration-none" id="dropdownNotificaciones" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-label="Notificaciones y pendientes" style="width: 40px; height: 40px; margin-right: 10px;">
                        <i class="bi bi-bell fs-5" aria-hidden="true"></i>
                    </button>
                    <span id="notificacionesEstado" class="visually-hidden" role="status" aria-live="polite"></span>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="width: 320px; max-height: 400px; overflow-y: auto;" aria-labelledby="dropdownNotificaciones">
                        <li><div class="dropdown-header fw-bold text-dark border-bottom pb-2 px-2">Notificaciones y Pendientes</div></li>
                        <li><span class="dropdown-item-text text-center text-muted py-3 small">Cargando...</span></li>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <!-- ESTADO LOGUEADO -->
                <div class="dropdown">
                    <button class="btn btn-custom dropdown-toggle d-flex align-items-center gap-2"
                            type="button"
                            id="dropdownUsuario"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="Abrir menú de usuario">
                        <img src="<?= url('public/img/icons/usuario.png') ?>" alt="" class="icono_perfil">
                        <span class="d-none d-md-inline"><?= htmlspecialchars($usuario['nombre'] ?? 'Mi Cuenta') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownUsuario">
                        <?php if ($esStaff): ?>
                            <li><a class="dropdown-item" href="<?= htmlspecialchars($urlPanel) ?>">Mi Panel (<?= strtoupper($rol) ?>)</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>

                        <li><a class="dropdown-item" href="<?= htmlspecialchars($urlPerfil) ?>">Mi Perfil</a></li>

                        <?php if ($rol === 'cliente'): ?>
                            <li><a class="dropdown-item" href="<?= url('index.php?pagina=mis-reservas') ?>">Mis reservas</a></li>
                            <li><a class="dropdown-item" href="<?= url('index.php?pagina=historial-compras') ?>">Historial de compras</a></li>
                        <?php endif; ?>

                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= htmlspecialchars($urlLogout) ?>">Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- ESTADO NO LOGUEADO -->
                <a href="<?= htmlspecialchars($urlLogin) ?>" class="btn btn-custom align-items-center gap-2" aria-label="Ingresar a mi cuenta">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="" class="icono_perfil">
                    <span class="d-none d-md-inline">Ingresar</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    const URL_NOTIFICACIONES = "<?= url('src/routes/notificaciones.php') ?>";
    const URL_MARCAR_NOTIFICACION = "<?= url('src/routes/marcar-notificacion.php') ?>";
    const URL_LISTADO_CEOS = "<?= url('index.php?pagina=ceos&seccion=listado') ?>";
    const URL_LISTADO_PROMOCIONES = "<?= url('index.php?pagina=promociones&seccion=listado') ?>";
</script>
<script src="<?= url('public/js/admin/notificaciones.js') ?>"></script>
<script src="<?= url('public/js/accesibilidad.js') ?>" defer></script>
