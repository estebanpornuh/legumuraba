<div class="card">
  <h3>Nuevo usuario</h3>
  <form method="post" action="<?= e(url('usuarios/crear')) ?>">
    <?= Csrf::campo() ?>
    <div class="g">
      <label>Nombre<input name="nombre" required></label>
      <label>Correo<input type="email" name="email" required></label>
      <label>Contraseña (mínimo 8)<input type="password" name="clave" required></label>
      <label>Rol<select name="rol"><option value="VENDEDOR">Vendedor (solo caja)</option><option value="ADMIN">Administrador</option></select></label>
    </div>
    <button class="b">Crear usuario</button>
  </form>
</div>
<div class="card">
  <h3>Usuarios</h3>
  <div class="tw"><table>
    <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th></th></tr>
    <?php foreach ($usuarios as $u): ?>
      <tr><td><?= e($u['nombre']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['rol']) ?><?= !empty($u['proveedor']) ? ' · ' . e($u['proveedor']) : '' ?></td>
        <td><span class="tag <?= $u['activo'] ? 'ok' : 'ag' ?>"><?= $u['activo'] ? 'Activo' : 'Desactivado' ?></span></td>
        <td><form method="post" action="<?= e(url('usuarios/estado')) ?>"><?= Csrf::campo() ?>
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><input type="hidden" name="activo" value="<?= $u['activo'] ? 0 : 1 ?>">
          <button class="s"><?= $u['activo'] ? 'Desactivar' : 'Activar' ?></button></form></td></tr>
    <?php endforeach; ?>
  </table></div>
</div>
