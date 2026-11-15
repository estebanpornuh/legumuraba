<?php
/** ReporteController: reportes visuales (RF-12 a RF-14) y exportación a CSV (RF-15). */
class ReporteController
{
    private static function periodo(): string
    {
        $p = $_GET['periodo'] ?? 'day';
        return in_array($p, ['day', 'week', 'month'], true) ? $p : 'day';
    }

    public static function ver(): void
    {
        $p = self::periodo();
        View::render('reportes', ['periodo' => $p, 'r' => ReporteService::resumen($p)]);
    }

    /** Descarga las ventas del periodo en CSV (separador ; y UTF-8 con BOM para que Excel lo abra bien). */
    public static function csv(): void
    {
        $p = self::periodo();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ventas_' . $p . '_' . date('Ymd') . '.csv"');
        echo "\xEF\xBB\xBF";
        $o = fopen('php://output', 'w');
        fputcsv($o, ['Ticket', 'Fecha y hora', 'Empleado', 'Producto', 'Cantidad', 'Precio unitario', 'Subtotal', 'Pago'], ';');
        foreach (ReporteRepository::detalleVentas(ReporteService::desde($p)) as $f) fputcsv($o, $f, ';');
        fclose($o);
    }
}
