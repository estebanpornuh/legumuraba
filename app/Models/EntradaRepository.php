<?php
/** EntradaRepository: entradas de mercancía y proveedores. */
class EntradaRepository
{
    /** Devuelve el id del proveedor por nombre; si no existe, lo crea. */
    public static function proveedorId(string $nombre): int
    {
        $p = Database::pdo();
        $s = $p->prepare('SELECT id FROM proveedores WHERE nombre = ?');
        $s->execute([$nombre]);
        $id = $s->fetchColumn();
        if ($id) return (int)$id;
        $p->prepare('INSERT INTO proveedores (nombre) VALUES (?)')->execute([$nombre]);
        return (int)$p->lastInsertId();
    }

    public static function crear(int $proveedor, int $usuario, string $fecha, float $total, ?string $obs): int
    {
        $p = Database::pdo();
        $p->prepare('INSERT INTO entradas (proveedor_id, usuario_id, fecha, total, observacion) VALUES (?,?,?,?,?)')
          ->execute([$proveedor, $usuario, $fecha, $total, $obs]);
        return (int)$p->lastInsertId();
    }

    public static function detalle(int $entrada, int $producto, float $cant, float $precio, string $lote, float $subtotal): void
    {
        Database::pdo()->prepare('INSERT INTO detalle_entrada (entrada_id, producto_id, cantidad, precio_compra, lote, subtotal) VALUES (?,?,?,?,?,?)')
            ->execute([$entrada, $producto, $cant, $precio, $lote, $subtotal]);
    }

    public static function recientes(int $limite = 50): array
    {
        return Database::pdo()->query('SELECT e.id, e.fecha, pr.nombre AS proveedor, p.nombre AS producto, p.unidad, d.cantidad, d.precio_compra, d.lote, d.subtotal
            FROM entradas e JOIN proveedores pr ON pr.id = e.proveedor_id JOIN detalle_entrada d ON d.entrada_id = e.id JOIN productos p ON p.id = d.producto_id
            ORDER BY e.id DESC LIMIT ' . (int)$limite)->fetchAll();
    }
}
