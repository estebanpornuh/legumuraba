<?php
$alertas = array_filter($productos, fn($p) => (float)$p['stock_actual'] <= (float)$p['stock_minimo']);
$estado = function ($p) {
    if ((float)$p['stock_actual'] <= 0) return ['Agotado', 'ag'];
    if ((float)$p['stock_actual'] <= (float)$p['stock_minimo']) return ['Pocas existencias', 'lo'];
    return ['OK', 'ok'];
};
?>
<?php if ($alertas): ?>
<div class="card al"><h3>⚠️ Alertas de reposición (<?= count($alertas) ?>)</h3>
  <?php foreach ($alertas as $p): ?><div><?= e($p['nombre']) ?>: quedan <b><?= e($p['stock_actual'] + 0) ?> <?= e($p['unidad']) ?></b> (mínimo <?= e($p['stock_minimo'] + 0) ?>)</div><?php endforeach; ?>
</div>
<?php endif; ?>
<div class="card">
  <h3>Existencias actuales</h3>
  <div class="tw"><table>
    <tr><th>Producto</th><th>Cantidad</th><th>Mínimo</th><th>Estado</th></tr>
    <?php foreach ($productos as $p): [$t, $c] = $estado($p); ?>
      <tr><td><?= e($p['nombre']) ?></td><td><?= e($p['stock_actual'] + 0) ?> <?= e($p['unidad']) ?></td><td><?= e($p['stock_minimo'] + 0) ?></td><td><span class="tag <?= $c ?>"><?= $t ?></span></td></tr>
    <?php endforeach; ?>
  </table></div>
</div>
<?php if (Auth::rol() === 'admin' && $productos): ?>
<div class="card">
  <h3>Ajuste por pérdida o daño</h3>
  <form method="post" action="<?= e(url('ajustes/guardar')) ?>">
    <?= Csrf::campo() ?>
    <div class="g">
      <label>Producto<select name="producto_id"><?php foreach ($productos as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nombre']) ?> (<?= e($p['stock_actual'] + 0) ?> <?= e($p['unidad']) ?>)</option><?php endforeach; ?></select></label>
      <label>Cantidad perdida<input type="number" name="cantidad" min="0" step="any" required></label>
      <label>Motivo<select name="motivo"><option value="PERDIDA">Pérdida</option><option value="DANO">Daño</option><option value="VENCIDO">Vencido</option><option value="CORRECCION">Corrección</option></select></label>
      <label>Nota<input name="nota" placeholder="Opcional"></label>
    </div>
    <button class="b">Registrar ajuste</button>
  </form>
  <div class="tw" style="margin-top:12px"><table>
    <tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Costo</th></tr>
    <?php foreach ($ajustes as $a): ?>
      <tr><td><?= e($a['fecha_hora']) ?></td><td><?= e($a['producto']) ?></td><td><?= e($a['cantidad'] + 0) ?> <?= e($a['unidad']) ?></td><td><?= e($a['motivo']) ?><?= $a['nota'] ? ' · ' . e($a['nota']) : '' ?></td><td><?= money($a['costo_total']) ?></td></tr>
    <?php endforeach; ?>
  </table></div>
</div>
<?php endif; ?>
