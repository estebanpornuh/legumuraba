<?php
/** Consultas de proveedores y consulta aislada del portal del proveedor. */
class ProveedorRepository
{
    public static function listar(): array
    {
        return Database::pdo()->query('SELECT p.*, (SELECT COUNT(*) FROM usuarios u WHERE u.proveedor_id = p.id AND u.activo = 1) AS cuentas_activas FROM proveedores p ORDER BY p.nombre')->fetchAll();
    }

    public static function crear(string $nombre, string $telefono, string $municipio, string $tipo): int
    {
        $s = Database::pdo()->prepare('INSERT INTO proveedores (nombre, telefono, municipio, tipo) VALUES (?, ?, ?, ?)');
        $s->execute([$nombre, $telefono ?: null, $municipio ?: null, $tipo]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function activo(int $id): bool
    {
        $s = Database::pdo()->prepare('SELECT COUNT(*) FROM proveedores WHERE id = ? AND activo = 1');
        $s->execute([$id]);
        return (int)$s->fetchColumn() === 1;
    }

    public static function porId(int $id): ?array
    {
        $s = Database::pdo()->prepare('SELECT * FROM proveedores WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public static function tieneCuenta(int $id): bool
    {
        $s = Database::pdo()->prepare('SELECT COUNT(*) FROM usuarios WHERE proveedor_id = ?');
        $s->execute([$id]);
        return (int)$s->fetchColumn() > 0;
    }

    public static function misEntregas(int $proveedorId): array
    {
        $s = Database::pdo()->prepare('SELECT e.id, e.fecha, e.observacion, p.nombre AS producto, p.unidad,
                d.cantidad, d.lote
            FROM entradas e JOIN detalle_entrada d ON d.entrada_id = e.id
            JOIN productos p ON p.id = d.producto_id
            WHERE e.proveedor_id = ? ORDER BY e.fecha DESC, e.id DESC');
        $s->execute([$proveedorId]);
        return $s->fetchAll();
    }
}
