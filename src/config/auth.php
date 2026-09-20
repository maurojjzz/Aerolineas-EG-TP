<?php

function estaLogueado(): bool {
    return isset($_SESSION['usuario']);
}

function usuarioActual(): ?array {
    return $_SESSION['usuario'] ?? null;
}

function rolActual(): ?string {
    return $_SESSION['usuario']['rol'] ?? null;
}

function aerolineaCEO(): ?int {
    return $_SESSION['usuario']['idUsuario'] ?? null;
}

// GUARDAS 

function requireAuth(): void {
    if (!estaLogueado()) {
        flash_set('error', 'Debes iniciar sesión para acceder a esta página.');
        redirect('index.php?pagina=login');
    }
}

// en el routes o controller se usa como middleware; requireRol('admin', 'ceo') o requireRol('cliente')  etc etc y todas esas variantes

function requireRol(string ...$roles):void {
    
    requireAuth();

    if(!in_array(rolActual(), $roles, true)) {
        flash_set('error', 'No tienes permisos para acceder a esta página.');
        redirect('index.php?pagina=login');

    }
}

function soloInvitados(): void {
    if (estaLogueado()) {
        redirect('index.php?pagina=inicio');
    }
}





?>