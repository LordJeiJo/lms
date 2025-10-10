<?php
$lesson = $lesson ?? null;
$comments = $comments ?? [];
$isCompleted = $isCompleted ?? false;
$moduleProgress = $moduleProgress ?? ['completed' => 0, 'total' => 0];
$total = max(0, (int)($moduleProgress['total'] ?? 0));
$done = max(0, (int)($moduleProgress['completed'] ?? 0));
$pct = $total ? round(($done / $total) * 100) : 0;

/**
 * Normaliza URLs de vídeo (YouTube o Vimeo) para incrustarlas en un iframe.
 */
function video_embed_src(?string $url): ?string {
  if (!$url) return null;
  $url = trim($url);

  // YOUTUBE: watch, youtu.be, shorts, playlist
  $ytId = null;

  // playlist
  if (preg_match('~(?:youtube\.com/(?:playlist|watch).*?[?&]list=)([a-zA-Z0-9_-]+)~i', $url, $m)) {
    $list = $m[1];
    $start = null;
    if (preg_match('~[?&](?:t|start)=(\d+)~', $url, $ms)) $start = (int)$ms[1];
    return 'https://www.youtube.com/embed/videoseries?list=' . urlencode($list) . ($start ? '&start=' . $start : '');
  }

  // shorts
  if (preg_match('~youtube\.com/shorts/([a-zA-Z0-9_-]{6,})~i', $url, $m)) {
    $ytId = $m[1];
  }
  // watch?v=
  elseif (preg_match('~youtube\.com/watch\?[^#]*v=([a-zA-Z0-9_-]{6,})~i', $url, $m)) {
    $ytId = $m[1];
  }
  // youtu.be/ID
  elseif (preg_match('~youtu\.be/([a-zA-Z0-9_-]{6,})~i', $url, $m)) {
    $ytId = $m[1];
  }

  if ($ytId) {
    $start = null;
    if (preg_match('~[?&](?:t|start)=(\d+)~', $url, $ms)) $start = (int)$ms[1];
    $params = [
      'rel' => 0,
      'modestbranding' => 1,
      'playsinline' => 1,
    ];
    if ($start) $params['start'] = $start;
    return 'https://www.youtube-nocookie.com/embed/' . $ytId . '?' . http_build_query($params);
  }

  // VIMEO
  if (preg_match('~vimeo\.com/(?:video/)?(\d{6,})~i', $url, $m)) {
    return 'https://player.vimeo.com/video/' . $m[1];
  }

  // Si no reconocemos la URL, devolvemos la original
  return $url;
}

$embedSrc = video_embed_src($lesson['video_url'] ?? '');
?>
<section class="hero">
  <div class="subtitle">Lección</div>
  <h1><?php echo htmlspecialchars($lesson['title']); ?></h1>
  <p class="muted">
    Del módulo: <strong><?php echo htmlspecialchars($lesson['module_title']); ?></strong> · 
    Por <?php echo htmlspecialchars($lesson['author']); ?>
  </p>

  <div class="module-progress-grid">
    <div class="module-progress-card">
      <span class="chip outline">Progreso del módulo</span>
      <h2><?php echo $pct; ?>%</h2>
      <div class="progress-bar">
        <span style="transform: scaleX(<?php echo $total > 0 ? min(1, $done / $total) : 0; ?>);"></span>
      </div>
      <p class="muted"><?php echo $done; ?> / <?php echo $total; ?> lecciones completadas.</p>
    </div>

    <div class="module-progress-card" style="align-content:start;">
      <span class="chip outline">Estado</span>
      <h3><?php echo $isCompleted ? 'Completada' : 'Pendiente'; ?></h3>
      <form method="post" action="?a=lesson_progress_toggle" style="margin-top:12px; display:flex; gap:12px; flex-wrap:wrap;">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
        <input type="hidden" name="module_id" value="<?php echo $lesson['module_id']; ?>">
        <input type="hidden" name="complete" value="<?php echo $isCompleted ? '0' : '1'; ?>">
        <button class="button <?php echo $isCompleted ? 'secondary' : ''; ?>" type="submit">
          <?php echo $isCompleted ? 'Marcar como pendiente' : 'Marcar como completada'; ?>
        </button>
      </form>
    </div>
  </div>
</section>

<section class="card" style="display:grid; gap:20px;">
  <?php if ($embedSrc): ?>
    <div style="position:relative; padding-bottom:56.25%; height:0; border-radius:20px; overflow:hidden;">
      <iframe
        src="<?php echo htmlspecialchars($embedSrc); ?>"
        title="<?php echo htmlspecialchars($lesson['title']); ?>"
        style="position:absolute; inset:0; width:100%; height:100%; border:0;"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen
      ></iframe>
    </div>
  <?php endif; ?>

  <article class="lesson-content" style="display:grid; gap:16px;">
    <div class="muted" style="font-size:0.85rem;">
      Actualizado el <?php echo date('d/m/Y H:i', strtotime($lesson['created_at'] ?? 'now')); ?>
    </div>
    <div class="lesson-body" style="display:grid; gap:16px; line-height:1.7;">
      <?php echo $lesson['content_html']; ?>
    </div>
  </article>
</section>

<section class="grid" style="margin-top:32px; gap:20px;">
  <h2>Comentarios</h2>
  <div class="card" style="display:grid; gap:18px;">
    <?php if (!$comments): ?>
      <div class="empty" style="margin:0;">Sé la primera persona en comentar esta lección.</div>
    <?php else: ?>
      <div class="grid" style="gap:14px;">
        <?php foreach ($comments as $comment): ?>
          <div class="comment">
            <div class="meta">
              <?php echo htmlspecialchars($comment['name']); ?> · 
              <?php echo htmlspecialchars($comment['role']); ?> · 
              <?php echo date('d/m/Y H:i', strtotime($comment['created_at'] ?? 'now')); ?>
            </div>
            <div><?php echo nl2br(htmlspecialchars($comment['body'])); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="?a=comment_post" style="margin-top:10px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <input type="hidden" name="lesson_id" value="<?php echo $lesson['id']; ?>">
      <label>
        Añadir comentario
        <textarea name="body" placeholder="Comparte feedback, dudas o aprendizajes clave..." required></textarea>
      </label>
      <button class="button small" type="submit">Publicar comentario</button>
    </form>
  </div>
</section>
