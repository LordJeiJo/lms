<?php
$modules = $modules ?? [];
$users = $users ?? [];
$progressMatrix = $progressMatrix ?? [];
$moduleTotals = [];
foreach ($modules as $module) {
  $moduleTotals[$module['id']] = (int)($module['lesson_count'] ?? 0);
}
$isAdmin = current_user()['role'] === 'admin';
?>
<section class="hero">
  <div class="subtitle">Seguimiento</div>
  <h1>Visibilidad total del avance de cada alumno.</h1>
  <p>Integra la precisión de ThePowerMBA con la interfaz dinámica de EdApp: detecta dónde acelerar, qué reforzar y quién necesita ayuda.</p>
</section>
<?php if ($isAdmin): ?>
  <section class="card" style="margin-top:32px; padding:28px; display:grid; gap:18px;">
    <div>
      <h2>Registrar nuevo usuario</h2>
      <p class="muted" style="margin:0;">Crea cuentas manualmente. Puedes definir el rol inicial y si deberá cambiar la contraseña al iniciar sesión.</p>
    </div>
    <form method="post" action="?a=register" style="display:grid; gap:16px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
        <label style="display:grid; gap:6px;">
          <span>Nombre completo</span>
          <input type="text" name="name" autocomplete="name" required>
        </label>
        <label style="display:grid; gap:6px;">
          <span>Correo electrónico</span>
          <input type="email" name="email" autocomplete="email" required>
        </label>
      </div>
      <div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px;">
        <label style="display:grid; gap:6px;">
          <span>Rol</span>
          <select name="role" required>
            <option value="student">Estudiante</option>
            <option value="teacher">Profesor</option>
            <option value="admin">Admin</option>
          </select>
        </label>
        <label style="display:grid; gap:6px;">
          <span>Contraseña temporal</span>
          <input type="password" name="pass" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="new-password" required>
        </label>
      </div>
      <label style="display:flex; align-items:center; gap:10px; font-size:0.9rem;">
        <input type="checkbox" name="must_reset" value="1" checked>
        <span>Obligar a cambiar la contraseña al iniciar sesión</span>
      </label>
      <button class="button secondary" type="submit" style="justify-self:flex-start;">Crear usuario</button>
    </form>
  </section>
<?php endif; ?>
<section class="grid" style="margin-top:32px; gap:20px;">
  <h2>Alumnado</h2>
  <?php if (!$users): ?>
    <div class="empty">Todavía no hay usuarios registrados.</div>
  <?php else: ?>
    <div class="grid" style="gap:22px;">
      <?php foreach ($users as $user):
        $userProgress = $progressMatrix[$user['id']] ?? [];
      ?>
        <article class="card" style="display:grid; gap:18px;">
          <header style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px; flex-wrap:wrap;">
            <div>
              <h3><?php echo htmlspecialchars($user['name']); ?></h3>
              <p class="muted" style="font-size:0.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <span class="chip outline">Rol: <?php echo htmlspecialchars($user['role']); ?></span>
            <?php if (!empty($user['must_reset_password'])): ?>
              <span class="chip warning">Contraseña pendiente</span>
            <?php endif; ?>
          </header>
          <div class="muted" style="font-size:0.8rem;">Alta: <?php echo date('d/m/Y', strtotime($user['created_at'] ?? 'now')); ?></div>
          <div class="module-progress-grid">
            <?php foreach ($modules as $module):
              $total = $moduleTotals[$module['id']] ?: 0;
              $done = $userProgress[$module['id']] ?? 0;
              $pct = $total ? round(($done / $total) * 100) : 0;
            ?>
              <div class="module-progress-card" style="gap:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px;">
                  <span style="font-weight:600; color:var(--text-strong); font-size:0.95rem;"><?php echo htmlspecialchars($module['title']); ?></span>
                  <span class="chip <?php echo $pct >= 100 ? 'success' : 'outline'; ?>"><?php echo $pct; ?>%</span>
                </div>
                <div class="progress-bar"><span style="transform: scaleX(<?php echo $total ? min(1, $done / $total) : 0; ?>);"></span></div>
                <p class="muted" style="font-size:0.78rem; text-transform:uppercase; letter-spacing:0.12em;">Completado: <?php echo $done; ?> / <?php echo $total; ?></p>
              </div>
            <?php endforeach; ?>
            <?php if (!$modules): ?>
              <div class="empty" style="margin:0;">Sin módulos todavía.</div>
            <?php endif; ?>
          </div>
          <?php if ($isAdmin): ?>
            <div class="user-actions">
              <form method="post" action="?a=user_set_role" class="user-role-form">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <label class="user-role-label" for="user-role-<?php echo $user['id']; ?>">
                  <span>Cambiar rol</span>
                  <select id="user-role-<?php echo $user['id']; ?>" name="role" class="user-role-select">
                    <?php foreach (['student'=>'Estudiante','teacher'=>'Profesor','admin'=>'Admin'] as $value => $label): ?>
                      <option value="<?php echo $value; ?>" <?php if ($user['role'] === $value) echo 'selected'; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <button class="button small" type="submit">Actualizar</button>
              </form>
              <form method="post" action="?a=user_set_password" class="user-password-form" style="display:grid; gap:8px;">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <label style="display:grid; gap:6px;">
                  <span>Asignar contraseña temporal</span>
                  <input type="password" name="password" minlength="<?php echo PASSWORD_MIN_LENGTH; ?>" autocomplete="new-password" placeholder="Nueva contraseña" required>
                </label>
                <p class="muted" style="font-size:0.75rem; margin:0;">El usuario deberá cambiarla al iniciar sesión.</p>
                <button class="button secondary small" type="submit">Guardar contraseña</button>
              </form>
              <?php if ($user['id'] !== current_user()['id']): ?>
                <form method="post" action="?a=user_delete" class="user-delete-form" onsubmit="return confirm('¿Eliminar usuario?');">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <button class="button danger small" type="submit">Eliminar</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
