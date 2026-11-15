<?php
/** ProductoController: gestión de productos (RF-03). Solo administrador. */
class ProductoController
{
    public static function index(): void
    {
        $editar = isset($_GET['editar']) ? ProductoRepository::buscar((int)$_GET['editar']) : null;
        View::render('productos', ['productos' => ProductoRepository::todos(), 'editar' => $editar]);
    }

    public static function guardar(): void
    {
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $nombre = trim($_POST['nombre'] ?? '');
        $unidad = $_POST['unidad'] ?? '';
        if ($nombre === '' || !in_array($unidad, ['kg', 'g', 'unidad', 'bulto', 'caja'], true)) {
            flash('error', 'Completa el nombre y la unidad.');
            redirigir('productos');
        }
        $d = [
            'nombre' => $nombre, 'unidad' => $unidad,
            'categoria_id' => ProductoRepository::categoriaId(trim($_POST['categoria'] ?? '') ?: 'Otros'),
            'precio_compra' => max(0, (float)($_POST['precio_compra'] ?? 0)),
            'precio_venta'  => max(0, (float)($_POST['precio_venta'] ?? 0)),
            'stock_minimo'  => max(0, (float)($_POST['stock_minimo'] ?? 0)),
            'stock_inicial' => max(0, (float)($_POST['stock_inicial'] ?? 0)),
            'imagen' => self::subirImagen(),
        ];
        $nuevo = ProductoRepository::guardar($id, $d);
        if (!$id && $d['stock_inicial'] > 0) InventarioRepository::kardex($nuevo, 'AJUSTE', $d['stock_inicial'], $nuevo);   // stock inicial en el kardex
        Auditoria::log($id ? 'EDITAR_PRODUCTO' : 'CREAR_PRODUCTO', 'productos', $nuevo);
        flash('ok', 'Producto guardado.');
        redirigir('productos');
    }

    public static function desactivar(): void
    {
        ProductoRepository::desactivar((int)($_POST['id'] ?? 0));
        Auditoria::log('DESACTIVAR_PRODUCTO', 'productos', (int)($_POST['id'] ?? 0));
        flash('ok', 'Producto desactivado (se conserva su historial).');
        redirigir('productos');
    }

    /** Valida y guarda la imagen: tipo real (JPG/PNG), máximo 2 MB y nombre aleatorio. Devuelve el nombre o null. */
    private static function subirImagen(): ?string
    {
        $f = $_FILES['imagen'] ?? null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) return null;
        if ($f['size'] > 2 * 1024 * 1024) { flash('error', 'La imagen supera 2 MB.'); return null; }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);          // tipo REAL del archivo, no el que dice el nombre
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png'][$mime] ?? null;
        if (!$ext) { flash('error', 'La imagen debe ser JPG o PNG.'); return null; }
        $nombre = bin2hex(random_bytes(8)) . '.' . $ext;
        move_uploaded_file($f['tmp_name'], __DIR__ . '/../../public/uploads/' . $nombre);
        return $nombre;
    }
}
