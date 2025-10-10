<section class="hero">
  <div class="subtitle">Panel de creación</div>
  <h1>Control absoluto sin perder velocidad.</h1>
  <p>Activa nuevos módulos, edita contenidos existentes y mantén la biblioteca alineada con tu estrategia.</p>
</section>
<div class="grid" style="margin-top:24px;">
  <details class="drawer" open>
    <summary>Crear nuevo módulo</summary>
    <form method="post" action="?a=create_module" style="margin-top:18px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <label>
        Título del módulo
        <input type="text" name="title" required>
      </label>
      <label>
        Descripción (opcional)
        <textarea name="description" placeholder="Qué transformación obtiene el alumno, duración, requisitos..."></textarea>
      </label>
      <button class="button" type="submit">Publicar módulo</button>
    </form>
  </details>
</div>
<section class="grid" style="margin-top:32px; gap:24px;">
  <h2>Módulos existentes</h2>
  <?php if (!$modules): ?>
    <div class="empty">Crea tu primer módulo para empezar.</div>
  <?php else: ?>
    <?php foreach ($modules as $module): ?>
      <article class="card" id="module-<?php echo $module['id']; ?>" style="display:grid; gap:18px;">
        <header style="display:flex; justify-content:space-between; gap:14px; align-items:flex-start;">
          <div>
            <h3><?php echo htmlspecialchars($module['title']); ?></h3>
            <p class="muted"><?php echo htmlspecialchars($module['description'] ?? ''); ?></p>
          </div>
          <span class="chip outline">Lecciones: <?php echo count($module['lessons'] ?? []); ?></span>
        </header>
        <details class="drawer">
          <summary>Editar módulo</summary>
          <form method="post" action="?a=update_module" style="margin-top:18px;">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?php echo $module['id']; ?>">
            <label>
              Título
              <input type="text" name="title" value="<?php echo htmlspecialchars($module['title']); ?>" required>
            </label>
            <label>
              Descripción
              <textarea name="description"><?php echo htmlspecialchars($module['description'] ?? ''); ?></textarea>
            </label>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
              <button class="button small" type="submit">Guardar cambios</button>
            </div>
          </form>
          <form method="post" action="?a=delete_module" onsubmit="return confirm('¿Seguro que quieres eliminar este módulo?');" style="margin-top:12px;">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="id" value="<?php echo $module['id']; ?>">
            <button class="button danger small" type="submit">Eliminar módulo</button>
          </form>
        </details>
        <details class="drawer">
          <summary>Crear nueva lección</summary>
          <form method="post" action="?a=create_lesson" style="margin-top:18px;">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
            <label>
              Título de la lección
              <input type="text" name="title" required>
            </label>
            <label>
              Contenido HTML (simple)
              <textarea name="content_html" placeholder="Puedes usar HTML básico para estructurar el contenido."></textarea>
            </label>
            <label>
              URL de vídeo (YouTube, Vimeo...)
              <input type="text" name="video_url" placeholder="https://...">
            </label>
            <button class="button small" type="submit">Añadir lección</button>
          </form>
        </details>
        <section class="lesson-list">
          <?php if (empty($module['lessons'])): ?>
            <div class="empty" style="border-style:dashed;">Aún no hay lecciones en este módulo.</div>
          <?php else: ?>
            <?php foreach ($module['lessons'] as $lesson): ?>
              <article class="lesson-card" style="gap:14px;">
                <header>
                  <div>
                    <h3 style="font-size:1.1rem;"><?php echo htmlspecialchars($lesson['title']); ?></h3>
                    <p class="muted" style="font-size:0.8rem;">Creada por <?php echo htmlspecialchars($lesson['author']); ?></p>
                  </div>
                  <div class="actions">
                    <a class="button secondary small" href="?a=view_lesson&id=<?php echo $lesson['id']; ?>">Ver</a>
                    <details class="drawer" style="padding:0; border:none; background:transparent;">
                      <summary style="padding:6px 10px; border-radius:10px; background:rgba(255,255,255,0.08);">Editar</summary>
                      <div style="padding:16px; background:rgba(6,14,30,0.85); border-radius:16px; margin-top:10px;">
                        <form method="post" action="?a=update_lesson">
                          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                          <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">
                          <input type="hidden" name="module_id" value="<?php echo $module['id']; ?>">
                          <label>
                            Título
                            <input type="text" name="title" value="<?php echo htmlspecialchars($lesson['title']); ?>" required>
                          </label>
                          <label>
                            Contenido HTML
                            <textarea name="content_html"><?php echo htmlspecialchars($lesson['content_html'] ?? ''); ?></textarea>
                          </label>
                          <label>
                            URL de vídeo
                            <input type="text" name="video_url" value="<?php echo htmlspecialchars($lesson['video_url'] ?? ''); ?>">
                          </label>
                          <div style="display:flex; gap:10px;">
                            <button class="button small" type="submit">Actualizar</button>
                          </div>
                        </form>
                        <form method="post" action="?a=delete_lesson" onsubmit="return confirm('¿Eliminar lección?');" style="margin-top:12px;">
                          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                          <input type="hidden" name="id" value="<?php echo $lesson['id']; ?>">
                          <button class="button danger small" type="submit">Eliminar</button>
                        </form>
                      </div>
                    </details>
                  </div>
                </header>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </section>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
