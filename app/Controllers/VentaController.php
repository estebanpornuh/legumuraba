<?php
/** VentaController: la caja (punto de venta), el registro de ventas (RF-06 a RF-08) y el historial. */
class VentaController
{
    public static function caja(): void
    {
        // Al navegador solo se envían los datos necesarios para vender (NUNCA el precio de compra)
        $productos = array_map(fn($p) => [
            'id' => (int)$p['id'], 'nombre' => $p['nombre'], 'unidad' => $p['unidad'],
            'precio_venta' => (float)$p['precio_venta'], 'stock' => (float)$p['stock_actual'], 'imagen' => $p['imagen_ruta'],
        ], ProductoRepository::todos());
        View::render('caja', ['productos' => $productos]);
    }

    /** Recibe la cuenta en JSON desde caja.js y responde en JSON. */
    public static function guardar(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $in = json_decode(file_get_contents('php://input'), true);
            if (!is_array($in)) throw new RuntimeException('Datos no válidos.');
            $r = VentaService::registrar($in['lineas'] ?? [], $in['pago'] ?? '');
            echo json_encode(['ok' => true] + $r);
        } catch (RuntimeException $e) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    public static function historial(): void
    {
        View::render('historial', ['ventas' => VentaRepository::historial()]);
    }
}
