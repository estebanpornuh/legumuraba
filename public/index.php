<?php
/**
 * index.php: FRONT CONTROLLER. Toda petición entra por aquí.
 * 1) Carga las clases   2) Inicia la sesión   3) Busca la ruta (?r=...)
 * 4) Verifica permisos por rol (RBAC) y el token CSRF   5) Ejecuta el controlador.
 */
declare(strict_types=1);
ini_set('display_errors', '0');        // en producción no se muestran errores técnicos al usuario

require __DIR__ . '/../app/Core/helpers.php';
// Autocarga: busca la clase en las carpetas de la aplicación
spl_autoload_register(function (string $c) {
    foreach (['Core', 'Models', 'Services', 'Controllers'] as $dir) {
        $f = __DIR__ . "/../app/$dir/$c.php";
        if (is_file($f)) { require $f; return; }
    }
});

// Cualquier error no controlado muestra un mensaje genérico y se guarda en el registro de PHP
set_exception_handler(function (Throwable $e) {
    error_log($e);
    http_response_code(500);
    echo 'Ocurrió un error. Verifica que MySQL esté encendido y que importaste database/schema.sql.';
});

Auth::iniciarSesion();

// ruta => [Controlador, método, acceso]   acceso: public | staff (admin o vendedor) | admin
$rutas = [
    'login'              => ['AuthController', 'login', 'public'],
    'setup'              => ['AuthController', 'setup', 'public'],
    'logout'             => ['AuthController', 'logout', 'auth'],
    'productos'          => ['ProductoController', 'index', 'admin'],
    'productos/guardar'  => ['ProductoController', 'guardar', 'admin'],
    'productos/desactivar' => ['ProductoController', 'desactivar', 'admin'],
    'entradas'           => ['EntradaController', 'index', 'admin'],
    'entradas/guardar'   => ['EntradaController', 'guardar', 'admin'],
    'caja'               => ['VentaController', 'caja', 'staff'],
    'ventas/guardar'     => ['VentaController', 'guardar', 'staff'],
    'ventas'             => ['VentaController', 'historial', 'admin'],
    'existencias'        => ['InventarioController', 'existencias', 'staff'],
    'ajustes/guardar'    => ['InventarioController', 'ajustar', 'admin'],
    'reportes'           => ['ReporteController', 'ver', 'admin'],
    'reportes/csv'       => ['ReporteController', 'csv', 'admin'],
    'usuarios'           => ['UsuarioController', 'index', 'admin'],
    'usuarios/crear'     => ['UsuarioController', 'crear', 'admin'],
    'usuarios/estado'    => ['UsuarioController', 'estado', 'admin'],
    'proveedores'         => ['ProveedorController', 'index', 'admin'],
    'proveedores/crear'   => ['ProveedorController', 'crear', 'admin'],
    'proveedores/cuenta'  => ['ProveedorController', 'crearCuenta', 'admin'],
    'proveedor/portal'    => ['ProveedorController', 'portal', 'provider'],
];

$r = $_GET['r'] ?? '';
if ($r === '') {   // ruta por defecto según el estado del sistema
    if (UsuarioRepository::contar() === 0) redirigir('setup');
    if (!Auth::user()) redirigir('login');
    redirigir(Auth::rol() === 'admin' ? 'productos' : (Auth::rol() === 'proveedor' ? 'proveedor/portal' : 'caja'));
}
if (!isset($rutas[$r])) { http_response_code(404); exit('Página no encontrada.'); }

[$clase, $metodo, $acceso] = $rutas[$r];
if ($acceso !== 'public') {
    if (!Auth::user()) redirigir('login');
    $permitido = $acceso === 'admin' ? Auth::rol() === 'admin' : ($acceso === 'provider' ? Auth::rol() === 'proveedor' : ($acceso === 'auth' ? Auth::user() !== null : in_array(Auth::rol(), ['admin', 'vendedor'], true)));
    if (!$permitido) { http_response_code(403); exit('No tienes permiso para ver esta página.'); }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !Csrf::verificar()) {
    http_response_code(419);
    exit('La sesión expiró o la petición no es válida. Vuelve atrás y recarga la página.');
}
$clase::$metodo();
