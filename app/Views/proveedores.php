<div class="card">
  <h3>Registrar proveedor</h3>
  <form method="post" action="<?= e(url('proveedores/crear')) ?>">
    <?= Csrf::campo() ?>
    <div class="g">
      <label>Nombre o razón social<input name="nombre" maxlength="100" required></label>
      <label>Teléfono<input name="telefono" maxlength="20"></label>
      <label>Municipio<input name="municipio" maxlength="60"></label>
      <label>Tipo<select name="tipo"><option value="PRODUCTOR">Productor</option><option value="DISTRIBUIDOR">Distribuidor</option></select></label>
    </div>
    <button class="b">Guardar proveedor</button>
  </form>
</div>
<div class="card">
  <h3>Crear cuenta de acceso</h3>
  <p class="mu">La cuenta queda vinculada al proveedor seleccionado. Solo podrá consultar sus propias entregas.</p>
  <form method="post" action="<?= e(url('proveedores/cuenta')) ?>">
    <?= Csrf::campo() ?>
    <div class="g">
      <label>Proveedor<select name="proveedor_id" required><option value="">Selecciona un proveedor</option>
        <?php foreach ($proveedores as $p): if ((int)$p['activo'] === 1 && (int)$p['cuentas_activas'] === 0): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?> · <?= e($p['municipio'] ?: 'Sin municipio') ?></option><?php endif; endforeach; ?>
      </select></label>
      <label>Correo de acceso<input type="email" name="email" required></label>
      <label>Contraseña (mínimo 8 caracteres)<input type="password" name="clave" minlength="8" required></label>
    </div>
    <button class="b">Crear cuenta del proveedor</button>
  </form>
</div>
<div class="card">
  <h3>Proveedores registrados</h3>
  <div class="tw"><table><tr><th>Nombre</th><th>Tipo</th><th>Teléfono</th><th>Municipio</th><th>Cuenta</th><th>Estado</th></tr>
  <?php foreach ($proveedores as $p): ?><tr>
    <td><?= e($p['nombre']) ?></td><td><?= e($p['tipo']) ?></td><td><?= e($p['telefono'] ?: '—') ?></td><td><?= e($p['municipio'] ?: '—') ?></td>
    <td><?= (int)$p['cuentas_activas'] > 0 ? 'Cuenta activa' : 'Sin cuenta' ?></td><td><?= (int)$p['activo'] === 1 ? 'Activo' : 'Inactivo' ?></td>
  </tr><?php endforeach; ?>
  <?php if (!$proveedores): ?><tr><td colspan="6" class="mu">Todavía no hay proveedores registrados.</td></tr><?php endif; ?>
  </table></div>
</div>
