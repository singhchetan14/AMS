<?php
// Simple messaging page. No AJAX — every action is a regular form submit.
// Works with both student session (user_id) and teacher session (teacher_id).

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

$me_id = $_SESSION['user_id'] ?? $_SESSION['teacher_id'] ?? null;
if (!$me_id) {
    header('Location: /AMS/index.php');
    exit;
}
$me_id = (int) $me_id;

// Auto-create the messages table on first hit so this works without manual SQL.
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
          `id`          INT(11)      NOT NULL AUTO_INCREMENT,
          `sender_id`   INT(11)      NOT NULL,
          `receiver_id` INT(11)      NOT NULL,
          `body`        TEXT         NOT NULL,
          `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
          `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `idx_pair` (`sender_id`, `receiver_id`, `created_at`),
          KEY `idx_receiver` (`receiver_id`, `is_read`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (Throwable $e) { /* surfaces on the next query if it really failed */ }

// ── Handle send (POST) ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to   = (int) ($_POST['to']   ?? 0);
    $body = trim($_POST['body']   ?? '');
    if ($to && $body !== '' && $to !== $me_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
        $stmt->execute([$me_id, $to, $body]);
    }
    header('Location: messages.php?to=' . $to);
    exit;
}

// ── Active thread ───────────────────────────────────────────────────
$activeId   = (int) ($_GET['to'] ?? 0);
$activeUser = null;
$thread     = [];

