<?php
/** Administración de proveedores y portal privado de consulta de entregas. */
class ProveedorController
{
    public static function index(): void
    {
        View::render('proveedores', ['proveedores' => ProveedorRepository::listar()]);
    }

    public static function crear(): void
    {
        try {
            $nombre = trim($_POST['nombre'] ?? '');
            $tipo = $_POST['tipo'] ?? 'PRODUCTOR';
            if ($nombre === '') throw new RuntimeException('Escribe el nombre del proveedor.');
            if (!in_array($tipo, ['PRODUCTOR', 'DISTRIBUIDOR'], true)) throw new RuntimeException('Tipo de proveedor no válido.');
            $id = ProveedorRepository::crear($nombre, trim($_POST['telefono'] ?? ''), trim($_POST['municipio'] ?? ''), $tipo);
            Auditoria::log('CREAR_PROVEEDOR', 'proveedores', $id);
            flash('ok', 'Proveedor registrado correctamente. Ahora puedes crearle su cuenta de acceso.');
        } catch (Throwable $e) { flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'No fue posible registrar el proveedor.'); }
        redirigir('proveedores');
    }

    public static function crearCuenta(): void
    {
        try {
            $id = (int)($_POST['proveedor_id'] ?? 0);
            $proveedor = ProveedorRepository::porId($id);
            if (!$proveedor || (int)$proveedor['activo'] !== 1) throw new RuntimeException('Selecciona un proveedor activo.');
            if (ProveedorRepository::tieneCuenta($id)) throw new RuntimeException('Este proveedor ya tiene una cuenta asociada.');
            AuthService::crearUsuario('PROVEEDOR', $proveedor['nombre'], $_POST['email'] ?? '', $_POST['clave'] ?? '', $id);
            Auditoria::log('CREAR_CUENTA_PROVEEDOR', 'proveedores', $id);
            flash('ok', 'Cuenta creada. El proveedor ya puede iniciar sesión y consultar sus entregas.');
        } catch (Throwable $e) { flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'No fue posible crear la cuenta.'); }
        redirigir('proveedores');
    }

    public static function portal(): void
    {
        $u = Auth::user();
        $proveedorId = (int)($u['proveedor_id'] ?? 0);
        if ($proveedorId < 1) { http_response_code(403); exit('La cuenta no está vinculada a un proveedor. Contacta al administrador.'); }
        $proveedor = ProveedorRepository::porId($proveedorId);
        if (!$proveedor || (int)$proveedor['activo'] !== 1) { http_response_code(403); exit('El acceso de este proveedor está deshabilitado.'); }
        View::render('portal_proveedor', ['proveedor' => $proveedor, 'entregas' => ProveedorRepository::misEntregas($proveedorId)]);
    }
}
