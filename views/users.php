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
          <header style="display:flex; justify-content:space-between; align-items:flex-start; gap:14px;">
            <div>
              <h3><?php echo htmlspecialchars($user['name']); ?></h3>
              <p class="muted" style="font-size:0.85rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <span class="chip outline">Rol: <?php echo htmlspecialchars($user['role']); ?></span>
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
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
              <form method="post" action="?a=user_set_role" style="display:flex; gap:10px; align-items:center;">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <label style="margin:0;">
                  <span style="font-size:0.75rem; letter-spacing:0.14em; text-transform:uppercase; color:rgba(244,248,255,0.6);">Cambiar rol</span>
                  <select name="role" style="margin-top:4px; background:rgba(6,14,30,0.8); border:1px solid rgba(255,255,255,0.08); color:var(--text-strong); border-radius:10px; padding:8px 12px;">
                    <?php foreach (['student'=>'Estudiante','teacher'=>'Profesor','admin'=>'Admin'] as $value => $label): ?>
                      <option value="<?php echo $value; ?>" <?php if ($user['role'] === $value) echo 'selected'; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                  </select>
                </label>
                <button class="button small" type="submit">Actualizar</button>
              </form>
              <?php if ($user['id'] !== current_user()['id']): ?>
                <form method="post" action="?a=user_delete" onsubmit="return confirm('¿Eliminar usuario?');">
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
