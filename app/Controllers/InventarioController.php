<?php
/** InventarioController: control de existencias, alertas de reposición y ajustes por pérdida o daño. */
class InventarioController
{
    public static function existencias(): void
    {
        View::render('existencias', ['productos' => ProductoRepository::todos(), 'ajustes' => Auth::rol() === 'admin' ? InventarioRepository::ajustesRecientes() : []]);
    }

    public static function ajustar(): void
    {
        try {
            AjusteService::registrar((int)($_POST['producto_id'] ?? 0), (float)($_POST['cantidad'] ?? 0), $_POST['motivo'] ?? '', trim($_POST['nota'] ?? ''));
            flash('ok', 'Ajuste registrado.');
        } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
        redirigir('existencias');
    }
}
