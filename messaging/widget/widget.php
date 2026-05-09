<?php
// Floating chat: bubble bottom-right, click opens a panel that embeds the
// messages.php page in an iframe. All real logic lives in messages.php.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msg_user_id = $_SESSION['user_id'] ?? $_SESSION['teacher_id'] ?? null;
if (!$msg_user_id) {
    return;
}

// Auto-detect URL prefix so this works whether AMS is served at /AMS or some
// other path. Uses DOCUMENT_ROOT vs this file's path.
$msg_doc_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$msg_file_dir = realpath(__DIR__);            // .../AMS/messaging/widget
$msg_base     = '/AMS';                       // sensible default
if ($msg_doc_root && $msg_file_dir && strpos($msg_file_dir, $msg_doc_root) === 0) {
    $rel = str_replace('\\', '/', substr($msg_file_dir, strlen($msg_doc_root)));
    $msg_base = dirname(dirname($rel));        // strips /messaging/widget
    if ($msg_base === '/' || $msg_base === '\\' || $msg_base === '.') $msg_base = '';
}

// Unread badge count.
$unread = 0;
try {
    require_once __DIR__ . '/../../config/db.php';
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([(int) $msg_user_id]);
    $unread = (int) $stmt->fetchColumn();
} catch (Throwable $e) { /* table may not exist yet — fine */ }
?>
<style>
  #ams-msg-bubble {
    position: fixed; bottom: 20px; right: 20px; z-index: 99999;
    width: 56px; height: 56px; border-radius: 50%;
    background: #2563eb; color: #fff; border: 0;
    cursor: pointer; font-size: 26px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 16px rgba(0,0,0,.25);
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  }
  #ams-msg-bubble:hover { background: #1d4ed8; }
  #ams-msg-bubble .ams-badge {
    position: absolute; top: -4px; right: -4px;
    background: #ef4444; color: #fff;
    font-size: 11px; min-width: 18px; height: 18px; line-height: 18px;
    padding: 0 5px; border-radius: 9px; font-weight: 600;
    box-sizing: border-box;
  }
  #ams-msg-frame-wrap {
    position: fixed; bottom: 88px; right: 20px; z-index: 99998;
    width: 600px; height: 830px; max-height: calc(100vh - 110px);
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.18);
    display: none;
  }
  #ams-msg-frame-wrap.ams-open { display: block; }
  #ams-msg-frame { width: 100%; height: 100%; border: 0; display: block; }
  @media (max-width: 480px) {
    #ams-msg-frame-wrap { width: calc(100vw - 20px); right: 10px; bottom: 80px; }
  }
</style>

<button id="ams-msg-bubble" type="button" title="Messages"
        onclick="(function(w){w.classList.toggle('ams-open');var f=document.getElementById('ams-msg-frame');if(w.classList.contains('ams-open')&&!f.dataset.loaded){f.src='<?= htmlspecialchars($msg_base) ?>/messaging/messages.php?embed=1';f.dataset.loaded='1';}})(document.getElementById('ams-msg-frame-wrap'))">
  &#128172;
  <?php if ($unread > 0): ?>
    <span class="ams-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
  <?php endif; ?>
</button>

<div id="ams-msg-frame-wrap">
  <iframe id="ams-msg-frame" src="about:blank" title="Messages"></iframe>
</div>
