<?php
/** ProductoRepository: consultas SQL de productos y categorías. */
class ProductoRepository
{
    public static function todos(bool $soloActivos = true): array
    {
        $sql = 'SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id'
             . ($soloActivos ? ' WHERE p.activo = 1' : '') . ' ORDER BY p.nombre';
        return Database::pdo()->query($sql)->fetchAll();
    }

    public static function buscar(int $id): ?array
    {
        $s = Database::pdo()->prepare('SELECT p.*, c.nombre AS categoria FROM productos p JOIN categorias c ON c.id = p.categoria_id WHERE p.id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Lee el producto y BLOQUEA su fila hasta terminar la transacción (evita ventas simultáneas sobre el mismo stock). */
    public static function bloquear(int $id): ?array
    {
        $s = Database::pdo()->prepare('SELECT * FROM productos WHERE id = ? AND activo = 1 FOR UPDATE');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Devuelve el id de la categoría; si no existe, la crea. */
    public static function categoriaId(string $nombre): int
    {
        $p = Database::pdo();
        $s = $p->prepare('SELECT id FROM categorias WHERE nombre = ?');
        $s->execute([$nombre]);
        $id = $s->fetchColumn();
        if ($id) return (int)$id;
        $p->prepare('INSERT INTO categorias (nombre) VALUES (?)')->execute([$nombre]);
        return (int)$p->lastInsertId();
    }

    /** Crea (si $id es null) o actualiza un producto. Devuelve su id. */
    public static function guardar(?int $id, array $d): int
    {
        $p = Database::pdo();
        if ($id) {
            $sql = 'UPDATE productos SET categoria_id=?, nombre=?, unidad=?, precio_compra=?, precio_venta=?, stock_minimo=?'
                 . ($d['imagen'] ? ', imagen_ruta=?' : '') . ' WHERE id=?';
            $args = [$d['categoria_id'], $d['nombre'], $d['unidad'], $d['precio_compra'], $d['precio_venta'], $d['stock_minimo']];
            if ($d['imagen']) $args[] = $d['imagen'];
            $args[] = $id;
            $p->prepare($sql)->execute($args);
            return $id;
        }
        $p->prepare('INSERT INTO productos (categoria_id, nombre, unidad, precio_compra, precio_venta, stock_actual, stock_minimo, imagen_ruta) VALUES (?,?,?,?,?,?,?,?)')
          ->execute([$d['categoria_id'], $d['nombre'], $d['unidad'], $d['precio_compra'], $d['precio_venta'], $d['stock_inicial'], $d['stock_minimo'], $d['imagen']]);
        return (int)$p->lastInsertId();
    }

    /** Suma (o resta si es negativo) al stock de forma atómica. */
    public static function sumarStock(int $id, float $delta): void
    {
        Database::pdo()->prepare('UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?')->execute([$delta, $id]);
    }

    public static function actualizarCompra(int $id, float $precio): void
    {
        Database::pdo()->prepare('UPDATE productos SET precio_compra = ? WHERE id = ?')->execute([$precio, $id]);
    }

    /** Eliminación lógica: se conserva el historial de ventas y entradas. */
    public static function desactivar(int $id): void
    {
        Database::pdo()->prepare('UPDATE productos SET activo = 0 WHERE id = ?')->execute([$id]);
    }
}
