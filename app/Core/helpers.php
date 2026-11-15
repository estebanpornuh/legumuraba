<?php
// Funciones auxiliares usadas en toda la aplicación.

/** e(): escapa texto para mostrarlo en HTML (evita XSS). Úsala SIEMPRE al imprimir datos. */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/** url(): arma el enlace a una ruta, por ejemplo url('productos', ['editar' => 3]). */
function url(string $r, array $q = []): string { return 'index.php?' . http_build_query(['r' => $r] + $q); }

/** money(): formato de pesos colombianos. */
function money($n): string { return '$' . number_format((float)$n, 0, ',', '.'); }

/** redirigir(): envía al usuario a otra ruta y termina el script. */
function redirigir(string $r, array $q = []): void { header('Location: ' . url($r, $q)); exit; }

/** flash(): guarda un mensaje para mostrarlo en la siguiente pantalla. */
function flash(string $tipo, string $msg): void { $_SESSION['flash'] = [$tipo, $msg]; }

/** negocio(): nombre del negocio guardado en la base de datos. */
function negocio(): string
{
    static $n = null;
    if ($n === null) {
        try { $n = (string)Database::pdo()->query('SELECT nombre FROM configuracion_negocio WHERE id = 1')->fetchColumn(); }
        catch (Throwable $e) { $n = ''; }
        if ($n === '') $n = 'Legumbrería';
    }
    return $n;
}
