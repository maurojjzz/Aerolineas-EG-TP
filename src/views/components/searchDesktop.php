<?php
    $paginaActual = $_GET['pagina'] ?? 'inicio';
    $esCliente    = $paginaActual === 'cliente' && rolActual() === 'cliente';
    $mostrarTabs  = $paginaActual === 'inicio' || $esCliente;
?>
<div class="container-xxl">

    <div class="desktop-search bg-light rounded-4 shadow-lg overflow-visible border border-2">

        <?php if ($esCliente): ?>
            <div class="desktop-search-header">
                <div class="d-flex w-100 flex-row justify-content-start align-items-center gap-3">
                    <img class="img-fluid ms-3" src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" alt="" style="width: 42px; height: 42px;">
                    <div class="d-flex flex-column justify-content-center align-items-start ms-2">
                        <span class="fw-semibold fs-5 p-0 m-0" style="color:#137AFF;">Buscar vuelos</span>
                        <span class="text-muted fs-6">Encuentra las mejores opciones para tu próximo viaje</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($mostrarTabs): ?>
            <div class="desktop-search-tabs d-flex align-items-center" role="tablist" aria-label="Qué querés buscar">
                <button type="button" role="tab" aria-selected="true" aria-controls="desktopPanelVuelos"
                        id="desktopTabVuelos" class="desktop-tab active d-flex align-items-center gap-2" data-tab="vuelos">
                    <img src="<?= url('public/img/icons/avion.png') ?>" alt="">
                    <span>Vuelos</span>
                </button>

                <button type="button" role="tab" aria-selected="false" aria-controls="desktopPanelPromociones"
                        id="desktopTabPromociones" class="desktop-tab" data-tab="promociones">
                    <img src="<?= url('public/img/icons/promocion.png') ?>" alt="">
                    <span>Promociones</span>
                </button>

                <button type="button" role="tab" aria-selected="false" aria-controls="desktopPanelNovedades"
                        id="desktopTabNovedades" class="desktop-tab" data-tab="novedades">
                    <img src="<?= url('public/img/icons/novedades.png') ?>" alt="">
                    <span>Novedades</span>
                </button>
            </div>
        <?php endif; ?>

        <div id="desktopPanelVuelos" role="tabpanel" aria-labelledby="desktopTabVuelos" tabindex="0">
            <!-- RADIOS IDA / IDA Y VUELTA -->
            <fieldset id="contenedorRadiosViaje" class="border-0 m-0 d-flex align-items-center gap-4 px-4 pt-3">
                <legend class="visually-hidden">Tipo de viaje</legend>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo_viaje_desktop" id="desktopSoloIda" value="solo_ida" checked>
                    <label class="form-check-label fw-semibold" for="desktopSoloIda" style="font-size:0.9rem;">Solo ida</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo_viaje_desktop" id="desktopIdaVuelta" value="ida_vuelta">
                    <label class="form-check-label fw-semibold" for="desktopIdaVuelta" style="font-size:0.9rem;">Ida y vuelta</label>
                </div>
            </fieldset>

            <form
                class="desktop-search-form d-flex align-items-center"
                action="<?= url('index.php?pagina=buscar-vuelos') ?>"
                method="GET"
            >
                <input type="hidden" name="pagina" value="buscar-vuelos">
                <input type="hidden" name="tipo_viaje" id="desktopTipoViajeHidden" value="solo_ida">

                <div class="desktop-field desktop-airport position-relative">
                    <label for="from-desktop">Salida</label>
                    <input type="text" id="from-desktop" name="origen" class="desktop-main-input" placeholder="Ciudad" autocomplete="off" required>
                    <p id="from-location-desktop">Ingrese salida</p>
                    <span class="desktop-arrow">⌄</span>
                    <div id="from-results-desktop" class="autocomplete-results"></div>
                </div>

                <button type="button" class="swap-btn d-flex align-items-center justify-content-center" id="swap-desktop" aria-label="intercambiar origen y destino">
                    ⇄
                </button>

                <div class="desktop-field desktop-airport position-relative">
                    <label for="to-desktop">Destino</label>
                    <input
                        type="text"
                        id="to-desktop"
                        class="desktop-main-input"
                        placeholder="Ciudad"
                        autocomplete="off"
                        name="destino"
                        required
                    >
                    <p id="to-location-desktop">Ingrese destino</p>
                    <span class="desktop-arrow">⌄</span>
                    <div id="to-results-desktop" class="autocomplete-results"></div>
                </div>

                <div class="desktop-divider"></div>

                <div class="desktop-field desktop-date">
                    <label for="fecha-desktop">Salida</label>
                    <input type="date" id="fecha-desktop" class="desktop-date-input" min="<?php echo date('Y-m-d'); ?>" name="fecha" >
                    <p id="fecha-sub-desktop">Seleccione fecha</p>
                </div>

                <!-- BLOQUE DE VUELTA -->
                <div class="desktop-divider d-none" id="dividerVueltaDesktop"></div>

                <div class="desktop-field desktop-date d-none" id="contenedorFechaVueltaDesktop">
                    <label for="fecha-vuelta-desktop">Vuelta</label>
                    <input type="date" id="fecha-vuelta-desktop" class="desktop-date-input" min="<?php echo date('Y-m-d'); ?>" name="fecha_vuelta">
                    <p id="fecha-vuelta-sub-desktop">Seleccione fecha</p>
                </div>

                <div class="desktop-divider"></div>

                <div class="desktop-field desktop-passengers">
                    <label for="passengers-desktop">Pasajeros</label>
                    <select id="passengers-desktop" class="form-select desktop-passenger-select" name="pasajeros">
                        <option value="1">1 Pasajero</option>
                        <option value="2">2 Pasajeros</option>
                        <option value="3">3 Pasajeros</option>
                        <option value="4">4 Pasajeros</option>
                        <option value="5">5 Pasajeros</option>
                    </select>
                </div>

                <button type="submit" class="btn desktop-search-btn ms-auto gap-2">
                    <img src="<?= url('public/img/icons/lupa.png') ?>" alt="" class="autocomplete-icon">
                    Buscar Vuelos
                </button>
            </form>
        </div>

        <?php if ($mostrarTabs): ?>
            <div id="desktopPanelTabs" class="home-tabs-panels d-none">
                <?php $homeTabsPrefijo = 'desktop'; require __DIR__ . '/homeTabsCards.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
