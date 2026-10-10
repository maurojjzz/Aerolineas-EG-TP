<footer class="bg-dark text-light pt-4 pb-2 mt-auto">
    <div class="container-xxl">
        <div class="row gy-4 justify-content-between">
            
            <!-- Columna 1: Brand & Slogan -->
            <div class="col-12 col-md-2">
                <a class="d-inline-block mb-1" href="#">
                    <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de la página" style="max-height: 45px;">
                </a>
                <p class="text-secondary small">
                    Conectamos personas y destinos con una experiencia rápida, segura y confiable. Vuela sin límites.
                </p>
            </div>

            <!-- Columna 2: Navegación rápida -->
            <div class="col-6 col-md-2">
                <h6 class="text-uppercase text-white fw-bold mb-3">Navegación</h6>
                <ul class="list-unstyled text-small">
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Inicio</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Vuelos</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Promociones</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Novedades</a></li>
                </ul>
            </div>

            <!-- Columna 3: Soporte / Legales -->
            <div class="col-6 col-md-2">
                <h6 class="text-uppercase text-white fw-bold mb-3">Soporte</h6>
                <ul class="list-unstyled text-small">
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Centro de Ayuda</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Términos y Condiciones</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Políticas de Privacidad</a></li>
                    <li class="mb-2"><a class="link-secondary text-decoration-none" href="#">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <!-- Columna 4: Contacto / Redes -->
            <div class="col-12 col-md-3">
                <h6 class="text-uppercase text-white fw-bold mb-3">Contacto</h6>
                <p class="text-secondary small mb-2">
                    <i class="bi bi-envelope me-1"></i> soporte@aerolinea.com
                </p>
                <p class="text-secondary small mb-3">
                    <i class="bi bi-telephone me-1"></i> +54 (0341) 400-0000
                </p>
            </div>

        </div>

        <hr class="my-4 border-secondary opacity-50">

        <!-- Copyright y Créditos -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center small text-secondary">
            <p class="m-0">&copy; <?= date('Y') ?> Aerolínea. Todos los derechos reservados.</p>
            <p class="m-0">Desarrollado para Entornos Gráficos</p>
        </div>
    </div>
</footer>