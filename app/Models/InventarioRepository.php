<?php
/** InventarioRepository: kardex (movimientos de inventario) y ajustes por pérdida o daño. */
class InventarioRepository
{
    /** Registra un movimiento: cantidad positiva suma, negativa resta. */
    public static function kardex(int $producto, string $tipo, float $cantidad, int $referencia): void
    {
        Database::pdo()->prepare('INSERT INTO movimientos_inventario (producto_id, tipo, cantidad, referencia_id, fecha_hora) VALUES (?,?,?,?,NOW())')
            ->execute([$producto, $tipo, $cantidad, $referencia]);
    }

    public static function crearAjuste(int $producto, int $usuario, float $cant, string $motivo, float $costo, ?string $nota): int
    {
        $p = Database::pdo();
        $p->prepare('INSERT INTO ajustes_inventario (producto_id, usuario_id, fecha_hora, cantidad, motivo, costo_total, nota) VALUES (?,?,NOW(),?,?,?,?)')
          ->execute([$producto, $usuario, $cant, $motivo, $costo, $nota]);
        return (int)$p->lastInsertId();
    }

    public static function ajustesRecientes(int $limite = 30): array
    {
        return Database::pdo()->query('SELECT a.fecha_hora, p.nombre AS producto, p.unidad, a.cantidad, a.motivo, a.costo_total, a.nota
            FROM ajustes_inventario a JOIN productos p ON p.id = a.producto_id ORDER BY a.id DESC LIMIT ' . (int)$limite)->fetchAll();
    }
}
