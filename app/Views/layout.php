<?php
// layout.php: estructura común de todas las pantallas (encabezado, menú y mensajes).
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$rol = Auth::rol();
$menu = $rol === 'admin'
    ? ['productos' => 'Productos', 'entradas' => 'Entradas', 'caja' => '🧾 Caja', 'ventas' => 'Ventas', 'existencias' => 'Existencias', 'reportes' => 'Reportes', 'usuarios' => 'Usuarios', 'proveedores' => 'Proveedores']
    : ($rol === 'proveedor' ? ['proveedor/portal' => 'Mis entregas'] : ['caja' => '🧾 Caja', 'existencias' => 'Existencias']);
$actual = $_GET['r'] ?? '';
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(negocio()) ?> – Inventario y caja</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<?php if (Auth::user()): ?>
  <header>
    <span class="marca">🥬 <?= e(negocio()) ?></span>
    <span class="mu"><?= e(Auth::user()['nombre']) ?> · <?= $rol === 'admin' ? 'Administrador' : ($rol === 'proveedor' ? 'Proveedor' : 'Vendedor') ?></span>
    <form method="post" action="<?= e(url('logout')) ?>"><?= Csrf::campo() ?><button class="s">Salir</button></form>
  </header>
  <nav>
    <?php foreach ($menu as $r => $t): ?>
      <a href="<?= e(url($r)) ?>" class="<?= strpos($actual, $r) === 0 ? 'on' : '' ?>"><?= e($t) ?></a>
    <?php endforeach; ?>
  </nav>
<?php endif; ?>
<main>
  <?php if ($flash): ?><div class="aviso <?= e($flash[0]) ?>"><?= e($flash[1]) ?></div><?php endif; ?>
  <?= $contenido ?>
</main>
</body>
</html>
