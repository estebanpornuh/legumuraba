<div class="card">
  <h3>Bienvenido, <?= e($proveedor['nombre']) ?></h3>
  <p class="mu">Este espacio es privado. Aquí puedes consultar únicamente las entregas registradas a tu nombre en LegumUrabá.</p>
  <p><strong>Tipo:</strong> <?= e($proveedor['tipo']) ?> &nbsp; <strong>Municipio:</strong> <?= e($proveedor['municipio'] ?: 'No registrado') ?></p>
</div>
<div class="card">
  <h3>Mis entregas registradas</h3>
  <div class="tw"><table><tr><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Lote</th><th>Observación</th></tr>
  <?php foreach ($entregas as $x): ?><tr>
    <td><?= e($x['fecha']) ?></td><td><?= e($x['producto']) ?></td><td><?= e($x['cantidad'] + 0) ?> <?= e($x['unidad']) ?></td>
    <td><?= e($x['lote']) ?></td><td><?= e($x['observacion'] ?: '—') ?></td>
  </tr><?php endforeach; ?>
  <?php if (!$entregas): ?><tr><td colspan="5" class="mu">Todavía no hay entregas registradas para este proveedor.</td></tr><?php endif; ?>
  </table></div>
</div>
