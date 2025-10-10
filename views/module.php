<?php
$module = $module ?? null;
$lessons = $lessons ?? [];
$completedLessons = $completedLessons ?? [];
$total = count($lessons);
$completed = 0;
foreach ($lessons as $lesson) {
  if (in_array($lesson['id'], $completedLessons, true)) {
    $completed++;
  }
}
$pct = $total ? round(($completed / $total) * 100) : 0;
$isTeacher = in_array(current_user()['role'], ['teacher','admin'], true);
?>
<section class="hero">
  <div class="subtitle">Módulo</div>
  <h1><?php echo htmlspecialchars($module['title']); ?></h1>
  <p><?php echo nl2br(htmlspecialchars($module['description'] ?? '')); ?></p>
  <div class="module-progress-grid">
    <div class="module-progress-card">
      <span class="chip outline">Progreso</span>
      <h2><?php echo $pct; ?>%</h2>
      <div class="progress-bar"><span style="transform: scaleX(<?php echo $total > 0 ? min(1, $completed / $total) : 0; ?>);"></span></div>
      <p class="muted"><?php echo $completed; ?> / <?php echo $total; ?> lecciones completadas.</p>
    </div>
    <div class="module-progress-card">
      <span class="chip outline">Autor</span>
      <h3><?php echo htmlspecialchars($module['author']); ?></h3>
      <p class="muted">Publicado el <?php echo date('d M Y', strtotime($module['created_at'] ?? 'now')); ?></p>
    </div>
    <?php if ($isTeacher): ?>
      <div class="module-progress-card">
        <span class="chip outline">Acción rápida</span>
        <p class="muted">Gestiona lecciones y edita información desde el panel.</p>
        <a class="button secondary" href="?a=admin#module-<?php echo $module['id']; ?>">Gestionar módulo</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<section class="grid" style="margin-top:32px; gap:20px;">
  <h2>Lecciones</h2>
  <?php if (!$lessons): ?>
    <div class="empty">Todavía no se han creado lecciones para este módulo.</div>
  <?php else: ?>
    <div class="lesson-list">
      <?php foreach ($lessons as $lesson):
        $isCompleted = in_array($lesson['id'], $completedLessons, true);
      ?>
        <article class="lesson-card <?php echo $isCompleted ? 'completed' : ''; ?>">
          <header>
            <div>
              <h3><?php echo htmlspecialchars($lesson['title']); ?></h3>
              <p class="muted" style="font-size:0.85rem;">Por <?php echo htmlspecialchars($lesson['author']); ?></p>
            </div>
            <span class="chip <?php echo $isCompleted ? 'success' : 'outline'; ?>"><?php echo $isCompleted ? 'Completada' : 'Pendiente'; ?></span>
          </header>
          <div class="muted" style="font-size:0.85rem;">Creada el <?php echo date('d/m/Y', strtotime($lesson['created_at'] ?? 'now')); ?></div>
          <div class="actions">
            <a class="button small" href="?a=view_lesson&id=<?php echo $lesson['id']; ?>">Entrar</a>
            <?php if ($isTeacher): ?>
              <a class="button secondary small" href="?a=admin#module-<?php echo $module['id']; ?>">Editar</a>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
