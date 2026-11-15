<?php
/** EntradaController: entrada de mercancía (RF-05) y su historial con proveedor y lote (RF-16). */
class EntradaController
{
    public static function index(): void
    {
        View::render('entradas', ['productos' => ProductoRepository::todos(), 'proveedores' => ProveedorRepository::listar(), 'entradas' => EntradaRepository::recientes()]);
    }

    public static function guardar(): void
    {
        try {
            EntradaService::registrar($_POST['proveedor'] ?? '', $_POST['fecha'] ?? '', (int)($_POST['producto_id'] ?? 0),
                (float)($_POST['cantidad'] ?? 0), (float)($_POST['precio'] ?? 0), trim($_POST['observacion'] ?? ''));
            flash('ok', 'Entrada registrada: el stock y el precio de compra se actualizaron.');
        } catch (RuntimeException $e) { flash('error', $e->getMessage()); }
        redirigir('entradas');
    }
}
