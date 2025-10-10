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
  <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token()); ?>" />
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
    .rich-editor {
      display: grid;
      gap: 10px;
      background: rgba(6,14,30,0.7);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 18px;
      padding: 14px;
    }
    .rich-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .rich-toolbar button {
      border: 1px solid rgba(255,255,255,0.12);
      background: rgba(255,255,255,0.06);
      color: var(--text-strong);
      border-radius: 10px;
      padding: 6px 12px;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.02em;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }
    .rich-toolbar button:hover {
      background: rgba(126,246,216,0.24);
      color: #031227;
      transform: translateY(-1px);
    }
    .rich-toolbar button:focus {
      outline: 2px solid rgba(126,246,216,0.4);
      outline-offset: 2px;
    }
    .rich-editor-area {
      min-height: 200px;
      padding: 12px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.08);
      background: rgba(7,17,34,0.72);
      line-height: 1.6;
      overflow: auto;
    }
    .rich-editor-area:focus {
      border-color: rgba(126,246,216,0.45);
      box-shadow: 0 0 0 3px rgba(126,246,216,0.18);
      outline: none;
    }
    .rich-editor-area:empty::before {
      content: attr(data-placeholder);
      color: rgba(244,248,255,0.45);
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
    .lesson-list[data-saving] {
      opacity: 0.7;
      pointer-events: none;
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
    .lesson-card.dragging {
      opacity: 0.6;
      transform: scale(0.99);
    }
    .drag-handle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 6px;
      border-radius: 10px;
      background: rgba(255,255,255,0.06);
      font-size: 1.1rem;
      cursor: grab;
      user-select: none;
      transition: background 0.2s ease, color 0.2s ease;
    }
    .drag-handle:active {
      cursor: grabbing;
      background: rgba(126,246,216,0.22);
      color: #031227;
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
    .lesson-content {
      display: grid;
      gap: 16px;
    }
    .lesson-body {
      display: grid;
      gap: 16px;
      line-height: 1.7;
      font-size: clamp(0.98rem, 2.8vw, 1.05rem);
      color: var(--text-soft);
      word-break: break-word;
    }
    .lesson-body > * {
      margin: 0;
    }
    .lesson-body > * + * {
      margin-top: 0.6em;
    }
    .lesson-body a {
      color: var(--accent-strong);
      text-decoration: underline;
    }
    .lesson-body img,
    .lesson-body video,
    .lesson-body iframe {
      max-width: 100%;
      border-radius: 18px;
      height: auto;
      box-shadow: 0 18px 40px -32px rgba(0,0,0,0.8);
    }
    .lesson-body pre,
    .lesson-body code {
      font-family: 'JetBrains Mono', 'Fira Code', 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    }
    .lesson-body pre {
      padding: 16px;
      background: rgba(6,14,30,0.82);
      border-radius: 16px;
      overflow-x: auto;
      font-size: 0.9rem;
    }
    .lesson-body blockquote {
      margin: 0;
      padding-left: 16px;
      border-left: 3px solid rgba(126,246,216,0.45);
      color: var(--text-strong);
    }
    .lesson-body table {
      width: 100%;
      border-collapse: collapse;
      overflow-x: auto;
      display: block;
    }
    .lesson-body table th,
    .lesson-body table td {
      padding: 10px 12px;
      border: 1px solid rgba(255,255,255,0.08);
      text-align: left;
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
    @media (max-width: 720px) {
      .module-progress-grid {
        grid-template-columns: minmax(0, 1fr);
      }
      .card {
        padding: 20px;
      }
      .lesson-body {
        font-size: 1rem;
      }
      .rich-editor-area {
        min-height: 160px;
      }
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function scheduleFlashAutoHide(flash) {
      if (!flash) return;
      setTimeout(() => {
        flash.style.transition = 'opacity .5s ease, transform .5s ease';
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-8px)';
        setTimeout(() => flash.remove(), 600);
      }, 3600);
    }

    function showToast(message, type = 'ok') {
      const flash = document.createElement('div');
      flash.className = `flash ${type}`;
      flash.textContent = `⚡ ${message}`;
      const main = document.querySelector('main') || document.body;
      if (main.firstChild) {
        main.insertBefore(flash, main.firstChild);
      } else {
        main.appendChild(flash);
      }
      scheduleFlashAutoHide(flash);
      return flash;
    }

    (function initFlash() {
      const flash = document.querySelector('.flash');
      if (flash) {
        scheduleFlashAutoHide(flash);
      }
    })();

    function initRichEditors() {
      document.querySelectorAll('textarea[data-rich-editor]').forEach((textarea) => {
        if (textarea.dataset.richReady === '1') {
          return;
        }
        textarea.dataset.richReady = '1';
        const wrapper = document.createElement('div');
        wrapper.className = 'rich-editor';
        const toolbar = document.createElement('div');
        toolbar.className = 'rich-toolbar';
        const area = document.createElement('div');
        area.className = 'rich-editor-area';
        area.contentEditable = 'true';
        area.setAttribute('role', 'textbox');
        area.setAttribute('aria-label', 'Editor de contenido enriquecido');
        const initialValue = textarea.value.trim();
        area.innerHTML = initialValue || '';
        area.dataset.placeholder = textarea.getAttribute('placeholder') || 'Empieza a escribir...';
        const commands = [
          { label: 'Negrita', icon: '<strong>B</strong>', cmd: 'bold' },
          { label: 'Cursiva', icon: '<em>I</em>', cmd: 'italic' },
          { label: 'Subrayado', icon: '<span style="text-decoration:underline">U</span>', cmd: 'underline' },
          { label: 'Lista', icon: '• Lista', cmd: 'insertUnorderedList' },
          { label: 'Numerada', icon: '1· Lista', cmd: 'insertOrderedList' },
          { label: 'Encabezado', icon: 'H2', cmd: 'formatBlock', value: 'h2' },
          { label: 'Párrafo', icon: 'P', cmd: 'formatBlock', value: 'p' },
          { label: 'Limpiar', icon: '⌫', cmd: 'removeFormat' }
        ];
        commands.forEach((action) => {
          const button = document.createElement('button');
          button.type = 'button';
          button.innerHTML = action.icon;
          button.title = action.label;
          button.setAttribute('aria-label', action.label);
          button.dataset.command = action.cmd;
          if (action.value) {
            button.dataset.value = action.value;
          }
          button.addEventListener('click', (event) => {
            event.preventDefault();
            area.focus();
            document.execCommand(action.cmd, false, action.value ?? null);
          });
          toolbar.appendChild(button);
        });
        const sync = () => {
          textarea.value = area.innerHTML.trim();
        };
        area.addEventListener('input', () => {
          if (!area.textContent.trim()) {
            area.innerHTML = '';
          }
          sync();
        });
        area.addEventListener('blur', sync);
        const form = textarea.closest('form');
        if (form) {
          form.addEventListener('submit', sync);
        }
        const parent = textarea.parentNode;
        textarea.style.display = 'none';
        parent.insertBefore(wrapper, textarea);
        wrapper.appendChild(toolbar);
        wrapper.appendChild(area);
        wrapper.appendChild(textarea);
        sync();
      });
    }

    function getLessonOrder(list) {
      return Array.from(list.querySelectorAll('[data-lesson-id]')).map((item) => Number(item.dataset.lessonId));
    }

    function getDragAfterElement(container, y) {
      const items = [...container.querySelectorAll('[data-lesson-id]:not(.dragging)')];
      return items.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        if (offset < 0 && offset > closest.offset) {
          return { offset, element: child };
        }
        return closest;
      }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
    }

    function persistLessonOrder(list, order) {
      if (!csrfToken) {
        showToast('No se pudo guardar el orden (token CSRF ausente).', 'error');
        return;
      }
      list.dataset.saving = '1';
      fetch('?a=lesson_reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          module_id: list.dataset.moduleId,
          order,
          csrf: csrfToken
        })
      })
        .then((response) => response.json().then((data) => ({ ok: response.ok, data })))
        .then(({ ok, data }) => {
          if (!ok || !data?.ok) {
            throw new Error(data?.message || 'No se pudo guardar el orden');
          }
          showToast(data.message || 'Orden de lecciones actualizado.');
        })
        .catch((error) => {
          console.error(error);
          showToast(error.message || 'No se pudo guardar el orden', 'error');
        })
        .finally(() => {
          delete list.dataset.saving;
        });
    }

    function initLessonSorting() {
      document.querySelectorAll('[data-sortable]').forEach((list) => {
        if (list.dataset.sortableReady === '1') {
          return;
        }
        list.dataset.sortableReady = '1';
        let dragItem = null;
        list.querySelectorAll('[data-lesson-id]').forEach((item) => {
          item.setAttribute('draggable', 'true');
          item.addEventListener('dragstart', (event) => {
            if (!event.target.closest('[data-drag-handle]')) {
              event.preventDefault();
              return;
            }
            dragItem = item;
            item.classList.add('dragging');
            list.dataset.initialOrder = JSON.stringify(getLessonOrder(list));
            event.dataTransfer.effectAllowed = 'move';
            try {
              event.dataTransfer.setData('text/plain', item.dataset.lessonId || '');
            } catch (err) {
              // Algunos navegadores lanzan si no se permite setData.
            }
          });
          item.addEventListener('dragend', () => {
            if (!dragItem) {
              return;
            }
            item.classList.remove('dragging');
            const previous = list.dataset.initialOrder;
            delete list.dataset.initialOrder;
            const currentOrder = getLessonOrder(list);
            dragItem = null;
            if (previous && previous !== JSON.stringify(currentOrder)) {
              persistLessonOrder(list, currentOrder);
            }
          });
        });
        list.addEventListener('dragover', (event) => {
          if (!dragItem) {
            return;
          }
          event.preventDefault();
          const afterElement = getDragAfterElement(list, event.clientY);
          if (!afterElement) {
            list.appendChild(dragItem);
          } else if (afterElement !== dragItem) {
            list.insertBefore(dragItem, afterElement);
          }
        });
      });
    }

    initRichEditors();
    initLessonSorting();
  </script>
</body>
</html>
