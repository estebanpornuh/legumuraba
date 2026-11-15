<div class="botones">
  <?php foreach (['day' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes'] as $k => $t): ?>
    <a class="s <?= $periodo === $k ? 'on' : '' ?>" href="<?= e(url('reportes', ['periodo' => $k])) ?>"><?= $t ?></a>
  <?php endforeach; ?>
  <a class="s" href="<?= e(url('reportes/csv', ['periodo' => $periodo])) ?>">⬇ Exportar CSV</a>
</div>
<div class="g tarjetas">
  <div class="card"><div class="mu">Ventas (<?= $r['n'] ?>)</div><div class="k"><?= money($r['total']) ?></div></div>
  <div class="card"><div class="mu">Ganancia aproximada</div><div class="k" style="color:var(--ac)"><?= money($r['ganancia']) ?></div></div>
  <div class="card"><div class="mu">Pérdidas por daños</div><div class="k" style="color:var(--wa)"><?= money($r['perdidas']) ?></div></div>
  <div class="card"><div class="mu">Ganancia neta aprox.</div><div class="k"><?= money($r['neta']) ?></div></div>
</div>
<div class="g">
  <div class="card"><h3>Más vendidos</h3>
    <?php foreach ($r['top'] as $x): ?><div><?= e($x['nombre']) ?> <b><?= e(round((float)$x['q'], 2)) ?> <?= e($x['unidad']) ?></b></div><?php endforeach; ?>
    <?php if (!$r['top']): ?><span class="mu">Sin datos</span><?php endif; ?></div>
  <div class="card"><h3>Menos vendidos</h3>
    <?php foreach ($r['low'] as $x): ?><div><?= e($x['nombre']) ?> <b><?= e(round((float)$x['q'], 2)) ?> <?= e($x['unidad']) ?></b></div><?php endforeach; ?>
    <?php if (!$r['low']): ?><span class="mu">Sin datos</span><?php endif; ?></div>
  <div class="card"><h3>Por método de pago</h3>
    <?php foreach ($r['pagos'] as $x): ?><div><?= e($x['metodo_pago']) ?> <b><?= money($x['total']) ?></b></div><?php endforeach; ?>
    <?php if (!$r['pagos']): ?><span class="mu">Sin datos</span><?php endif; ?></div>
  <div class="card"><h3>Ventas por empleado</h3>
    <?php foreach ($r['empleados'] as $x): ?><div><?= e($x['nombre']) ?> <b><?= money($x['total']) ?></b></div><?php endforeach; ?>
    <?php if (!$r['empleados']): ?><span class="mu">Sin datos</span><?php endif; ?></div>
</div>
