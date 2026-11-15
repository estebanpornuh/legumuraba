<?php
/** AuthController: inicio de sesión, cierre de sesión y configuración inicial (primer administrador). */
class AuthController
{
    public static function login(): void
    {
        if (UsuarioRepository::contar() === 0) redirigir('setup');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = AuthService::login($_POST['email'] ?? '', $_POST['clave'] ?? '');
            if ($error === null) redirigir(Auth::rol() === 'proveedor' ? 'proveedor/portal' : '');
        }
        View::render('login', ['error' => $error]);
    }

    public static function logout(): void
    {
        Auditoria::log('LOGOUT', 'usuarios', Auth::id());
        Auth::salir();
        header('Location: index.php');
        exit;
    }

    /** Solo funciona mientras NO exista ningún usuario: crea el administrador y el nombre del negocio. */
    public static function setup(): void
    {
        if (UsuarioRepository::contar() > 0) redirigir('login');
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (($_POST['clave'] ?? '') !== ($_POST['clave2'] ?? '')) throw new RuntimeException('Las contraseñas no coinciden.');
                AuthService::crearUsuario('ADMIN', $_POST['nombre'] ?? '', $_POST['email'] ?? '', $_POST['clave'] ?? '');
                $n = trim($_POST['negocio'] ?? '');
                if ($n !== '') Database::pdo()->prepare('UPDATE configuracion_negocio SET nombre = ? WHERE id = 1')->execute([$n]);
                flash('ok', 'Cuenta de administrador creada. Ya puedes iniciar sesión.');
                redirigir('login');
            } catch (RuntimeException $e) { $error = $e->getMessage(); }
        }
        View::render('setup', ['error' => $error]);
    }
}
