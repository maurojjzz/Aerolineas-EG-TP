<?php
require_once __DIR__ . '/../components/navLinks.php';
?>
<footer class="bg-dark text-light pt-4 pb-2 mt-auto">
    <div class="container-xxl">
        <div class="row gy-4 justify-content-between">
            
            <!-- Columna 1: Brand & Slogan -->
            <div class="col-12 col-md-2">
                <a class="d-inline-block mb-1" href="<?= htmlspecialchars($urlInicio) ?>">
                    <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de Volaré" style="max-height: 45px;">
                </a>
                <p class="text-white-50 small">
                    Conectamos personas y destinos con una experiencia rápida, segura y confiable. Volaré.
                </p>
            </div>

            <!-- Columna 2: Navegación rápida -->
            <div class="col-6 col-md-2">
                <h2 class="h6 text-uppercase text-white fw-bold mb-3">Navegación</h2>
                <ul class="list-unstyled text-small">
                    <?php if (!$isLoggedIn): ?>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=inicio') ?>">Inicio</a></li>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=buscar-vuelos') ?>">Vuelos</a></li>
                    <?php elseif ($rol === 'cliente'): ?>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= htmlspecialchars($urlInicio) ?>">Inicio</a></li>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=mis-reservas') ?>">Mis reservas</a></li>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=historial-compras') ?>">Historial de compras</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= htmlspecialchars($urlPanel) ?>">Mi Panel</a></li>
                        <li class="mb-2"><a class="link-light text-decoration-none" href="<?= htmlspecialchars($urlPerfil) ?>">Mi Perfil</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Columna 3: Soporte / Legales -->
            <div class="col-6 col-md-2">
                <h2 class="h6 text-uppercase text-white fw-bold mb-3">Soporte</h2>
                <ul class="list-unstyled text-small">
                    <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=ayuda') ?>">Centro de Ayuda</a></li>
                    <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=terminos') ?>">Términos y Condiciones</a></li>
                    <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=privacidad') ?>">Políticas de Privacidad</a></li>
                </ul>
            </div>

            <!-- Columna 4: Contacto / Redes -->
            <div class="col-12 col-md-3">
                <ul class="list-unstyled text-small">
                    <li class="mb-2"><span class="text-white-50"><i class="bi bi-envelope" aria-hidden="true"></i> soporte@volare.com</span></li>
                    <li class="mb-2"><a class="link-light text-decoration-none" href="<?= url('index.php?pagina=contacto') ?>"><i class="bi bi-headset" aria-hidden="true"></i> Formulario de Contacto</a></li>
                </ul>
            </div>

        </div>

        <hr class="my-4 border-secondary opacity-50">

        <!-- Copyright y Créditos -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-white-50">
            <p class="m-0">&copy; <?= date('Y') ?> Todos los derechos reservados.</p>
            <p class="m-0">Desarrollado para Entornos Gráficos</p>
        </div>
    </div>
</footer>
