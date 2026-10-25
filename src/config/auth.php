<?php

function estaLogueado(): bool {
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']);
}

function usuarioActual(): ?array {
    return estaLogueado() ? $_SESSION['usuario'] : null;
}

function rolActual(): ?string {
    $usuario = usuarioActual();
    return isset($usuario['rol']) && is_string($usuario['rol'])
        ? $usuario['rol']
        : null;
}

function aerolineaCEO(): ?int {
    $usuario = usuarioActual();
    return isset($usuario['idAerolinea']) && is_numeric($usuario['idAerolinea'])
        ? (int)$usuario['idAerolinea']
        : null;
}

// GUARDAS 

function requireAuth(): void {
    if (!estaLogueado()) {
        flash_set('error', 'Debes iniciar sesión para acceder a esta página.');
        redirect('index.php?pagina=login');
    }
}

function requireRol(string ...$roles): void {
    requireAuth();

    if (!in_array(rolActual(), $roles, true)) {
        flash_set('error', 'No tienes permisos para acceder a esta página.');
        redirect('index.php?pagina=inicio');
    }
}

function soloInvitados(): void {
    if (estaLogueado()) {
        redirect('index.php?pagina=inicio');
    }
}





?>