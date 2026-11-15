<?php
/**
 * Auth: manejo de la sesión del usuario y de sus permisos (RBAC).
 * Guarda en $_SESSION['u'] el id, nombre y rol (admin | vendedor | proveedor).
 */
class Auth
{
    public static function iniciarSesion(): void
    {
        $c = require __DIR__ . '/../../config/config.php';
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'lifetime' => 0, 'path' => '/', 'httponly' => true,      // la cookie no es legible por JavaScript
            'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']),
        ]);
        session_start();
        // Expiración por inactividad
        if (isset($_SESSION['t']) && time() - $_SESSION['t'] > $c['sesion_minutos'] * 60) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['t'] = time();
    }

    public static function entrar(array $u): void
    {
        session_regenerate_id(true);   // evita el secuestro de sesión
        $_SESSION['u'] = ['id' => (int)$u['id'], 'nombre' => $u['nombre'], 'rol' => strtolower($u['rol']), 'proveedor_id' => isset($u['proveedor_id']) ? (int)$u['proveedor_id'] : null];
    }

    public static function salir(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function user(): ?array { return $_SESSION['u'] ?? null; }
    public static function id(): int      { return $_SESSION['u']['id'] ?? 0; }
    public static function rol(): string  { return $_SESSION['u']['rol'] ?? ''; }
}
