<?php
/** UsuarioController: el administrador crea y desactiva usuarios (RF-01). */
class UsuarioController
{
    public static function index(): void
    {
        View::render('usuarios', ['usuarios' => UsuarioRepository::listar()]);
    }

    public static function crear(): void
    {
        $rol = in_array(($_POST['rol'] ?? ''), ['ADMIN', 'VENDEDOR'], true) ? $_POST['rol'] : 'VENDEDOR';
        try {
            AuthService::crearUsuario($rol, $_POST['nombre'] ?? '', $_POST['email'] ?? '', $_POST['clave'] ?? '');
            flash('ok', 'Usuario creado.');
        } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
        redirigir('usuarios');
    }

    public static function estado(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id === Auth::id()) { flash('error', 'No puedes desactivar tu propia cuenta.'); redirigir('usuarios'); }
        UsuarioRepository::cambiarActivo($id, (int)($_POST['activo'] ?? 0));
        Auditoria::log('CAMBIAR_ESTADO_USUARIO', 'usuarios', $id);
        redirigir('usuarios');
    }
}
