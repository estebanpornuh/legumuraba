<?php
/** VentaRepository: ventas y su detalle. */
class VentaRepository
{
    public static function crear(int $usuario, string $pago): int
    {
        $p = Database::pdo();
        $p->prepare("INSERT INTO ventas (usuario_id, fecha_hora, metodo_pago, total, estado) VALUES (?, NOW(), ?, 0, 'ACTIVA')")->execute([$usuario, $pago]);
        return (int)$p->lastInsertId();
    }

    public static function agregarDetalle(int $venta, int $producto, string $modalidad, float $cant, float $precio, float $costo, float $subtotal): void
    {
        Database::pdo()->prepare('INSERT INTO detalle_venta (venta_id, producto_id, modalidad, cantidad, precio_unitario, costo_unitario, subtotal) VALUES (?,?,?,?,?,?,?)')
            ->execute([$venta, $producto, $modalidad, $cant, $precio, $costo, $subtotal]);
    }

    public static function actualizarTotal(int $venta, float $total): void
    {
        Database::pdo()->prepare('UPDATE ventas SET total = ? WHERE id = ?')->execute([$total, $venta]);
    }

    /** Historial: una fila por venta (ticket) con sus productos resumidos. */
    public static function historial(int $limite = 100): array
    {
        return Database::pdo()->query("SELECT v.id, v.fecha_hora, u.nombre AS empleado, v.metodo_pago, v.total,
              GROUP_CONCAT(CONCAT(p.nombre, ' x', d.cantidad) SEPARATOR ', ') AS items
            FROM ventas v JOIN usuarios u ON u.id = v.usuario_id JOIN detalle_venta d ON d.venta_id = v.id JOIN productos p ON p.id = d.producto_id
            GROUP BY v.id, u.nombre ORDER BY v.id DESC LIMIT " . (int)$limite)->fetchAll();
    }
}
