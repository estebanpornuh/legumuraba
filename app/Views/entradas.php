<?php if (!$productos): ?><div class="card mu">Primero crea productos en la pestaña “Productos”.</div><?php else: ?>
<div class="card">
  <h3>Registrar entrada de mercancía</h3>
  <form method="post" action="<?= e(url('entradas/guardar')) ?>">
    <?= Csrf::campo() ?>
    <div class="g">
      <label>Producto<select name="producto_id">
        <?php foreach ($productos as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?> (<?= e($p['stock_actual'] + 0) ?> <?= e($p['unidad']) ?>)</option><?php endforeach; ?>
      </select></label>
      <label>Cantidad comprada<input type="number" name="cantidad" min="0" step="any" required></label>
      <label>Precio de compra (unitario)<input type="number" name="precio" min="0" step="any" required></label>
      <label>Fecha de entrada<input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required></label>
      <label>Proveedor<select name="proveedor" required><option value="">Selecciona proveedor</option><?php foreach ($proveedores as $pr): if ((int)$pr['activo'] === 1): ?><option value="<?= (int)$pr['id'] ?>"><?= e($pr['nombre']) ?></option><?php endif; endforeach; ?></select></label>
      <label>Observación<input name="observacion" placeholder="Opcional"></label>
    </div>
    <button class="b">Registrar entrada</button>
  </form>
</div>
<?php endif; ?>
<div class="card">
  <h3>Entradas recientes (trazabilidad)</h3>
  <div class="tw"><table>
    <tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Proveedor</th><th>Lote</th><th>P. compra</th><th>Total</th></tr>
    <?php foreach ($entradas as $x): ?>
      <tr><td><?= e($x['fecha']) ?></td><td><?= e($x['producto']) ?></td><td><?= e($x['cantidad'] + 0) ?> <?= e($x['unidad']) ?></td>
          <td><?= e($x['proveedor']) ?></td><td><?= e($x['lote']) ?></td><td><?= money($x['precio_compra']) ?></td><td><?= money($x['subtotal']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$entradas): ?><tr><td colspan="7" class="mu">Sin entradas.</td></tr><?php endif; ?>
  </table></div>
</div>
