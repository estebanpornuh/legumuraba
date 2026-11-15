<?php
/** ReporteService: calcula el periodo (día, semana o mes) y reúne los datos del reporte. */
class ReporteService
{
    /** Fecha de inicio del periodo. La semana empieza el lunes. */
    public static function desde(string $periodo): string
    {
        $d = new DateTime('today');
        if ($periodo === 'week')  $d->modify('monday this week');
        if ($periodo === 'month') $d->modify('first day of this month');
        return $d->format('Y-m-d H:i:s');
    }

    public static function resumen(string $periodo): array
    {
        $desde = self::desde($periodo);
        $v = ReporteRepository::ventas($desde);
        $ganancia = ReporteRepository::ganancia($desde);
        $perdidas = ReporteRepository::perdidas($desde);
        return [
            'desde' => $desde, 'n' => (int)$v['n'], 'total' => (float)$v['total'],
            'ganancia' => $ganancia, 'perdidas' => $perdidas, 'neta' => $ganancia - $perdidas,
            'pagos' => ReporteRepository::porPago($desde), 'empleados' => ReporteRepository::porEmpleado($desde),
            'top' => ReporteRepository::ranking($desde, 'DESC'), 'low' => ReporteRepository::ranking($desde, 'ASC'),
        ];
    }
}
