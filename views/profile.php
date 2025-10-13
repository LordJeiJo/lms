<?php
$profileUser = $profileUser ?? current_user();
$errors = $errors ?? [];
$mustReset = !empty($profileUser['must_reset_password']);
?>
<section class="hero">
  <div class="subtitle">Tu cuenta</div>
  <h1>Personaliza tus datos y protege tu acceso.</h1>
  <p>Actualiza tu nombre visible y establece una contraseña sólida para mantener tu progreso a salvo.</p>
</section>
<?php if ($mustReset): ?>
  <div class="flash error" style="margin-bottom:18px;">⚡ Debes cambiar la contraseña temporal antes de continuar navegando por la plataforma.</div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="flash error" style="margin-bottom:18px; display:grid; gap:6px;">
    <span>Se encontraron algunos problemas:</span>
    <ul style="margin:0; padding-left:18px;">
      <?php foreach ($errors as $error): ?>
        <li><?php echo htmlspecialchars($error); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<div class="grid" style="margin-top:24px; gap:24px; align-items:flex-start;">
  <article class="card" style="display:grid; gap:18px; max-width:520px;">
    <h2>Datos principales</h2>
    <form method="post" action="?a=profile" style="display:grid; gap:16px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <label style="display:grid; gap:6px;">
        <span>Nombre visible</span>
        <input type="text" name="name" value="<?php echo htmlspecialchars($profileUser['name'] ?? ''); ?>" required>
      </label>
      <div class="muted" style="font-size:0.85rem;">Correo electrónico: <strong style="color:var(--text-strong);"><?php echo htmlspecialchars($profileUser['email'] ?? ''); ?></strong></div>
      <div class="muted" style="font-size:0.85rem;">Rol actual: <strong style="color:var(--text-strong);"><?php echo htmlspecialchars($profileUser['role'] ?? ''); ?></strong></div>
      <div style="height:1px; background:var(--border-soft);"></div>
      <h3 style="margin:0; font-size:1.05rem;">Cambiar contraseña</h3>
      <p class="muted" style="margin:0; font-size:0.85rem;">Completa los campos siguientes únicamente si deseas actualizarla. Necesitas indicar la contraseña actual para confirmar el cambio.</p>
      <label style="display:grid; gap:6px;">
        <span>Contraseña actual</span>
        <input type="password" name="current_pass" autocomplete="current-password" placeholder="••••••">
      </label>
      <label style="display:grid; gap:6px;">
        <span>Nueva contraseña (mínimo <?php echo PASSWORD_MIN_LENGTH; ?> caracteres)</span>
        <input type="password" name="new_pass" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="new-password" placeholder="Nueva contraseña">
      </label>
      <label style="display:grid; gap:6px;">
        <span>Repite la nueva contraseña</span>
        <input type="password" name="confirm_pass" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="new-password" placeholder="Confirma la contraseña">
      </label>
      <button class="button" type="submit">Guardar cambios</button>
    </form>
  </article>
  <article class="card" style="display:grid; gap:14px; max-width:420px;">
    <h3>Consejos rápidos</h3>
    <ul style="margin:0; padding-left:20px; display:grid; gap:10px; font-size:0.9rem; color:var(--text-soft);">
      <li>Utiliza una contraseña única que combine letras, números y símbolos.</li>
      <li>Si recibiste una contraseña temporal de un administrador, cámbiala inmediatamente desde este formulario.</li>
      <li>Puedes actualizar tu nombre en cualquier momento: es el que verán tus compañeros y tutores.</li>
    </ul>
  </article>
</div>