if ($activeId) {
    // Mark messages from peer → me as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0")
        ->execute([$activeId, $me_id]);

    $stmt = $pdo->prepare("SELECT id, full_name, email, role FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$activeId]);
    $activeUser = $stmt->fetch();

    if ($activeUser) {
        $stmt = $pdo->prepare("
            SELECT id, sender_id, body, created_at
              FROM messages
             WHERE (sender_id = ? AND receiver_id = ?)
                OR (sender_id = ? AND receiver_id = ?)
             ORDER BY created_at ASC, id ASC
        ");
        $stmt->execute([$me_id, $activeId, $activeId, $me_id]);
        $thread = $stmt->fetchAll();
    }
}

// ── Sidebar: search results OR conversation list ────────────────────
$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT id, full_name, email, role
          FROM users
         WHERE id <> ?
           AND (full_name LIKE ? OR email LIKE ?)
         ORDER BY full_name ASC, email ASC
         LIMIT 20
    ");
    $stmt->execute([$me_id, "%$q%", "%$q%"]);
    $sidebar      = $stmt->fetchAll();
    $sidebarLabel = 'Search results';
} else {
    // Note: PDO with EMULATE_PREPARES=false requires each placeholder to be
    // unique, so we use positional `?` and repeat $me_id once per occurrence.
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, u.email, u.role,
            (SELECT body FROM messages m
              WHERE (m.sender_id = u.id AND m.receiver_id = ?)
                 OR (m.sender_id = ? AND m.receiver_id = u.id)
              ORDER BY m.created_at DESC, m.id DESC LIMIT 1) AS last_body,
            (SELECT MAX(created_at) FROM messages m
              WHERE (m.sender_id = u.id AND m.receiver_id = ?)
                 OR (m.sender_id = ? AND m.receiver_id = u.id)) AS last_at,
            (SELECT COUNT(*) FROM messages m
              WHERE m.sender_id = u.id
                AND m.receiver_id = ?
                AND m.is_read = 0) AS unread
          FROM users u
         WHERE u.id IN (
            SELECT receiver_id FROM messages WHERE sender_id = ?
            UNION
            SELECT sender_id   FROM messages WHERE receiver_id = ?
         )
         ORDER BY last_at DESC
    ");
    $stmt->execute([$me_id, $me_id, $me_id, $me_id, $me_id, $me_id, $me_id]);
    $sidebar      = $stmt->fetchAll();
    $sidebarLabel = 'Conversations';
}

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function initial($name, $email) {
    $s = trim($name ?: $email ?: '?');
    return strtoupper(mb_substr($s, 0, 1));
}

// When loaded inside the floating widget's iframe (?embed=1), use compact
// styling that fills the panel instead of the centered desktop layout.
$embed = isset($_GET['embed']) && $_GET['embed'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages — AMS</title>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; }
  .wrap { max-width: 1100px; margin: 20px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.08); height: calc(100vh - 40px); display: flex; }
  .sidebar { width: 320px; border-right: 1px solid #e5e7eb; display: flex; flex-direction: column; min-width: 0; }
  .sidebar-header { padding: 14px; background: #2563eb; color: #fff; }
  .sidebar-header h2 { margin: 0 0 10px; font-size: 16px; font-weight: 600; }
  .sidebar-header form input { width: 100%; padding: 8px 10px; border: 0; border-radius: 6px; font-size: 13px; outline: none; }
  .sidebar-list { flex: 1; overflow-y: auto; }
  .sidebar-label { padding: 12px 14px 6px; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600; letter-spacing: .05em; }
  .contact { display: flex; align-items: center; gap: 10px; padding: 10px 14px; text-decoration: none; color: inherit; border-bottom: 1px solid #f3f4f6; }
  .contact:hover { background: #f9fafb; }
  .contact.active { background: #eff6ff; }
  .avatar { width: 36px; height: 36px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #4b5563; font-size: 14px; flex-shrink: 0; }
  .contact-meta { flex: 1; min-width: 0; }
  .contact-name { font-weight: 600; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .contact-snippet { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .role { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-left: 6px; letter-spacing: .04em; }
  .unread { background: #ef4444; color: #fff; font-size: 10px; min-width: 18px; height: 18px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; padding: 0 5px; font-weight: 600; }
  .back-link { display: block; padding: 12px 14px; text-align: center; color: #2563eb; text-decoration: none; font-size: 13px; border-top: 1px solid #e5e7eb; }
  .back-link:hover { background: #f9fafb; }

  .thread-pane { flex: 1; display: flex; flex-direction: column; min-width: 0; }
  .thread-header { padding: 14px 18px; border-bottom: 1px solid #e5e7eb; font-weight: 600; font-size: 14px; }
  .thread-body { flex: 1; padding: 16px; overflow-y: auto; background: #f9fafb; display: flex; flex-direction: column; gap: 6px; }
  .bubble { max-width: 75%; padding: 8px 12px; border-radius: 14px; font-size: 13px; line-height: 1.4; word-wrap: break-word; white-space: pre-wrap; }
  .bubble.me { align-self: flex-end; background: #2563eb; color: #fff; border-bottom-right-radius: 4px; }
  .bubble.them { align-self: flex-start; background: #fff; color: #111827; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
  .send-form { display: flex; gap: 6px; padding: 12px; border-top: 1px solid #e5e7eb; background: #fff; }
  .send-form input { flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; outline: none; }
  .send-form input:focus { border-color: #2563eb; }
  .send-form button { padding: 0 20px; background: #2563eb; color: #fff; border: 0; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; }
  .send-form button:hover { background: #1d4ed8; }
  .empty { padding: 40px 20px; text-align: center; color: #9ca3af; font-size: 13px; }
  .empty-center { flex: 1; display: flex; align-items: center; justify-content: center; background: #f9fafb; }
<?php if ($embed): ?>
  /* Compact mode for floating-widget iframe: fill 100% and stack sidebar over thread. */
  body { background: #fff; }
  .wrap { margin: 0; height: 100vh; max-width: none; box-shadow: none; border-radius: 0; flex-direction: column; }
  .sidebar { width: 100%; max-height: 45%; border-right: 0; border-bottom: 1px solid #e5e7eb; }
  .sidebar-header { padding: 10px; }
  .sidebar-header h2 { font-size: 14px; margin-bottom: 6px; }
  .back-link { display: none; }
  .thread-pane { flex: 1; min-height: 0; }
  .thread-header { padding: 10px 14px; font-size: 13px; }
  .thread-body { padding: 10px; }
  .send-form { padding: 8px; }
<?php endif; ?>
</style>
</head>
<body>
<div class="wrap">
  <aside class="sidebar">
    <div class="sidebar-header">
      <h2>Messages</h2>
      <form method="get" action="messages.php">
        <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search by name or email…" autocomplete="off" autofocus>
        <?php if ($activeId): ?><input type="hidden" name="to" value="<?= $activeId ?>"><?php endif; ?>
      </form>
    </div>

    <div class="sidebar-list">
      <div class="sidebar-label"><?= h($sidebarLabel) ?></div>
      <?php if (!$sidebar): ?>
        <div class="empty">
          <?= $q !== '' ? 'No users found for "' . h($q) . '".' : 'No conversations yet. Search to start one.' ?>
        </div>
      <?php else: foreach ($sidebar as $c):
            $name = $c['full_name'] ?: $c['email'];
            $isActive = ((int) $c['id']) === $activeId;
      ?>
        <a class="contact <?= $isActive ? 'active' : '' ?>" href="messages.php?to=<?= (int) $c['id'] ?>">
          <div class="avatar"><?= h(initial($c['full_name'], $c['email'])) ?></div>
          <div class="contact-meta">
            <div class="contact-name">
              <?= h($name) ?><span class="role"><?= h($c['role']) ?></span>
            </div>
            <div class="contact-snippet">
              <?= h($c['last_body'] ?? $c['email']) ?>
            </div>
          </div>
          <?php if (!empty($c['unread']) && (int) $c['unread'] > 0): ?>
            <span class="unread"><?= (int) $c['unread'] ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; endif; ?>
    </div>

    <a class="back-link" href="javascript:history.back()">&larr; Back to dashboard</a>
  </aside>

  <main class="thread-pane">
    <?php if (!$activeUser): ?>
      <div class="empty-center">
        <div class="empty">Select a user from the left,<br>or search above to start a new chat.</div>
      </div>
    <?php else: ?>
      <div class="thread-header">
        <?= h($activeUser['full_name'] ?: $activeUser['email']) ?>
        <span class="role"><?= h($activeUser['role']) ?></span>
      </div>

      <div class="thread-body" id="thread-body">
        <?php if (!$thread): ?>
          <div class="empty">No messages yet — say hi.</div>
        <?php else: foreach ($thread as $m): ?>
          <div class="bubble <?= ((int) $m['sender_id']) === $me_id ? 'me' : 'them' ?>">
            <?= h($m['body']) ?>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <form class="send-form" method="post" action="messages.php">
        <input type="hidden" name="to" value="<?= $activeId ?>">
        <input type="text" name="body" placeholder="Type a message…" autocomplete="off" required autofocus>
        <button type="submit">Send</button>
      </form>
    <?php endif; ?>
  </main>
</div>

<script>
  // Auto-scroll thread to bottom on load.
  var t = document.getElementById('thread-body');
  if (t) t.scrollTop = t.scrollHeight;
</script>
</body>
</html>
