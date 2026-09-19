<?php
$alertType    = $alertType    ?? null;
$alertTitle   = $alertTitle   ?? null;
$alertMessage = $alertMessage ?? null;

if ($alertType === null && $alertMessage === null) {
    $successMsg = flash_get('success');
    $errorMsg   = flash_get('error');

    if ($successMsg) {
        $alertType    = 'success';
        $alertTitle   = '¡Exito!';
        $alertMessage = $successMsg;
    } elseif ($errorMsg) {
        $alertType    = 'danger';
        $alertTitle   = 'Error...';
        $alertMessage = $errorMsg;
    }
}

if (!$alertType || !$alertMessage) {
    return;
}

$titulosPorDefecto = [
    'success' => '¡Exito!',
    'danger'  => 'Error...',
    'warning' => 'Atención',
    'info'    => 'Info',
];

$alertTitle = $alertTitle ?: ($titulosPorDefecto[$alertType] ?? '');
?>

<div class="container-xxl mt-3 pe-5 pe-md-0 ps-md-5  position-absolute d-flex flex-column align-items-center ms-3" style="width: 100%; max-width: 1200px;"  >
    <div class="alert ms-md-5 alert-<?= htmlspecialchars($alertType) ?> alert-dismissible fade show" role="alert" style="width: 100%; max-width: 600px;">
        <?php if ($alertTitle): ?>
            <h4 class="alert-heading"><?= htmlspecialchars($alertTitle) ?></h4>
        <?php endif; ?>
        <p class="mb-0"><?= htmlspecialchars($alertMessage) ?></p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
</div>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert-dismissible').forEach(el => {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);
</script>