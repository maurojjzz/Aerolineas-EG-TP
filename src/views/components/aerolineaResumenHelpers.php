<?php
/**
 * Helpers compartidos por el detalle de aerolínea (admin) y el home del CEO.
 * Mantienen el mismo formato de números, moneda, tendencias y badges.
 */

if (!function_exists('adminAeroNumero')) {
    function adminAeroNumero($valor): string {
        return number_format((float)$valor, 0, ',', '.');
    }
}

if (!function_exists('adminAeroMoneda')) {
    function adminAeroMoneda($valor): string {
        return '$' . number_format((float)$valor, 0, ',', '.');
    }
}

if (!function_exists('adminAeroTendencia')) {
    /**
     * Devuelve [icono, texto, color] para la tendencia vs mes anterior.
     */
    function adminAeroTendencia($actual, $anterior, bool $porcentaje = false): array {
        $actual   = (float)$actual;
        $anterior = (float)$anterior;

        if ($porcentaje) {
            $pct = $anterior > 0
                ? (int)round((($actual - $anterior) / $anterior) * 100)
                : ($actual > 0 ? 100 : 0);

            if ($pct > 0) {
                return ['flecha-up.png', '+' . $pct . '% vs mes anterior', 'text-success'];
            }
            if ($pct < 0) {
                return ['flecha-down.png', $pct . '% vs mes anterior', 'text-danger'];
            }
            return ['simbolo-igual.png', 'Sin variación vs mes anterior', 'text-muted'];
        }

        $diff = (int)round($actual - $anterior);

        if ($diff > 0) {
            return ['flecha-up.png', '+' . adminAeroNumero($diff) . ' vs mes anterior', 'text-success'];
        }
        if ($diff < 0) {
            return ['flecha-down.png', adminAeroNumero($diff) . ' vs mes anterior', 'text-danger'];
        }
        return ['simbolo-igual.png', 'Igual que el mes anterior', 'text-muted'];
    }
}

if (!function_exists('adminAeroBadgeEstadoPromo')) {
    function adminAeroBadgeEstadoPromo(string $estado): string {
        return match ($estado) {
            'Aprobada' => 'text-bg-success',
            'Denegada' => 'text-bg-danger',
            default    => 'text-bg-warning',
        };
    }
}

if (!function_exists('adminAeroBadgeVigenciaPromo')) {
    function adminAeroBadgeVigenciaPromo(array $p): string {
        if ($p['estado'] === 'Denegada') {
            return '<span class="badge text-bg-danger">Denegada</span>';
        }
        if ($p['estado'] === 'Pendiente') {
            return '<span class="badge text-bg-warning">Pendiente</span>';
        }

        $hoy = new DateTime('today');
        $fin = new DateTime($p['fechaFin']);

        if ($fin < $hoy) {
            return '<span class="badge text-bg-danger">Vencida</span>';
        }

        $diff = $hoy->diff($fin)->days;
        if ($diff <= 7) {
            return '<span class="badge text-bg-warning">Quedan ' . $diff . ' día' . ($diff === 1 ? '' : 's') . '</span>';
        }

        return '<span class="badge text-bg-success">Vigente</span>';
    }
}

if (!function_exists('adminAeroTextoAlcancePromo')) {
    function adminAeroTextoAlcancePromo(array $p): string {
        if (!empty($p['idVuelo'])) {
            $origen  = $p['codigoOrigen'] ?? '—';
            $destino = $p['codigoDestino'] ?? '—';
            return "Vuelo #{$p['idVuelo']} ($origen → $destino)";
        }
        if (!empty($p['codigoOrigen']) && !empty($p['codigoDestino'])) {
            return "Ruta {$p['codigoOrigen']} → {$p['codigoDestino']}";
        }
        return 'Aerolínea completa';
    }
}
