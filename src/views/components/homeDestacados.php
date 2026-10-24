<?php
/**
 * Sección de Promociones destacadas + Últimas novedades + beneficios
 * del home público. Reutiliza los datos cacheados de homeCardsData.php.
 */

require_once __DIR__ . '/homeCardsData.php';

$homeDestData      = homeTabsCardsData(6);
$homeDestPromos    = array_slice($homeDestData['promociones'], 0, 6);
$homeDestNovedades = array_slice($homeDestData['novedades'], 0, 3);
$homeDestVariantes = ['a', 'b', 'c'];
?>

<section class="home-destacados container-xxl px-3 mb-5" aria-label="Promociones y novedades">

    <div class="home-destacados-grid">

        <!-- ── Promociones destacadas ─────────────────────────── -->
        <div class="home-destacados-panel home-destacados-promos">
            <div class="home-destacados-head">
                <div class="home-destacados-head-text">
                    <h2 class="home-destacados-title">Promociones destacadas</h2>
                    <p class="home-destacados-sub">Aprovechá los descuentos vigentes para tu próximo viaje.</p>
                </div>
                <a href="<?= url('index.php?pagina=cliente-novedades&tab=promociones') ?>" class="home-destacados-link">
                    Ver todas las promociones <span aria-hidden="true">→</span>
                </a>
            </div>

            <?php if (empty($homeDestPromos)): ?>
                <div class="home-destacados-empty">
                    <i class="bi bi-tag" aria-hidden="true"></i>
                    <p>No hay promociones vigentes en este momento.</p>
                </div>
            <?php else: ?>
                <div class="home-promos-carousel<?= count($homeDestPromos) <= 1 ? ' is-static' : '' ?>" data-carousel>
                    <div class="home-promos-viewport">
                        <div class="home-promos-track"
                             data-carousel-track
                             role="region"
                             aria-label="Promociones destacadas"
                             tabindex="0">
                            <?php foreach ($homeDestPromos as $homeDestIdx => $homeDestPromo):
                                $homeDestPct      = (int)round((float)($homeDestPromo['descuentoPorcentaje'] ?? 0));
                                $homeDestVariante = $homeDestVariantes[$homeDestIdx % count($homeDestVariantes)];
                                $homeDestLogo     = homeTabsCardImagen($homeDestPromo['logoUrl'] ?? null);
                                $homeDestIata     = strtoupper(trim((string)($homeDestPromo['codigoIATA'] ?? '')));
                                $homeDestOrigen   = strtoupper(trim((string)($homeDestPromo['codigoOrigen'] ?? '')));
                                $homeDestDestino  = strtoupper(trim((string)($homeDestPromo['codigoDestino'] ?? '')));

                                if ($homeDestOrigen !== '' && $homeDestDestino !== '') {
                                    $homeDestRuta = $homeDestOrigen . ' → ' . $homeDestDestino;
                                } elseif (!empty($homeDestPromo['idVuelo'])) {
                                    $homeDestRuta = 'Vuelo #' . (int)$homeDestPromo['idVuelo'];
                                } else {
                                    $homeDestRuta = 'Toda la aerolínea';
                                }
                            ?>
                                <a href="<?= url('index.php?pagina=cliente-novedades&tab=promociones') ?>"
                                   class="home-promo-card home-promo-card--<?= $homeDestVariante ?>">
                                    <span class="home-promo-top">
                                        <span class="home-promo-airline">
                                            <?php if ($homeDestLogo !== ''): ?>
                                                <img src="<?= htmlspecialchars($homeDestLogo) ?>"
                                                     alt="<?= htmlspecialchars($homeDestPromo['nombreAerolinea'] ?? 'Aerolínea') ?>"
                                                     class="home-promo-logo" loading="lazy">
                                            <?php else: ?>
                                                <span class="home-promo-iata"><?= htmlspecialchars($homeDestIata !== '' ? $homeDestIata : 'VOL') ?></span>
                                            <?php endif; ?>
                                        </span>

                                        <span class="home-promo-badge"><?= $homeDestPct > 0 ? '-' . $homeDestPct . '%' : 'PROMO' ?></span>
                                    </span>

                                    <span class="home-promo-name"><?= htmlspecialchars($homeDestPromo['nombre'] ?? 'Promoción') ?></span>

                                    <span class="home-promo-route">
                                        <i class="bi bi-airplane" aria-hidden="true"></i>
                                        <?= htmlspecialchars($homeDestRuta) ?>
                                    </span>

                                    <span class="home-promo-dates">
                                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                                        Del <?= homeTabsCardFecha($homeDestPromo['fechaInicio'] ?? null) ?>
                                        al <?= homeTabsCardFecha($homeDestPromo['fechaFin'] ?? null) ?>
                                    </span>

                                    <span class="home-promo-foot">
                                        <?php if (!empty($homeDestPromo['codigo'])): ?>
                                            <span class="home-promo-code"><?= htmlspecialchars($homeDestPromo['codigo']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($homeDestPromo['nombreAerolinea'])): ?>
                                            <span class="home-promo-airline-name"><?= htmlspecialchars($homeDestPromo['nombreAerolinea']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="home-carousel-nav">
                        <div class="home-carousel-dots" data-carousel-dots></div>

                        <div class="home-carousel-arrows">
                            <button type="button"
                                    class="home-carousel-arrow home-carousel-arrow--prev"
                                    data-carousel-prev
                                    aria-label="Promociones anteriores">
                                <i class="bi bi-chevron-left" aria-hidden="true"></i>
                            </button>

                            <button type="button"
                                    class="home-carousel-arrow home-carousel-arrow--next"
                                    data-carousel-next
                                    aria-label="Promociones siguientes">
                                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Últimas novedades ──────────────────────────────── -->
        <aside class="home-destacados-panel home-destacados-novedades">
            <div class="home-destacados-head">
                <div class="home-destacados-head-text">
                    <h2 class="home-destacados-title">Últimas novedades</h2>
                    <p class="home-destacados-sub">Lo último de Volaré.</p>
                </div>
                <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="home-destacados-link">
                    Ver todas las novedades <span aria-hidden="true">→</span>
                </a>
            </div>

            <?php if (empty($homeDestNovedades)): ?>
                <div class="home-destacados-empty">
                    <i class="bi bi-megaphone" aria-hidden="true"></i>
                    <p>No hay novedades publicadas en este momento.</p>
                </div>
            <?php else: ?>
                <ul class="home-novedades-list">
                    <?php foreach ($homeDestNovedades as $homeDestNovedad):
                        [$homeDestBadge, $homeDestIcono] = homeTabsCardCategoria((string)($homeDestNovedad['categoria'] ?? 'general'));
                        $homeDestImagen = homeTabsCardImagen($homeDestNovedad['imagen'] ?? null);
                    ?>
                        <li>
                            <a href="<?= url('index.php?pagina=cliente-novedad-detalle&id=' . (int)($homeDestNovedad['idNovedad'] ?? 0)) ?>"
                               class="home-novedad-item">
                                <span class="home-novedad-thumb">
                                    <?php if ($homeDestImagen !== ''): ?>
                                        <img src="<?= htmlspecialchars($homeDestImagen) ?>"
                                             alt=""
                                             loading="lazy">
                                    <?php else: ?>
                                        <i class="bi <?= htmlspecialchars($homeDestIcono) ?>" aria-hidden="true"></i>
                                    <?php endif; ?>
                                </span>

                                <span class="home-novedad-body">
                                    <span class="home-novedad-meta">
                                        <span class="badge <?= $homeDestBadge ?> text-capitalize"><?= htmlspecialchars($homeDestNovedad['categoria'] ?? 'general') ?></span>
                                        <span class="home-novedad-fecha"><?= homeTabsCardFecha($homeDestNovedad['fechaPublicacion'] ?? null) ?></span>
                                    </span>
                                    <span class="home-novedad-title"><?= htmlspecialchars($homeDestNovedad['titulo'] ?? 'Novedad') ?></span>
                                    <?php if (!empty($homeDestNovedad['subtitulo'])): ?>
                                        <span class="home-novedad-sub"><?= htmlspecialchars($homeDestNovedad['subtitulo']) ?></span>
                                    <?php endif; ?>
                                </span>

                                <i class="bi bi-chevron-right home-novedad-chevron" aria-hidden="true"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </aside>
    </div>

    <!-- ── Beneficios ─────────────────────────────────────────── -->
    <div class="home-beneficios">
        <div class="home-beneficio">
            <span class="home-beneficio-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
            <span class="home-beneficio-text">
                <strong>Pagos seguros</strong>
                <span>Tus datos protegidos</span>
            </span>
        </div>

        <div class="home-beneficio">
            <span class="home-beneficio-icon"><i class="bi bi-headset" aria-hidden="true"></i></span>
            <span class="home-beneficio-text">
                <strong>Atención 24/7</strong>
                <span>Estamos para ayudarte</span>
            </span>
        </div>

        <div class="home-beneficio">
            <span class="home-beneficio-icon"><i class="bi bi-airplane" aria-hidden="true"></i></span>
            <span class="home-beneficio-text">
                <strong>Cambios y cancelaciones</strong>
                <span>Gestioná tu reserva con facilidad</span>
            </span>
        </div>

        <div class="home-beneficio">
            <span class="home-beneficio-icon"><i class="bi bi-gift" aria-hidden="true"></i></span>
            <span class="home-beneficio-text">
                <strong>Programa de beneficios</strong>
                <span>Más viajes, más puntos</span>
            </span>
        </div>
    </div>
</section>
