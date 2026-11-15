<?php
/** UsuarioRepository: consultas SQL de la tabla usuarios (capa de acceso a datos). */
class UsuarioRepository
{
    public static function contar(): int
    {
        return (int)Database::pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    public static function porEmail(string $email): ?array
    {
        $s = Database::pdo()->prepare('SELECT u.*, r.nombre AS rol FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.email = ?');
        $s->execute([$email]);
        return $s->fetch() ?: null;
    }

    public static function crear(string $rol, string $nombre, string $email, string $hash, ?int $proveedorId = null): int
    {
        $p = Database::pdo();
        $s = $p->prepare('INSERT INTO usuarios (rol_id, proveedor_id, nombre, email, password_hash) SELECT id, ?, ?, ?, ? FROM roles WHERE nombre = ?');
        $s->execute([$proveedorId, $nombre, $email, $hash, $rol]);
        return (int)$p->lastInsertId();
    }

    public static function listar(): array
    {
        return Database::pdo()->query('SELECT u.id, u.nombre, u.email, u.activo, r.nombre AS rol, pr.nombre AS proveedor FROM usuarios u JOIN roles r ON r.id = u.rol_id LEFT JOIN proveedores pr ON pr.id = u.proveedor_id ORDER BY u.id')->fetchAll();
    }

    public static function cambiarActivo(int $id, int $activo): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET activo = ? WHERE id = ?')->execute([$activo, $id]);
    }

    /** Suma un intento fallido; al 5.º intento bloquea la cuenta 15 minutos (RNF-09). */
    public static function falloLogin(int $id): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET intentos_fallidos = intentos_fallidos + 1,
            bloqueado_hasta = IF(intentos_fallidos >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), bloqueado_hasta) WHERE id = ?')->execute([$id]);
    }

    public static function exitoLogin(int $id): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?')->execute([$id]);
    }

    public static function actualizarHash(int $id, string $hash): void
    {
        Database::pdo()->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')->execute([$hash, $id]);
    }
}
