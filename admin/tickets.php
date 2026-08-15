<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Tickete Suport';
$activeNav = 'tickets';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'set_status' && $id) {
        $allowed = ['open', 'in_progress', 'closed'];
        $status = $_POST['status'] ?? '';
        if (in_array($status, $allowed, true)) {
            $pdo->prepare('UPDATE support_tickets SET status = :s WHERE id = :id')->execute([':s' => $status, ':id' => $id]);
        }
    } elseif ($_POST['action'] === 'delete' && $id) {
        $pdo->prepare('DELETE FROM support_tickets WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: tickets.php');
    exit;
}

$tickets = $pdo->query('
    SELECT t.*, c.first_name, c.last_name, c.courier_code FROM support_tickets t
    LEFT JOIN couriers c ON c.id = t.courier_id ORDER BY t.created_at DESC LIMIT 300
')->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
$statusLabels = ['open' => 'Deschis', 'in_progress' => 'În Lucru', 'closed' => 'Închis'];
?>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Tickete de la Curieri</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Curier</th><th>Subiect</th><th>Mesaj</th><th>Prioritate</th><th>Status</th><th>Data</th><th></th></tr></thead>
      <tbody>
        <?php if (!$tickets): ?>
          <tr><td colspan="7" class="admin-empty">Niciun ticket încă.</td></tr>
        <?php endif; ?>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td><?= $t['first_name'] ? h($t['first_name'] . ' ' . $t['last_name']) : '<span class="cell-muted">—</span>' ?></td>
            <td><strong><?= h($t['subject']) ?></strong></td>
            <td class="cell-muted" style="max-width:280px;"><?= h(truncateText($t['message'], 90)) ?></td>
            <td class="cell-muted"><?= h($t['priority']) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <select name="status" onchange="this.form.submit()" class="badge badge-<?= h($t['status']) ?>" style="border:none;">
                  <?php foreach ($statusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $t['status']===$k?'selected':'' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($t['created_at']))) ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Ștergi acest ticket?');">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                <button type="submit" class="icon-btn danger">🗑</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
