<?php
/**
 * Cards de Promociones y Novedades para las tabs del home.
 *
 * El partial se incluye una vez desde searchMobile.php y otra desde
 * searchDesktop.php. Los datos y helpers viven en homeCardsData.php.
 */

require_once __DIR__ . '/homeCardsData.php';

$homeTabsPrefijo    = $homeTabsPrefijo ?? 'home';
$homeTabsData       = homeTabsCardsData(6);
$homeTabsPromos     = $homeTabsData['promociones'];
$homeTabsNovedades  = $homeTabsData['novedades'];
$homeTabsVariantes  = ['a', 'b', 'c'];
?>

<!-- PANEL PROMOCIONES -->
<div class="home-tabs-panel d-none" data-home-tab="promociones"
     id="<?= $homeTabsPrefijo ?>PanelPromociones"
     role="tabpanel" aria-labelledby="<?= $homeTabsPrefijo ?>TabPromociones">
    <div class="home-tabs-panel-head">
        <a href="<?= url('index.php?pagina=cliente-novedades&tab=promociones') ?>" class="home-tabs-link">
            Ver todas las promociones <span aria-hidden="true">→</span>
        </a>
    </div>

    <?php if (empty($homeTabsPromos)): ?>
        <div class="home-tabs-empty">
            <i class="bi bi-tag" aria-hidden="true"></i>
            <p>No hay promociones disponibles en este momento.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($homeTabsPromos as $homeTabsIdx => $homeTabsPromo):
                $homeTabsPct      = (int)round((float)($homeTabsPromo['descuentoPorcentaje'] ?? 0));
                $homeTabsVariante = $homeTabsVariantes[$homeTabsIdx % count($homeTabsVariantes)];
            ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <article class="home-card home-card-promo home-card-promo--<?= $homeTabsVariante ?> h-100">
                        <div class="home-card-promo-head">
                            <span class="home-card-promo-pct"><?= $homeTabsPct > 0 ? $homeTabsPct . '%' : 'PROMO' ?></span>
                            <span class="home-card-promo-off">OFF</span>
                        </div>

                        <div class="home-card-body">
                            <?php if (!empty($homeTabsPromo['codigo'])): ?>
                                <span class="home-card-code"><?= htmlspecialchars($homeTabsPromo['codigo']) ?></span>
                            <?php endif; ?>

                            <h2 class="home-card-title"><?= htmlspecialchars($homeTabsPromo['nombre'] ?? 'Promoción') ?></h2>

                            <?php if (!empty($homeTabsPromo['nombreAerolinea'])): ?>
                                <p class="home-card-meta">
                                    Aerolínea: <strong><?= htmlspecialchars($homeTabsPromo['nombreAerolinea']) ?></strong>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($homeTabsPromo['descripcion'])): ?>
                                <p class="home-card-desc"><?= htmlspecialchars($homeTabsPromo['descripcion']) ?></p>
                            <?php endif; ?>

                            <p class="home-card-date">
                                <i class="bi bi-calendar3" aria-hidden="true"></i>
                                <span>
                                    Del <strong><?= homeTabsCardFecha($homeTabsPromo['fechaInicio'] ?? null) ?></strong>
                                    al <strong><?= homeTabsCardFecha($homeTabsPromo['fechaFin'] ?? null) ?></strong>
                                </span>
                            </p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- PANEL NOVEDADES -->
<div class="home-tabs-panel d-none" data-home-tab="novedades"
     id="<?= $homeTabsPrefijo ?>PanelNovedades"
     role="tabpanel" aria-labelledby="<?= $homeTabsPrefijo ?>TabNovedades">
    <div class="home-tabs-panel-head">
        <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="home-tabs-link">
            Ver todas las novedades <span aria-hidden="true">→</span>
        </a>
    </div>

    <?php if (empty($homeTabsNovedades)): ?>
        <div class="home-tabs-empty">
            <i class="bi bi-megaphone" aria-hidden="true"></i>
            <p>No hay novedades disponibles en este momento.</p>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($homeTabsNovedades as $homeTabsNovedad):
                [$homeTabsBadge, $homeTabsIcono] = homeTabsCardCategoria((string)($homeTabsNovedad['categoria'] ?? 'general'));
                $homeTabsImagen = homeTabsCardImagen($homeTabsNovedad['imagen'] ?? null);
            ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="<?= url('index.php?pagina=cliente-novedad-detalle&id=' . (int)($homeTabsNovedad['idNovedad'] ?? 0)) ?>"
                       class="home-card home-card-novedad h-100 text-decoration-none"
                       aria-label="Leer la novedad: <?= htmlspecialchars($homeTabsNovedad['titulo'] ?? 'Novedad') ?>">
                        <?php if ($homeTabsImagen !== ''): ?>
                            <img src="<?= htmlspecialchars($homeTabsImagen) ?>"
                                 alt="Imagen de <?= htmlspecialchars($homeTabsNovedad['titulo'] ?? 'Novedad') ?>"
                                 class="home-card-img" loading="lazy">
                        <?php else: ?>
                            <div class="home-card-img home-card-img--placeholder">
                                <i class="bi <?= htmlspecialchars($homeTabsIcono) ?>" aria-hidden="true"></i>
                            </div>
                        <?php endif; ?>

                        <div class="home-card-body">
                            <div class="home-card-topline">
                                <span class="badge <?= $homeTabsBadge ?> text-capitalize"><?= htmlspecialchars($homeTabsNovedad['categoria'] ?? 'general') ?></span>
                                <span class="home-card-fecha"><?= homeTabsCardFecha($homeTabsNovedad['fechaPublicacion'] ?? null) ?></span>
                            </div>

                            <h2 class="home-card-title"><?= htmlspecialchars($homeTabsNovedad['titulo'] ?? 'Novedad') ?></h2>

                            <?php if (!empty($homeTabsNovedad['subtitulo'])): ?>
                                <p class="home-card-desc"><?= htmlspecialchars($homeTabsNovedad['subtitulo']) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
