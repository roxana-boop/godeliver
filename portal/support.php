<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Suport';
$activeNav = 'support';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'normal';
    if ($subject !== '' && $message !== '') {
        $pdo->prepare('INSERT INTO support_tickets (courier_id, subject, message, priority, status) VALUES (:id,:s,:m,:p,"open")')
            ->execute([':id' => $courier['id'], ':s' => $subject, ':m' => $message, ':p' => $priority]);
        $success = 'Ticket-ul a fost trimis. Echipa de suport îți răspunde în cel mai scurt timp.';
    }
}

$stmt = $pdo->prepare('SELECT * FROM support_tickets WHERE courier_id = :id ORDER BY created_at DESC');
$stmt->execute([':id' => $courier['id']]);
$tickets = $stmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
$statusLabels = ['open'=>'Deschis','in_progress'=>'În Lucru','closed'=>'Închis'];
?>

<?php if ($success): ?><div class="admin-alert admin-alert-success"><?= h($success) ?></div><?php endif; ?>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Deschide un Ticket Nou</h2></div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <div class="form-row">
      <div class="form-field"><label>Subiect</label><input class="input" name="subject" required></div>
      <div class="form-field"><label>Prioritate</label>
        <select name="priority">
          <option value="low">Scăzută</option><option value="normal" selected>Normală</option>
          <option value="high">Ridicată</option><option value="urgent">Urgentă</option>
        </select>
      </div>
    </div>
    <div class="form-field"><label>Mesaj</label><textarea class="input" name="message" rows="4" required></textarea></div>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Trimite Ticket-ul</button></div>
  </form>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Ticketele Mele</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Subiect</th><th>Prioritate</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
        <?php if (!$tickets): ?><tr><td colspan="4" class="admin-empty">Niciun ticket trimis încă.</td></tr><?php endif; ?>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><strong><?= h($t['subject']) ?></strong></td>
            <td class="cell-muted"><?= h($t['priority']) ?></td>
            <td><span class="badge badge-<?= $t['status']==='closed'?'terminated':($t['status']==='open'?'new':'in_review') ?>"><?= h($statusLabels[$t['status']]) ?></span></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($t['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
