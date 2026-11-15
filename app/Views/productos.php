<?php $p = $editar ?: []; ?>
<div class="card">
  <h3><?= $editar ? 'Editar producto' : 'Nuevo producto' ?></h3>
  <form method="post" action="<?= e(url('productos/guardar')) ?>" enctype="multipart/form-data">
    <?= Csrf::campo() ?>
    <input type="hidden" name="id" value="<?= e($p['id'] ?? '') ?>">
    <div class="g">
      <label>Nombre<input name="nombre" required value="<?= e($p['nombre'] ?? '') ?>"></label>
      <label>Categoría<input name="categoria" placeholder="Verduras, frutas…" value="<?= e($p['categoria'] ?? '') ?>"></label>
      <label>Unidad<select name="unidad">
        <?php foreach (['kg', 'g', 'unidad', 'bulto', 'caja'] as $u): ?><option <?= ($p['unidad'] ?? '') === $u ? 'selected' : '' ?>><?= e($u) ?></option><?php endforeach; ?>
      </select></label>
      <label>Precio de compra<input type="number" name="precio_compra" min="0" step="any" value="<?= e($p['precio_compra'] ?? 0) ?>"></label>
      <label>Precio de venta<input type="number" name="precio_venta" min="0" step="any" value="<?= e($p['precio_venta'] ?? 0) ?>"></label>
      <?php if (!$editar): ?><label>Cantidad inicial<input type="number" name="stock_inicial" min="0" step="any" value="0"></label><?php endif; ?>
      <label>Stock mínimo (alerta)<input type="number" name="stock_minimo" min="0" step="any" value="<?= e($p['stock_minimo'] ?? 5) ?>"></label>
      <label>Imagen (JPG o PNG, máx. 2 MB)<input type="file" name="imagen" accept="image/jpeg,image/png"></label>
    </div>
    <button class="b"><?= $editar ? 'Guardar cambios' : 'Agregar producto' ?></button>
    <?php if ($editar): ?><a class="s" href="<?= e(url('productos')) ?>">Cancelar</a><?php endif; ?>
  </form>
</div>
<div class="card">
  <h3>Productos (<?= count($productos) ?>)</h3>
  <div class="tw"><table>
    <tr><th></th><th>Nombre</th><th>Categoría</th><th>Unidad</th><th>Compra</th><th>Venta</th><th>Stock</th><th></th></tr>
    <?php foreach ($productos as $x): ?>
      <tr>
        <td><?php if ($x['imagen_ruta']): ?><img class="th" src="uploads/<?= e($x['imagen_ruta']) ?>" alt=""><?php endif; ?></td>
        <td><?= e($x['nombre']) ?></td><td><?= e($x['categoria']) ?></td><td><?= e($x['unidad']) ?></td>
        <td><?= money($x['precio_compra']) ?></td><td><?= money($x['precio_venta']) ?></td><td><?= e($x['stock_actual'] + 0) ?></td>
        <td>
          <a class="s" href="<?= e(url('productos', ['editar' => $x['id']])) ?>">Editar</a>
          <form method="post" action="<?= e(url('productos/desactivar')) ?>" style="display:inline" onsubmit="return confirm('¿Desactivar este producto?')">
            <?= Csrf::campo() ?><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button class="s">✕</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$productos): ?><tr><td colspan="8" class="mu">Aún no hay productos.</td></tr><?php endif; ?>
  </table></div>
</div>
