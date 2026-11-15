<div class="card centro">
  <h2 style="text-align:center">Configuración inicial</h2>
  <p class="mu">Crea la cuenta del administrador. Esta pantalla solo aparece la primera vez.</p>
  <?php if ($error): ?><div class="aviso error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e(url('setup')) ?>">
    <?= Csrf::campo() ?>
    <label>Nombre del negocio<input name="negocio" value="<?= e($_POST['negocio'] ?? '') ?>"></label>
    <label>Tu nombre<input name="nombre" required value="<?= e($_POST['nombre'] ?? '') ?>"></label>
    <label>Correo<input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
    <label>Contraseña (mínimo 8 caracteres)<input type="password" name="clave" required></label>
    <label>Repite la contraseña<input type="password" name="clave2" required></label>
    <button class="b" style="width:100%">Crear cuenta</button>
  </form>
</div>
