<div class="card">
  <h3>Historial de ventas</h3>
  <div class="tw"><table>
    <tr><th>#</th><th>Fecha y hora</th><th>Empleado</th><th>Productos</th><th>Total</th><th>Pago</th></tr>
    <?php foreach ($ventas as $v): ?>
      <tr><td><?= (int)$v['id'] ?></td><td><?= e($v['fecha_hora']) ?></td><td><?= e($v['empleado']) ?></td>
          <td style="white-space:normal"><?= e($v['items']) ?></td><td><?= money($v['total']) ?></td><td><?= e($v['metodo_pago']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$ventas): ?><tr><td colspan="6" class="mu">Sin ventas.</td></tr><?php endif; ?>
  </table></div>
</div>
