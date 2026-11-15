<?php
/** AjusteService: registra pérdidas o daños (resta stock y calcula el costo de la pérdida). */
class AjusteService
{
    private const MOTIVOS = ['PERDIDA', 'DANO', 'VENCIDO', 'CORRECCION'];

    public static function registrar(int $productoId, float $cant, string $motivo, ?string $nota): int
    {
        if (!in_array($motivo, self::MOTIVOS, true)) throw new RuntimeException('Motivo no válido.');
        if ($cant <= 0)                              throw new RuntimeException('La cantidad debe ser mayor que cero.');

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $p = ProductoRepository::bloquear($productoId);
            if (!$p) throw new RuntimeException('Producto no encontrado.');
            if ($cant > (float)$p['stock_actual'] + 1e-9) throw new RuntimeException("Solo hay {$p['stock_actual']} {$p['unidad']} en existencia.");
            $id = InventarioRepository::crearAjuste($productoId, Auth::id(), $cant, $motivo, round($cant * (float)$p['precio_compra'], 2), $nota ?: null);
            ProductoRepository::sumarStock($productoId, -$cant);
            InventarioRepository::kardex($productoId, 'AJUSTE', -$cant, $id);
            Auditoria::log('AJUSTE', 'ajustes_inventario', $id);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    }
}
