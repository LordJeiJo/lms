<?php
declare(strict_types=1);

$baseDir = __DIR__;
$dataDir = $baseDir . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($dataDir)) {
  @mkdir($dataDir, 0775, true);
}
if (!is_dir($dataDir) && is_writable(sys_get_temp_dir())) {
  $dataDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'lms-lite';
  @mkdir($dataDir, 0775, true);
}

$dbFile = $dataDir . DIRECTORY_SEPARATOR . 'lms.sqlite';
$firstRun = !file_exists($dbFile);

try {
  $pdo = new PDO('sqlite:' . $dbFile, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  $pdo->exec("PRAGMA journal_mode = WAL; PRAGMA synchronous = NORMAL;");
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Error de base de datos: ' . htmlspecialchars($e->getMessage());
  exit;
}

if ($firstRun) {
  $pdo->exec(<<<SQL
  CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT UNIQUE NOT NULL,
    name TEXT NOT NULL,
    role TEXT NOT NULL CHECK(role IN ('teacher','student','admin')),
    pass_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
  );
  CREATE TABLE IF NOT EXISTS modules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT DEFAULT '',
    is_active INTEGER NOT NULL DEFAULT 1,
    created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY(created_by) REFERENCES users(id)
  );
  CREATE TABLE IF NOT EXISTS lessons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    module_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content_html TEXT DEFAULT '',
    video_url TEXT DEFAULT '',
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY(module_id) REFERENCES modules(id),
    FOREIGN KEY(created_by) REFERENCES users(id)
  );
  CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lesson_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY(lesson_id) REFERENCES lessons(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
  );
  CREATE TABLE IF NOT EXISTS lesson_progress (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    lesson_id INTEGER NOT NULL,
    completed_at TEXT NOT NULL DEFAULT (datetime('now')),
    UNIQUE(user_id, lesson_id),
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(lesson_id) REFERENCES lessons(id)
  );
SQL);

  $pass = password_hash('admin', PASSWORD_DEFAULT);
  $stmt = $pdo->prepare('INSERT OR IGNORE INTO users (email,name,role,pass_hash) VALUES (?,?,?,?)');
  $stmt->execute(['admin@example.com', 'Admin', 'admin', $pass]);
}

$moduleColumns = $pdo->query("PRAGMA table_info(modules)")->fetchAll();
$hasModuleActive = false;
foreach ($moduleColumns as $column) {
  if (($column['name'] ?? '') === 'is_active') {
    $hasModuleActive = true;
    break;
  }
}

if (!$hasModuleActive) {
  try {
    $pdo->exec("ALTER TABLE modules ADD COLUMN is_active INTEGER NOT NULL DEFAULT 1");
  } catch (Throwable $e) {
    // Column already exists or cannot be created – continue gracefully.
  }
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS lesson_progress (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  lesson_id INTEGER NOT NULL,
  completed_at TEXT NOT NULL DEFAULT (datetime('now')),
  UNIQUE(user_id, lesson_id),
  FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(lesson_id) REFERENCES lessons(id)
);
SQL);

// Ensure lessons table has a stable sort order column
$hasSortOrder = false;
$columns = $pdo->query("PRAGMA table_info(lessons)")->fetchAll();
foreach ($columns as $column) {
  if (($column['name'] ?? '') === 'sort_order') {
    $hasSortOrder = true;
    break;
  }
}

if (!$hasSortOrder) {
  try {
    $pdo->exec("ALTER TABLE lessons ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0");
    $hasSortOrder = true;
  } catch (Throwable $e) {
    // Column already exists or cannot be created – continue gracefully.
  }
}

if ($hasSortOrder) {
  $needsOrdering = (int)($pdo->query("SELECT COUNT(*) FROM lessons WHERE sort_order IS NULL OR sort_order = 0")->fetchColumn() ?: 0);
  if ($needsOrdering > 0) {
    $moduleIds = $pdo->query("SELECT DISTINCT module_id FROM lessons")->fetchAll(PDO::FETCH_COLUMN);
    $fetchLessons = $pdo->prepare("SELECT id FROM lessons WHERE module_id = ? ORDER BY created_at ASC, id ASC");
    $updateOrder = $pdo->prepare("UPDATE lessons SET sort_order = ? WHERE id = ?");
    foreach ($moduleIds as $moduleId) {
      $fetchLessons->execute([$moduleId]);
      $rows = $fetchLessons->fetchAll(PDO::FETCH_COLUMN);
      $position = 1;
      foreach ($rows as $lessonId) {
        $updateOrder->execute([$position++, $lessonId]);
      }
    }
  }
}
