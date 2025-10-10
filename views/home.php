<?php
$progress = $progress ?? [];
$totalLessons = $totalLessons ?? 0;
$totalCompleted = $totalCompleted ?? 0;
$overall = $overall ?? 0;
$isTeacher = in_array(current_user()['role'], ['teacher','admin'], true);
?>
<section class="hero">
  <div class="subtitle">Dashboard</div>
  <h1><?php echo $isTeacher ? 'Orquesta el aprendizaje con datos inmediatos.' : 'Tu mapa de aprendizaje, siempre al día.'; ?></h1>
  <p><?php echo $isTeacher
    ? 'Revisa el avance de tu cohorte, crea módulos al vuelo y mantén el pulso de cada itinerario.'
    : 'Visualiza cuánto has completado en cada módulo y retoma las lecciones pendientes desde un sólo lugar.'; ?></p>
  <div class="module-progress-grid">
    <div class="module-progress-card">
      <span class="chip outline">Progreso global</span>
      <h2><?php echo $overall; ?>%</h2>
      <div class="progress-bar"><span style="transform: scaleX(<?php echo $overall / 100; ?>);"></span></div>
      <p class="muted"><?php echo $totalCompleted; ?> / <?php echo $totalLessons; ?> lecciones completadas.</p>
    </div>
    <?php if ($isTeacher): ?>
      <div class="module-progress-card">
        <span class="chip outline">Modo creador</span>
        <h3>Gestiona contenido</h3>
        <p class="muted">Lanza nuevos módulos, edita lecciones y monitoriza roles desde el panel.</p>
        <a class="button secondary" href="?a=admin">Ir al panel</a>
      </div>
    <?php else: ?>
      <div class="module-progress-card">
        <span class="chip outline">Siguiente paso</span>
        <?php
        $nextModule = null;
        foreach ($modules as $module) {
          $completed = $progress[$module['id']] ?? 0;
          if (($module['lesson_count'] ?? 0) > $completed) {
            $nextModule = $module;
            break;
          }
        }
        ?>
        <?php if ($nextModule): ?>
          <h3><?php echo htmlspecialchars($nextModule['title']); ?></h3>
          <p class="muted">Te quedan <?php echo max(0, ($nextModule['lesson_count'] ?? 0) - ($progress[$nextModule['id']] ?? 0)); ?> lecciones para completarlo.</p>
          <a class="button secondary" href="?a=view_module&id=<?php echo $nextModule['id']; ?>">Continuar módulo</a>
        <?php else: ?>
          <h3>¡Todo al día!</h3>
          <p class="muted">Has completado todos los módulos disponibles. 🎉</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<section id="modulos" class="grid" style="margin-top: 30px;">
  <h2>Módulos en vivo</h2>
  <?php if (!$modules): ?>
    <div class="empty">Todavía no hay módulos publicados.</div>
  <?php else: ?>
    <div class="grid two">
      <?php foreach ($modules as $module):
        $completed = $progress[$module['id']] ?? 0;
        $total = (int)($module['lesson_count'] ?? 0);
        $pct = $total > 0 ? round(($completed / $total) * 100) : 0;
      ?>
        <article class="card" style="display:grid; gap:16px;">
          <header style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
              <h3><?php echo htmlspecialchars($module['title']); ?></h3>
              <p class="muted">Por <?php echo htmlspecialchars($module['author']); ?></p>
            </div>
            <span class="chip <?php echo $pct >= 100 ? 'success' : 'outline'; ?>"><?php echo $pct; ?>%</span>
          </header>
          <?php if (!empty($module['description'])): ?>
            <p><?php echo nl2br(htmlspecialchars($module['description'])); ?></p>
          <?php endif; ?>
          <div>
            <div class="muted" style="font-size:0.85rem; margin-bottom:8px;">Progreso: <?php echo $completed; ?> / <?php echo $total; ?> lecciones</div>
            <div class="progress-bar"><span style="transform: scaleX(<?php echo $total > 0 ? min(1, $completed / $total) : 0; ?>);"></span></div>
          </div>
          <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a class="button small" href="?a=view_module&id=<?php echo $module['id']; ?>">Abrir módulo</a>
            <?php if ($isTeacher): ?>
              <a class="button secondary small" href="?a=admin#module-<?php echo $module['id']; ?>">Gestionar</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
