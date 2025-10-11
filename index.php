<?php
declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$action = $_GET['a'] ?? 'landing';

if (!current_user() && in_array($action, ['home', 'view_module', 'view_lesson', 'admin', 'users'], true)) {
    header('Location: ?a=login');
    exit;
}

function render(string $view, array $vars = []): void
{
    extract($vars);
    include __DIR__ . '/views/layout.php';
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verify_csrf_token(?string $token): void
{
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(400);
        echo 'CSRF inválido';
        exit;
    }
}

function check_csrf(): void
{
    verify_csrf_token($_POST['csrf'] ?? '');
}

switch ($action) {
    case 'landing':
        if (current_user()) {
            header('Location: ?a=home');
            exit;
        }
        render('login');
        break;

    case 'home':
        require_login();
        $isTeacher = in_array(current_user()['role'], ['teacher', 'admin'], true);
        $moduleSql = "SELECT m.*, u.name AS author, COUNT(l.id) AS lesson_count
             FROM modules m
             JOIN users u ON u.id = m.created_by
             LEFT JOIN lessons l ON l.module_id = m.id";
        if (!$isTeacher) {
            $moduleSql .= " WHERE m.is_active = 1";
        }
        $moduleSql .= " GROUP BY m.id ORDER BY m.created_at DESC";
        $modules = $pdo->query($moduleSql)->fetchAll();
        $progress = [];
        $totalLessons = 0;
        foreach ($modules as $module) {
            $totalLessons += (int)($module['lesson_count'] ?? 0);
        }
        $progressSql = "SELECT l.module_id, COUNT(*) AS completed
             FROM lesson_progress lp
             JOIN lessons l ON l.id = lp.lesson_id";
        if (!$isTeacher) {
            $progressSql .= " JOIN modules m ON m.id = l.module_id AND m.is_active = 1";
        }
        $progressSql .= " WHERE lp.user_id = ? GROUP BY l.module_id";
        $progressStmt = $pdo->prepare($progressSql);
        $progressStmt->execute([current_user()['id']]);
        $totalCompleted = 0;
        foreach ($progressStmt->fetchAll() as $row) {
            $moduleId = (int)$row['module_id'];
            $progress[$moduleId] = (int)$row['completed'];
            $totalCompleted += (int)$row['completed'];
        }
        if ($totalLessons > 0 && $totalCompleted > $totalLessons) {
            $totalCompleted = $totalLessons;
        }
        $overall = $totalLessons > 0 ? (int)round(($totalCompleted / $totalLessons) * 100) : 0;
        render('home', compact('modules', 'progress', 'totalLessons', 'totalCompleted', 'overall'));
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['pass'] ?? '';
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $u = $stmt->fetch();
            if ($u && password_verify($pass, $u['pass_hash'])) {
                $_SESSION['user'] = [
                    'id' => $u['id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'role' => $u['role'],
                ];
                header('Location: ?a=home');
                exit;
            }
            $error = 'Credenciales inválidas';
            render('login', compact('error'));
        } else {
            render('login');
        }
        break;

    case 'logout':
        session_destroy();
        header('Location: ?a=landing');
        exit;

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $email = trim($_POST['email'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $pass = $_POST['pass'] ?? '';
            if (!$email || !$name || !$pass) {
                $error = 'Rellena todos los campos';
                render('login', compact('error'));
            }
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            try {
                $pdo->prepare('INSERT INTO users (email, name, role, pass_hash) VALUES (?,?,?,?)')
                    ->execute([$email, $name, 'student', $hash]);
                flash_set('Usuario creado. Ya puedes iniciar sesión.');
                header('Location: ?a=login');
                exit;
            } catch (Throwable $e) {
                $error = 'No se pudo registrar (¿email ya en uso?)';
                render('login', compact('error'));
            }
        } else {
            http_response_code(405);
            echo 'Método no permitido';
        }
        break;

    case 'admin':
        require_login();
        require_role(['teacher', 'admin']);
        $modulesStmt = $pdo->query(
            "SELECT m.*, u.name AS author
             FROM modules m
             JOIN users u ON u.id = m.created_by
             ORDER BY m.id DESC"
        );
        $modules = $modulesStmt->fetchAll();
        $lessonsByModule = [];
        if ($modules) {
            $lessonRows = $pdo->query(
                "SELECT l.*, u.name AS author
                 FROM lessons l
                 JOIN users u ON u.id = l.created_by
                 ORDER BY l.module_id ASC, l.sort_order ASC, l.id ASC"
            )->fetchAll();
            foreach ($lessonRows as $lesson) {
                $lessonsByModule[$lesson['module_id']][] = $lesson;
            }
            foreach ($modules as &$module) {
                $module['lessons'] = $lessonsByModule[$module['id']] ?? [];
            }
            unset($module);
        }
        render('admin', compact('modules'));
        break;

    case 'users':
        require_login();
        require_role(['teacher', 'admin']);
        $modules = $pdo->query(
            "SELECT m.id, m.title, COUNT(l.id) AS lesson_count
             FROM modules m
             LEFT JOIN lessons l ON l.module_id = m.id
             GROUP BY m.id
             ORDER BY m.title ASC"
        )->fetchAll();
        $users = $pdo->query(
            "SELECT id, name, email, role, created_at
             FROM users
             ORDER BY role DESC, name ASC"
        )->fetchAll();
        $progressMatrix = [];
        if ($users && $modules) {
            $progressRows = $pdo->query(
                "SELECT lp.user_id, l.module_id, COUNT(*) AS completed
                 FROM lesson_progress lp
                 JOIN lessons l ON l.id = lp.lesson_id
                 GROUP BY lp.user_id, l.module_id"
            )->fetchAll();
            foreach ($progressRows as $row) {
                $progressMatrix[(int)$row['user_id']][(int)$row['module_id']] = (int)$row['completed'];
            }
        }
        render('users', compact('users', 'modules', 'progressMatrix'));
        break;

    case 'user_set_role':
        require_login();
        require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $uid = (int)($_POST['user_id'] ?? 0);
            $role = $_POST['role'] ?? 'student';
            if (!in_array($role, ['student', 'teacher', 'admin'], true)) {
                $role = 'student';
            }
            if ($role !== 'admin') {
                $admins = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")->fetch()['c'] ?? 1;
                $isAdmin = $pdo->prepare('SELECT role FROM users WHERE id = ?');
                $isAdmin->execute([$uid]);
                $row = $isAdmin->fetch();
                if ($admins <= 1 && $row && $row['role'] === 'admin') {
                    flash_set('Debe existir al menos un admin.', 'error');
                    header('Location: ?a=users');
                    exit;
                }
            }
            $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $uid]);
            flash_set('Rol actualizado.');
            header('Location: ?a=users');
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'user_delete':
        require_login();
        require_role(['admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $uid = (int)($_POST['user_id'] ?? 0);
            if ($uid === current_user()['id']) {
                flash_set('No puedes borrarte a ti mismo.', 'error');
                header('Location: ?a=users');
                exit;
            }
            $isAdmin = $pdo->prepare('SELECT role FROM users WHERE id = ?');
            $isAdmin->execute([$uid]);
            $row = $isAdmin->fetch();
            if ($row && $row['role'] === 'admin') {
                $admins = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'")->fetch()['c'] ?? 1;
                if ($admins <= 1) {
                    flash_set('Debe quedar al menos un admin.', 'error');
                    header('Location: ?a=users');
                    exit;
                }
            }
            $pdo->prepare('DELETE FROM comments WHERE user_id = ?')->execute([$uid]);
            $pdo->prepare('DELETE FROM lesson_progress WHERE user_id = ?')->execute([$uid]);
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]);
            flash_set('Usuario eliminado.');
            header('Location: ?a=users');
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'create_module':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $title = trim($_POST['title'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($title) {
                $pdo->prepare('INSERT INTO modules (title, description, is_active, created_by) VALUES (?,?,?,?)')
                    ->execute([$title, $desc, 1, current_user()['id']]);
                flash_set('Módulo creado.');
                header('Location: ?a=admin');
                exit;
            }
            $error = 'Título requerido';
            render('admin', compact('error'));
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'update_module':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $desc = trim($_POST['description'] ?? '');
            if ($id && $title) {
                $pdo->prepare('UPDATE modules SET title = ?, description = ? WHERE id = ?')
                    ->execute([$title, $desc, $id]);
                flash_set('Módulo actualizado.');
                header('Location: ?a=admin');
                exit;
            }
            $error = 'Faltan datos';
            render('admin', compact('error'));
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'toggle_module_active':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $id = (int)($_POST['id'] ?? 0);
            $activate = ($_POST['activate'] ?? '1') === '1';
            if ($id) {
                $pdo->prepare('UPDATE modules SET is_active = ? WHERE id = ?')
                    ->execute([$activate ? 1 : 0, $id]);
                flash_set($activate ? 'Módulo activado.' : 'Módulo desactivado.');
                header('Location: ?a=admin#module-' . $id);
                exit;
            }
            flash_set('No se pudo cambiar el estado del módulo.', 'error');
            header('Location: ?a=admin');
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'delete_module':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $ls = $pdo->prepare('SELECT id FROM lessons WHERE module_id = ?');
                $ls->execute([$id]);
                foreach ($ls->fetchAll() as $row) {
                    $pdo->prepare('DELETE FROM comments WHERE lesson_id = ?')->execute([$row['id']]);
                    $pdo->prepare('DELETE FROM lesson_progress WHERE lesson_id = ?')->execute([$row['id']]);
                }
                $pdo->prepare('DELETE FROM lessons WHERE module_id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM modules WHERE id = ?')->execute([$id]);
                flash_set('Módulo eliminado.');
            }
            header('Location: ?a=admin');
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'create_lesson':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $module_id = (int)($_POST['module_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content_html'] ?? '';
            $video = trim($_POST['video_url'] ?? '');
            if ($module_id && $title) {
                $nextOrderStmt = $pdo->prepare('SELECT COALESCE(MAX(sort_order), 0) + 1 FROM lessons WHERE module_id = ?');
                $nextOrderStmt->execute([$module_id]);
                $sortOrder = (int)$nextOrderStmt->fetchColumn();
                if ($sortOrder <= 0) {
                    $sortOrder = 1;
                }
                $pdo->prepare('INSERT INTO lessons (module_id, title, content_html, video_url, created_by, sort_order) VALUES (?,?,?,?,?,?)')
                    ->execute([$module_id, $title, $content, $video, current_user()['id'], $sortOrder]);
                flash_set('Lección creada.');
                header('Location: ?a=view_module&id=' . $module_id);
                exit;
            }
            $error = 'Faltan datos';
            render('admin', compact('error'));
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'update_lesson':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $id = (int)($_POST['id'] ?? 0);
            $module_id = (int)($_POST['module_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content_html'] ?? '';
            $video = trim($_POST['video_url'] ?? '');
            if ($id && $module_id && $title) {
                $pdo->prepare('UPDATE lessons SET title = ?, content_html = ?, video_url = ? WHERE id = ?')
                    ->execute([$title, $content, $video, $id]);
                flash_set('Lección actualizada.');
                header('Location: ?a=view_lesson&id=' . $id);
                exit;
            }
            $error = 'Faltan datos';
            render('admin', compact('error'));
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'delete_lesson':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $id = (int)($_POST['id'] ?? 0);
            $l = $pdo->prepare('SELECT id, module_id FROM lessons WHERE id = ?');
            $l->execute([$id]);
            $row = $l->fetch();
            if ($row) {
                $pdo->prepare('DELETE FROM comments WHERE lesson_id = ?')->execute([$row['id']]);
                $pdo->prepare('DELETE FROM lesson_progress WHERE lesson_id = ?')->execute([$row['id']]);
                $pdo->prepare('DELETE FROM lessons WHERE id = ?')->execute([$row['id']]);
                flash_set('Lección eliminada.');
                header('Location: ?a=view_module&id=' . $row['module_id']);
                exit;
            }
            flash_set('Lección no encontrada', 'error');
            header('Location: ?a=admin');
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'lesson_reorder':
        require_login();
        require_role(['teacher', 'admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw = file_get_contents('php://input') ?: '';
            $payload = json_decode($raw, true);
            if (!is_array($payload)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'Datos inválidos']);
                exit;
            }
            $token = $payload['csrf'] ?? null;
            if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'CSRF inválido']);
                exit;
            }
            $moduleId = (int)($payload['module_id'] ?? 0);
            $orderIds = [];
            if (is_array($payload['order'] ?? null)) {
                foreach ($payload['order'] as $value) {
                    $lessonId = (int)$value;
                    if ($lessonId > 0) {
                        $orderIds[] = $lessonId;
                    }
                }
            }
            if ($moduleId <= 0 || !$orderIds) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'Faltan datos']);
                exit;
            }
            $existingStmt = $pdo->prepare('SELECT id FROM lessons WHERE module_id = ?');
            $existingStmt->execute([$moduleId]);
            $existingIds = array_map('intval', $existingStmt->fetchAll(PDO::FETCH_COLUMN));
            if (!$existingIds) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'Módulo sin lecciones']);
                exit;
            }
            $existingSet = $existingIds;
            sort($existingSet);
            $orderSet = $orderIds;
            sort($orderSet);
            if ($existingSet !== $orderSet) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'Orden no válido']);
                exit;
            }
            try {
                $pdo->beginTransaction();
                $update = $pdo->prepare('UPDATE lessons SET sort_order = ? WHERE id = ? AND module_id = ?');
                foreach ($orderIds as $index => $lessonId) {
                    $update->execute([$index + 1, $lessonId, $moduleId]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'No se pudo actualizar el orden']);
                exit;
            }
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'message' => 'Orden de lecciones actualizado.']);
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'view_module':
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $m = $pdo->prepare(
            "SELECT m.*, u.name AS author
             FROM modules m
             JOIN users u ON u.id = m.created_by
             WHERE m.id = ?"
        );
        $m->execute([$id]);
        $module = $m->fetch();
        if (!$module) {
            http_response_code(404);
            echo 'Módulo no encontrado';
            exit;
        }
        $isTeacher = in_array(current_user()['role'], ['teacher', 'admin'], true);
        if (($module['is_active'] ?? 1) !== 1 && !$isTeacher) {
            flash_set('Este módulo no está disponible en este momento.', 'error');
            header('Location: ?a=home');
            exit;
        }
        $lessonsStmt = $pdo->prepare(
            "SELECT l.*, u.name AS author
             FROM lessons l
             JOIN users u ON u.id = l.created_by
             WHERE module_id = ?
             ORDER BY l.sort_order ASC, l.id ASC"
        );
        $lessonsStmt->execute([$id]);
        $lessons = $lessonsStmt->fetchAll();
        $completedLessons = [];
        if ($lessons) {
            $ids = array_column($lessons, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $params = array_merge([current_user()['id']], $ids);
            $completedStmt = $pdo->prepare(
                "SELECT lesson_id
                 FROM lesson_progress
                 WHERE user_id = ? AND lesson_id IN ($placeholders)"
            );
            $completedStmt->execute($params);
            $completedLessons = array_map('intval', array_column($completedStmt->fetchAll(), 'lesson_id'));
        }
        render('module', compact('module', 'lessons', 'completedLessons'));
        break;

    case 'view_lesson':
        require_login();
        $id = (int)($_GET['id'] ?? 0);
        $st = $pdo->prepare(
            "SELECT l.*, m.title AS module_title, m.is_active AS module_active, u.name AS author
             FROM lessons l
             JOIN modules m ON m.id = l.module_id
             JOIN users u ON u.id = l.created_by
             WHERE l.id = ?"
        );
        $st->execute([$id]);
        $lesson = $st->fetch();
        if (!$lesson) {
            http_response_code(404);
            echo 'Lección no encontrada';
            exit;
        }
        $isTeacher = in_array(current_user()['role'], ['teacher', 'admin'], true);
        if (($lesson['module_active'] ?? 1) !== 1 && !$isTeacher) {
            flash_set('La lección pertenece a un módulo inactivo.', 'error');
            header('Location: ?a=home');
            exit;
        }
        $c = $pdo->prepare(
            "SELECT c.*, u.name, u.role
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE lesson_id = ?
             ORDER BY c.id ASC"
        );
        $c->execute([$id]);
        $comments = $c->fetchAll();
        $isCompletedStmt = $pdo->prepare('SELECT 1 FROM lesson_progress WHERE user_id = ? AND lesson_id = ?');
        $isCompletedStmt->execute([current_user()['id'], $id]);
        $isCompleted = (bool)$isCompletedStmt->fetchColumn();
        $totalLessonsStmt = $pdo->prepare('SELECT COUNT(*) FROM lessons WHERE module_id = ?');
        $totalLessonsStmt->execute([$lesson['module_id']]);
        $totalLessons = (int)$totalLessonsStmt->fetchColumn();
        $completedCountStmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM lesson_progress lp
             JOIN lessons l ON l.id = lp.lesson_id
             WHERE lp.user_id = ? AND l.module_id = ?'
        );
        $completedCountStmt->execute([current_user()['id'], $lesson['module_id']]);
        $completedCount = (int)$completedCountStmt->fetchColumn();
        $moduleProgress = ['total' => $totalLessons, 'completed' => $completedCount];
        render('lesson', compact('lesson', 'comments', 'isCompleted', 'moduleProgress'));
        break;

    case 'lesson_progress_toggle':
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $lessonId = (int)($_POST['lesson_id'] ?? 0);
            $complete = ($_POST['complete'] ?? '0') === '1';
            if ($lessonId) {
                $lessonInfoStmt = $pdo->prepare(
                    'SELECT l.module_id, m.is_active
                     FROM lessons l
                     JOIN modules m ON m.id = l.module_id
                     WHERE l.id = ?'
                );
                $lessonInfoStmt->execute([$lessonId]);
                $lessonInfo = $lessonInfoStmt->fetch();
                if (!$lessonInfo) {
                    http_response_code(404);
                    echo 'Lección no encontrada';
                    exit;
                }
                $isTeacher = in_array(current_user()['role'], ['teacher', 'admin'], true);
                if (($lessonInfo['is_active'] ?? 1) !== 1 && !$isTeacher) {
                    flash_set('No puedes actualizar el progreso de un módulo inactivo.', 'error');
                    header('Location: ?a=home');
                    exit;
                }
                $existsStmt = $pdo->prepare('SELECT 1 FROM lesson_progress WHERE user_id = ? AND lesson_id = ?');
                $existsStmt->execute([current_user()['id'], $lessonId]);
                $exists = (bool)$existsStmt->fetchColumn();
                if ($complete && !$exists) {
                    $pdo->prepare('INSERT INTO lesson_progress (user_id, lesson_id, completed_at) VALUES (?,?,datetime("now"))')
                        ->execute([current_user()['id'], $lessonId]);
                    flash_set('Lección marcada como completada.');
                } elseif (!$complete && $exists) {
                    $pdo->prepare('DELETE FROM lesson_progress WHERE user_id = ? AND lesson_id = ?')
                        ->execute([current_user()['id'], $lessonId]);
                    flash_set('Lección marcada como pendiente.');
                }
                header('Location: ?a=view_lesson&id=' . $lessonId);
                exit;
            }
            http_response_code(400);
            echo 'Faltan datos';
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case 'comment_post':
        require_login();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $lesson_id = (int)($_POST['lesson_id'] ?? 0);
            $body = trim($_POST['body'] ?? '');
            if ($lesson_id && $body) {
                $lessonInfoStmt = $pdo->prepare(
                    'SELECT m.is_active
                     FROM lessons l
                     JOIN modules m ON m.id = l.module_id
                     WHERE l.id = ?'
                );
                $lessonInfoStmt->execute([$lesson_id]);
                $lessonInfo = $lessonInfoStmt->fetch();
                $isTeacher = in_array(current_user()['role'], ['teacher', 'admin'], true);
                if (!$lessonInfo || (($lessonInfo['is_active'] ?? 1) !== 1 && !$isTeacher)) {
                    flash_set('No puedes comentar en un módulo inactivo.', 'error');
                    header('Location: ?a=home');
                    exit;
                }
                $pdo->prepare('INSERT INTO comments (lesson_id, user_id, body) VALUES (?,?,?)')
                    ->execute([$lesson_id, current_user()['id'], $body]);
                header('Location: ?a=view_lesson&id=' . $lesson_id);
                exit;
            }
            http_response_code(400);
            echo 'Faltan datos';
            exit;
        }
        http_response_code(405);
        echo 'Método no permitido';
        break;

    default:
        http_response_code(404);
        echo 'Ruta no encontrada';
}
