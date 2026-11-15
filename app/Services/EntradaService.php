<?php
/** EntradaService: registra la llegada de mercancía (suma stock y actualiza el precio de compra). */
class EntradaService
{
    public static function registrar(string $proveedor, string $fecha, int $productoId, float $cant, float $precio, ?string $obs): int
    {
        $proveedorId = (int)$proveedor;
        if ($proveedorId < 1 || !ProveedorRepository::activo($proveedorId)) throw new RuntimeException('Selecciona un proveedor registrado y activo.');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) throw new RuntimeException('Fecha no válida.');
        if ($cant <= 0)                                   throw new RuntimeException('La cantidad debe ser mayor que cero.');
        if ($precio < 0)                                  throw new RuntimeException('El precio no puede ser negativo.');

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            if (!ProductoRepository::bloquear($productoId)) throw new RuntimeException('Producto no encontrado.');
            $prov  = $proveedorId;
            $total = round($cant * $precio, 2);
            $id    = EntradaRepository::crear($prov, Auth::id(), $fecha, $total, $obs ?: null);
            $lote  = 'L' . date('ymd', strtotime($fecha)) . '-' . $id;       // lote para la trazabilidad
            EntradaRepository::detalle($id, $productoId, $cant, $precio, $lote, $total);
            ProductoRepository::sumarStock($productoId, $cant);
            ProductoRepository::actualizarCompra($productoId, $precio);
            InventarioRepository::kardex($productoId, 'ENTRADA', $cant, $id);
            Auditoria::log('ENTRADA', 'entradas', $id);
            $pdo->commit();
            return $id;
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
    }
}
