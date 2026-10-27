<?php
// Breadcrumb para vistas públicas/cliente.
// Uso: $migas = [['label' => 'Mis reservas', 'href' => 'index.php?pagina=mis-reservas'], ['label' => 'Detalle']];
// El primer nivel ("Inicio") se agrega solo según el rol; el último ítem es la página actual.
$rolMigas = $_SESSION['usuario']['rol'] ?? '';
$inicioMigas = [
    'cliente' => 'index.php?pagina=cliente',
    'ceo'     => 'index.php?pagina=ceo',
    'admin'   => 'index.php?pagina=aerolinea&seccion=listado',
][$rolMigas] ?? 'index.php?pagina=inicio';
$migas = array_merge([['label' => 'Inicio', 'href' => $inicioMigas]], $migas ?? []);
$ultimaMiga = count($migas) - 1;
?>
<nav aria-label="breadcrumb" class="migas-cliente">
    <ol class="breadcrumb m-0">
        <?php foreach ($migas as $i => $miga): ?>
            <?php if ($i === $ultimaMiga || empty($miga['href'])): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($miga['label']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item"><a href="<?= url($miga['href']) ?>"><?= htmlspecialchars($miga['label']) ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<style>
    .migas-cliente {
        display: inline-block;
        margin: 0.75rem 0;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.9);
        font-size: 0.85rem;
    }
    .migas-cliente .breadcrumb-item a { color: #0b4f9c; text-decoration: none; }
    .migas-cliente .breadcrumb-item a:hover { text-decoration: underline; }
    .migas-cliente .breadcrumb-item.active { color: #333; }
</style>
