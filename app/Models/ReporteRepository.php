<?php
/** ReporteRepository: consultas agregadas para los reportes (usan los índices de fecha). */
class ReporteRepository
{
    private static function uno(string $sql, array $args)
    {
        $s = Database::pdo()->prepare($sql);
        $s->execute($args);
        return $s->fetch();
    }

    private static function todos(string $sql, array $args): array
    {
        $s = Database::pdo()->prepare($sql);
        $s->execute($args);
        return $s->fetchAll();
    }

    public static function ventas(string $desde): array
    {
        return self::uno("SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS total FROM ventas WHERE estado='ACTIVA' AND fecha_hora >= ?", [$desde]);
    }

    /** Ganancia = subtotal vendido - costo de lo vendido (costo guardado al momento de la venta). */
    public static function ganancia(string $desde): float
    {
        $r = self::uno("SELECT COALESCE(SUM(d.subtotal - d.costo_unitario * d.cantidad),0) AS g FROM detalle_venta d
            JOIN ventas v ON v.id = d.venta_id WHERE v.estado='ACTIVA' AND v.fecha_hora >= ?", [$desde]);
        return (float)$r['g'];
    }

    public static function perdidas(string $desde): float
    {
        $r = self::uno("SELECT COALESCE(SUM(costo_total),0) AS p FROM ajustes_inventario WHERE motivo IN ('PERDIDA','DANO','VENCIDO') AND fecha_hora >= ?", [$desde]);
        return (float)$r['p'];
    }

    public static function porPago(string $desde): array
    {
        return self::todos("SELECT metodo_pago, COALESCE(SUM(total),0) AS total FROM ventas WHERE estado='ACTIVA' AND fecha_hora >= ? GROUP BY metodo_pago", [$desde]);
    }

    public static function porEmpleado(string $desde): array
    {
        return self::todos("SELECT u.nombre, COALESCE(SUM(v.total),0) AS total FROM ventas v JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.estado='ACTIVA' AND v.fecha_hora >= ? GROUP BY u.id, u.nombre ORDER BY total DESC", [$desde]);
    }

    /** Ranking de productos por cantidad vendida. $orden = 'DESC' (más vendidos) o 'ASC' (menos vendidos). */
    public static function ranking(string $desde, string $orden): array
    {
        $orden = $orden === 'ASC' ? 'ASC' : 'DESC';
        $having = $orden === 'DESC' ? 'HAVING q > 0' : '';
        return self::todos("SELECT p.nombre, p.unidad, COALESCE(SUM(d.cantidad),0) AS q FROM productos p
            LEFT JOIN (detalle_venta d JOIN ventas v ON v.id = d.venta_id AND v.estado='ACTIVA' AND v.fecha_hora >= ?) ON d.producto_id = p.id
            WHERE p.activo = 1 GROUP BY p.id, p.nombre, p.unidad $having ORDER BY q $orden LIMIT 5", [$desde]);
    }

    /** Detalle de ventas del periodo (para exportar a CSV). */
    public static function detalleVentas(string $desde): array
    {
        return self::todos("SELECT v.id AS ticket, v.fecha_hora, u.nombre AS empleado, p.nombre AS producto, d.cantidad, d.precio_unitario, d.subtotal, v.metodo_pago
            FROM detalle_venta d JOIN ventas v ON v.id = d.venta_id JOIN productos p ON p.id = d.producto_id JOIN usuarios u ON u.id = v.usuario_id
            WHERE v.estado='ACTIVA' AND v.fecha_hora >= ? ORDER BY v.id", [$desde]);
    }
}
