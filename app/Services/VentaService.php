<?php
/**
 * VentaService: registra una venta completa dentro de UNA transacción (todo o nada).
 * Nunca confía en los precios ni totales que envía el navegador: los recalcula con la base de datos.
 */
class VentaService
{
    private const PAGOS = ['EFECTIVO', 'NEQUI', 'TRANSFERENCIA'];

    /**
     * @param array $lineas  cada línea: ['id' => producto, 'modo' => 'q'|'v'|'g', 'valor' => número]
     *                       q = cantidad, v = valor en pesos, g = gramos (solo productos en kg)
     * @return array ['ticket' => número de venta, 'total' => total cobrado]
     */
    public static function registrar(array $lineas, string $pago): array
    {
        if (!in_array($pago, self::PAGOS, true)) throw new RuntimeException('Método de pago no válido.');
        if (!$lineas)                            throw new RuntimeException('La cuenta está vacía.');
        usort($lineas, fn($a, $b) => (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0));   // orden fijo: evita bloqueos cruzados

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $ventaId = VentaRepository::crear(Auth::id(), $pago);
            $total = 0.0;
            foreach ($lineas as $l) {
                $p = ProductoRepository::bloquear((int)($l['id'] ?? 0));       // bloquea la fila del producto
                if (!$p) throw new RuntimeException('Producto no encontrado.');
                $modo  = $l['modo'] ?? 'q';
                $valor = (float)($l['valor'] ?? 0);
                $precio = (float)$p['precio_venta'];
                if ($valor <= 0) throw new RuntimeException('Revisa las cantidades de la cuenta.');

                if ($modo === 'v') {                                            // por valor: cantidad = valor / precio
                    if ($precio <= 0) throw new RuntimeException('El producto no tiene precio de venta.');
                    $cant = round($valor / $precio, 3); $sub = round($valor, 2); $mod = 'VALOR';
                } elseif ($modo === 'g') {                                      // por peso: gramos / 1000
                    if ($p['unidad'] !== 'kg') throw new RuntimeException('El peso en gramos solo aplica a productos en kg.');
                    $cant = round($valor / 1000, 3); $sub = round($cant * $precio, 2); $mod = 'PESO';
                } else {                                                        // por cantidad
                    $cant = round($valor, 3); $sub = round($cant * $precio, 2); $mod = 'CANTIDAD';
                }
                if ($cant <= 0) throw new RuntimeException('Cantidad no válida.');
                if ($cant > (float)$p['stock_actual'] + 1e-9)
                    throw new RuntimeException("Solo hay {$p['stock_actual']} {$p['unidad']} de {$p['nombre']}.");

                VentaRepository::agregarDetalle($ventaId, (int)$p['id'], $mod, $cant, $precio, (float)$p['precio_compra'], $sub);
                ProductoRepository::sumarStock((int)$p['id'], -$cant);
                InventarioRepository::kardex((int)$p['id'], 'VENTA', -$cant, $ventaId);
                $total += $sub;
            }
            VentaRepository::actualizarTotal($ventaId, $total);
            Auditoria::log('VENTA', 'ventas', $ventaId);
            $pdo->commit();
            return ['ticket' => $ventaId, 'total' => $total];
        } catch (Throwable $e) {
            $pdo->rollBack();       // si algo falla no queda NADA a medias
            throw $e;
        }
    }
}
