<?php
$u = current_user();
$flash = flash_get();
$viewFile = __DIR__ . '/' . ($view ?? '');
$viewPath = is_string($viewFile) ? $viewFile . '.php' : null;
if (!is_file($viewPath ?? '')) {
  http_response_code(500);
  echo 'Vista no encontrada';
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>JeiJoLand</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      color-scheme: dark;
      --bg-gradient: linear-gradient(135deg, #030711, #141d34 52%, #1b3867);
      --card-gradient: linear-gradient(160deg, rgba(255,255,255,0.08), rgba(255,255,255,0.01));
      --frost: rgba(9, 18, 40, 0.72);
      --frost-strong: rgba(11, 25, 58, 0.92);
      --outline: rgba(255,255,255,0.08);
      --glow: rgba(82, 215, 255, 0.28);
      --accent: #7ef6d8;
      --accent-strong: #4be4ff;
      --accent-soft: rgba(64, 217, 255, 0.18);
      --text-strong: #f4f8ff;
      --text-soft: rgba(244, 248, 255, 0.7);
      --danger: #ff6b7d;
      --warning: #ffad4b;
      --success: #5dffa3;
      --font-base: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    * {
      box-sizing: border-box;
    }
    html, body {
      margin: 0;
      min-height: 100%;
      background: var(--bg-gradient);
      font-family: var(--font-base);
      color: var(--text-soft);
      letter-spacing: -0.01em;
    }
    body {
      position: relative;
      overflow-x: hidden;
    }
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background: radial-gradient(600px at 15% 15%, rgba(126, 246, 216, 0.15), transparent 60%),
                  radial-gradient(700px at 85% 10%, rgba(75, 228, 255, 0.16), transparent 65%),
                  radial-gradient(900px at 70% 80%, rgba(255, 255, 255, 0.08), transparent 70%);
      pointer-events: none;
      z-index: -2;
    }
    body::after {
      content: '';
      position: fixed;
      inset: 0;
      backdrop-filter: blur(90px);
      pointer-events: none;
      z-index: -3;
    }
    a {
      color: inherit;
      text-decoration: none;
    }
    .app-shell {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      padding: 32px clamp(16px, 3vw, 48px) 48px;
      gap: 32px;
    }
    .app-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px clamp(16px, 3vw, 36px);
      background: var(--frost);
      border-radius: 26px;
      border: 1px solid var(--outline);
      box-shadow: 0 18px 40px -30px rgba(0,0,0,0.85), 0 0 0 1px var(--accent-soft);
      backdrop-filter: blur(22px);
      position: sticky;
      top: 24px;
      z-index: 20;
    }
    .brand {
      display: flex;
      align-items: center;
      gap: 14px;
      font-weight: 800;
      font-size: clamp(1.1rem, 2.3vw, 1.5rem);
      text-transform: uppercase;
      letter-spacing: 0.18em;
      color: var(--text-strong);
    }
    .brand .pulse {
      display: inline-flex;
      width: 32px;
      height: 32px;
      border-radius: 12px;
      background: radial-gradient(circle at 30% 30%, #f9ffff, #9af7ff 45%, rgba(154,247,255,0.2) 100%);
      box-shadow: 0 0 0 2px rgba(154,247,255,0.35), 0 8px 18px -8px rgba(126, 246, 216, 0.95);
      justify-content: center;
      align-items: center;
      color: #0a1a33;
      font-size: 0.9rem;
      font-weight: 700;
    }
    nav {
      display: flex;
      align-items: center;
      gap: clamp(12px, 2vw, 28px);
      font-weight: 500;
      font-size: 0.95rem;
    }
    .nav-link {
      display: inline-flex;
      padding: 10px 16px;
      border-radius: 14px;
      border: 1px solid transparent;
      transition: all 0.18s ease;
      background: transparent;
      color: var(--text-soft);
    }
    .nav-link.active,
    .nav-link:hover {
      border-color: rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.05);
      color: var(--text-strong);
    }
    .user-pill {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 10px 18px;
      border-radius: 999px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.12);
      backdrop-filter: blur(20px);
      box-shadow: 0 12px 24px -20px rgba(126, 246, 216, 0.7);
    }
    .user-avatar {
      width: 38px;
      height: 38px;
      border-radius: 12px;
      background: radial-gradient(circle at top, rgba(126,246,216,0.8), rgba(75,228,255,0.45));
      display: grid;
      place-items: center;
      font-weight: 700;
      color: #031227;
    }
    .logout-form {
      margin: 0;
    }
    .logout-form button {
      border: none;
      padding: 8px 14px;
      border-radius: 10px;
      font-weight: 600;
      background: rgba(255,255,255,0.08);
      color: var(--text-strong);
      cursor: pointer;
      transition: background 0.2s ease;
    }
    .logout-form button:hover {
      background: rgba(126,246,216,0.32);
      color: #041127;
    }
    main {
      display: flex;
      flex-direction: column;
      gap: 24px;
      width: min(1200px, 100%);
      margin: 0 auto;
    }
    .flash {
      padding: 18px 22px;
      border-radius: 18px;
      border: 1px solid rgba(255,255,255,0.18);
      background: rgba(7,18,35,0.84);
      backdrop-filter: blur(18px);
      display: flex;
      align-items: center;
      gap: 14px;
      font-weight: 600;
      color: var(--text-strong);
      box-shadow: 0 14px 30px -24px rgba(0,0,0,0.6);
      animation: fadeIn 0.4s ease;
    }
    .flash.ok {
      border-color: rgba(126,246,216,0.45);
      box-shadow: 0 0 24px -14px rgba(126, 246, 216, 0.9);
    }
    .flash.error {
      border-color: rgba(255,107,125,0.45);
      box-shadow: 0 0 24px -14px rgba(255,107,125,0.9);
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .grid {
      display: grid;
      gap: 20px;
    }
    .grid.two {
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
    .card {
      position: relative;
      border-radius: 24px;
      padding: clamp(22px, 3vw, 32px);
      background: var(--card-gradient);
      border: 1px solid var(--outline);
      box-shadow: 0 30px 80px -60px rgba(0,0,0,0.9), inset 0 0 0 1px rgba(255,255,255,0.02);
      backdrop-filter: blur(18px);
      color: var(--text-soft);
    }
    h1, h2, h3, h4 {
      margin: 0;
      color: var(--text-strong);
      letter-spacing: -0.02em;
    }
    h1 {
      font-size: clamp(2.1rem, 4vw, 2.8rem);
      font-weight: 800;
    }
    h2 {
      font-size: clamp(1.4rem, 3vw, 2rem);
      font-weight: 700;
    }
    h3 {
      font-size: clamp(1.1rem, 2.2vw, 1.5rem);
      font-weight: 600;
    }
    p {
      margin: 0;
      line-height: 1.6;
    }
    .muted {
      color: var(--text-soft);
      opacity: 0.75;
      font-size: 0.95rem;
    }
    .hero {
      display: grid;
      gap: 18px;
      padding: clamp(30px, 4vw, 40px);
      border-radius: 30px;
      background: linear-gradient(145deg, rgba(126,246,216,0.18), rgba(75,228,255,0.1));
      border: 1px solid rgba(126,246,216,0.35);
      box-shadow: 0 24px 80px -60px rgba(126, 246, 216, 0.8);
    }
    .hero .subtitle {
      text-transform: uppercase;
      letter-spacing: 0.45em;
      font-weight: 600;
      font-size: 0.75rem;
      color: rgba(244,248,255,0.6);
    }
    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 12px 20px;
      border-radius: 14px;
      border: 1px solid transparent;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.95rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      background: linear-gradient(135deg, rgba(126,246,216,0.85), rgba(75,228,255,0.9));
      color: #041427;
      box-shadow: 0 18px 30px -20px rgba(75, 228, 255, 0.9);
    }
    .button:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 40px -18px rgba(126, 246, 216, 0.9);
    }
    .button.secondary {
      background: transparent;
      color: var(--text-strong);
      border-color: rgba(255,255,255,0.18);
      box-shadow: none;
    }
    .button.secondary:hover {
      border-color: rgba(126,246,216,0.4);
      background: rgba(126,246,216,0.16);
      color: #031227;
    }
    .button.danger {
      background: rgba(255,107,125,0.85);
      color: #1b0b16;
      box-shadow: 0 18px 36px -22px rgba(255,107,125,0.7);
    }
    .button.small {
      padding: 8px 14px;
      font-size: 0.85rem;
    }
    form {
      display: grid;
      gap: 14px;
    }
    label {
      display: grid;
      gap: 6px;
      font-weight: 600;
      color: rgba(244,248,255,0.82);
      font-size: 0.9rem;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
      background: rgba(7,17,34,0.72);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 12px 16px;
      color: var(--text-strong);
      font: inherit;
      transition: border 0.2s ease, box-shadow 0.2s ease;
    }
    textarea {
      min-height: 160px;
      resize: vertical;
    }
    input:focus,
    textarea:focus {
      outline: none;
      border-color: rgba(126,246,216,0.45);
      box-shadow: 0 0 0 3px rgba(126,246,216,0.18);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      border-radius: 18px;
      overflow: hidden;
      background: rgba(6,14,30,0.75);
      border: 1px solid rgba(255,255,255,0.08);
    }
    thead {
      background: rgba(255,255,255,0.05);
      color: var(--text-strong);
    }
    th, td {
      padding: 14px 16px;
      text-align: left;
      border-bottom: 1px solid rgba(255,255,255,0.06);
      vertical-align: top;
    }
    tr:last-child td {
      border-bottom: none;
    }
    .progress-bar {
      position: relative;
      height: 10px;
      border-radius: 999px;
      background: rgba(255,255,255,0.08);
      overflow: hidden;
    }
    .progress-bar span {
      position: absolute;
      inset: 0;
      border-radius: inherit;
      background: linear-gradient(90deg, rgba(126,246,216,0.85), rgba(75,228,255,0.95));
      transform-origin: left center;
    }
    .chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 999px;
      padding: 6px 10px;
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      background: rgba(255,255,255,0.08);
    }
    .chip.success {
      background: rgba(126,246,216,0.2);
      color: #031227;
    }
    .chip.warning {
      background: rgba(255,173,75,0.18);
      color: #1f1204;
    }
    .chip.outline {
      border: 1px solid rgba(255,255,255,0.15);
      background: transparent;
      color: var(--text-soft);
    }
    .lesson-list {
      display: grid;
      gap: 16px;
    }
    .lesson-card {
      padding: 18px 20px;
      border-radius: 18px;
      background: rgba(6,14,30,0.7);
      border: 1px solid rgba(255,255,255,0.06);
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .lesson-card.completed {
      border-color: rgba(126,246,216,0.35);
      background: rgba(126,246,216,0.08);
    }
    .lesson-card header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .lesson-card .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    details.drawer {
      background: rgba(7,17,34,0.75);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 20px;
      padding: 18px 22px;
    }
    details.drawer summary {
      cursor: pointer;
      font-weight: 600;
      color: var(--text-strong);
      list-style: none;
    }
    details.drawer[open] {
      border-color: rgba(126,246,216,0.3);
      background: rgba(126,246,216,0.1);
    }
    details.drawer summary::-webkit-details-marker {
      display: none;
    }
    details.drawer summary::after {
      content: '▸';
      margin-left: 10px;
      transition: transform 0.2s ease;
      display: inline-block;
    }
    details.drawer[open] summary::after {
      transform: rotate(90deg);
    }
    .comment {
      display: grid;
      gap: 6px;
      padding: 14px 16px;
      background: rgba(6,14,30,0.68);
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,0.08);
    }
    .comment .meta {
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: rgba(244,248,255,0.6);
    }
    .empty {
      padding: 26px;
      border-radius: 20px;
      border: 1px dashed rgba(255,255,255,0.18);
      background: rgba(6,14,30,0.55);
      text-align: center;
      font-weight: 600;
      color: rgba(244,248,255,0.6);
    }
    .module-progress-grid {
      display: grid;
      gap: 18px;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    }
    .module-progress-card {
      background: rgba(6,14,30,0.72);
      border: 1px solid rgba(255,255,255,0.06);
      border-radius: 18px;
      padding: 16px 20px;
      display: grid;
      gap: 12px;
    }
    .table-scroll {
      overflow-x: auto;
    }
    @media (max-width: 860px) {
      .app-header {
        flex-wrap: wrap;
        gap: 18px;
      }
      nav {
        width: 100%;
        justify-content: center;
      }
      .user-pill {
        width: 100%;
        justify-content: space-between;
      }
      main {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <header class="app-header">
      <a class="brand" href="?a=<?php echo $u ? 'home' : 'landing'; ?>">
        <span class="pulse">▲</span>
        JeiJoLand
      </a>
      <nav>
        <?php if ($u): ?>
          <a class="nav-link <?php echo ($_GET['a'] ?? 'home') === 'home' ? 'active' : ''; ?>" href="?a=home">Inicio</a>
          <a class="nav-link <?php echo ($_GET['a'] ?? '') === 'view_module' ? 'active' : ''; ?>" href="?a=home#modulos">Módulos</a>
          <?php if (in_array($u['role'], ['teacher','admin'], true)): ?>
            <a class="nav-link <?php echo ($_GET['a'] ?? '') === 'admin' ? 'active' : ''; ?>" href="?a=admin">Panel</a>
            <a class="nav-link <?php echo ($_GET['a'] ?? '') === 'users' ? 'active' : ''; ?>" href="?a=users">Seguimiento</a>
          <?php endif; ?>
        <?php else: ?>
          <a class="nav-link active" href="?a=landing">Bienvenida</a>
        <?php endif; ?>
      </nav>
      <?php if ($u): ?>
        <div class="user-pill">
          <div class="user-avatar"><?php echo strtoupper(substr($u['name'], 0, 2)); ?></div>
          <div>
            <div style="font-weight:700;color:var(--text-strong); font-size:0.95rem;">Hola, <?php echo htmlspecialchars($u['name']); ?></div>
            <div style="font-size:0.75rem; letter-spacing:0.16em; text-transform:uppercase; opacity:0.7;">Rol · <?php echo htmlspecialchars($u['role']); ?></div>
          </div>
          <form class="logout-form" method="post" action="?a=logout">
            <button type="submit">Salir</button>
          </form>
        </div>
      <?php endif; ?>
    </header>
    <main>
      <?php if ($flash): ?>
        <div class="flash <?php echo htmlspecialchars($flash['type']); ?>">⚡ <?php echo htmlspecialchars($flash['msg']); ?></div>
      <?php endif; ?>
      <?php include $viewPath; ?>
    </main>
  </div>
  <script>
    setTimeout(() => {
      const flash = document.querySelector('.flash');
      if (flash) {
        flash.style.transition = 'opacity .5s ease, transform .5s ease';
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-8px)';
        setTimeout(() => flash.remove(), 600);
      }
    }, 3600);
  </script>
</body>
</html>
