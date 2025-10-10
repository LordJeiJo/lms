<section class="hero" style="margin-top: 24px;">
  <div class="subtitle">Bienvenido a tu LMS </div>
  <h1>Desbloquea experiencias de aprendizaje sin fricción.</h1>
  <p>La plataforma ultra ligera inspirada en ThePowerMBA + EdApp. Gestiona módulos, mide progreso y ofrece contenidos en un entorno que vibra con energía.</p>
</section>
<div class="grid two" style="margin-top: 12px;">
  <div class="card">
    <h2>Inicia sesión</h2>
    <p class="muted">Accede al panel y continúa donde lo dejaste.</p>
    <?php if (!empty($error ?? null)): ?>
      <div class="flash error" style="margin-top:16px;">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="?a=login" style="margin-top:20px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <label>
        Correo electrónico
        <input type="email" name="email" autocomplete="email" required>
      </label>
      <label>
        Contraseña
        <input type="password" name="pass" autocomplete="current-password" required>
      </label>
      <button class="button" type="submit">Entrar ahora</button>
    </form>
  </div>
  <div class="card">
    <h2>Únete al campus</h2>
    <p class="muted">Crea tu cuenta en segundos. Empieza como estudiante (si necesitas otro rol, un admin podrá asignártelo).</p>
    <form method="post" action="?a=register" style="margin-top:20px;">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      <label>
        Nombre completo
        <input type="text" name="name" autocomplete="name" required>
      </label>
      <label>
        Correo corporativo
        <input type="email" name="email" autocomplete="email" required>
      </label>
      <label>
        Crea una contraseña
        <input type="password" name="pass" autocomplete="new-password" minlength="4" required>
      </label>
      <button class="button secondary" type="submit">Crear cuenta</button>
    </form>
  </div>
</div>
