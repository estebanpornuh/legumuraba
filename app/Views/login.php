<div class="card centro">
  <h2 style="text-align:center">🥬 <?= e(negocio()) ?></h2>
  <?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e(url('login')) ?>">
    <?= Csrf::campo() ?>
    <label>Correo<input type="email" name="email" required autofocus></label>
    <label>Contraseña<input type="password" name="clave" required></label>
    <button class="b" style="width:100%">Entrar</button>
  </form>
</div>
