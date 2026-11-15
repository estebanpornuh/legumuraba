<?php
/** AuthService: reglas de negocio de inicio de sesión y creación de usuarios. */
class AuthService
{
    /** Intenta iniciar sesión. Devuelve null si todo salió bien o un mensaje de error. */
    public static function login(string $email, string $clave): ?string
    {
        $u = UsuarioRepository::porEmail(mb_strtolower(trim($email)));
        if (!$u || !$u['activo']) return 'Correo o contraseña incorrectos.';
        if ($u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time())
            return 'Cuenta bloqueada temporalmente por demasiados intentos. Intenta en 15 minutos.';
        if (!password_verify($clave, $u['password_hash'])) {
            UsuarioRepository::falloLogin((int)$u['id']);
            Auditoria::log('LOGIN_FALLIDO', 'usuarios', (int)$u['id'], (int)$u['id']);
            return 'Correo o contraseña incorrectos.';     // mensaje genérico: no revela si el correo existe
        }
        // Si el algoritmo de hash mejoró desde que se guardó la clave, se actualiza
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT))
            UsuarioRepository::actualizarHash((int)$u['id'], password_hash($clave, PASSWORD_DEFAULT));
        UsuarioRepository::exitoLogin((int)$u['id']);
        Auth::entrar($u);
        Auditoria::log('LOGIN', 'usuarios', (int)$u['id']);
        return null;
    }

    /** Crea un usuario validando los datos. Lanza RuntimeException si algo está mal. */
    public static function crearUsuario(string $rol, string $nombre, string $email, string $clave, ?int $proveedorId = null): int
    {
        $nombre = trim($nombre);
        $email  = mb_strtolower(trim($email));
        if ($nombre === '')                                   throw new RuntimeException('Escribe el nombre.');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))       throw new RuntimeException('El correo no es válido.');
        if (strlen($clave) < 8)                               throw new RuntimeException('La contraseña debe tener al menos 8 caracteres.');
        if (UsuarioRepository::porEmail($email))              throw new RuntimeException('Ese correo ya está registrado.');
        // password_hash: hashing seguro con sal automática (RNF-05)
        if ($rol === 'PROVEEDOR' && (!$proveedorId || !ProveedorRepository::activo($proveedorId))) throw new RuntimeException('Selecciona un proveedor activo.');
        if ($rol !== 'PROVEEDOR') $proveedorId = null;
        $id = UsuarioRepository::crear($rol, $nombre, $email, password_hash($clave, PASSWORD_DEFAULT), $proveedorId);
        Auditoria::log('CREAR_USUARIO', 'usuarios', $id);
        return $id;
    }
}
