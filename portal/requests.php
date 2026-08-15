<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Concediu & Echipament';
$activeNav = 'requests';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = $_POST['action'] ?? '';
    if ($action === 'request_vacation') {
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        if ($start && $end) {
            $pdo->prepare('INSERT INTO vacation_requests (courier_id, start_date, end_date, reason, status) VALUES (:id,:s,:e,:r,"pending")')
                ->execute([':id' => $courier['id'], ':s' => $start, ':e' => $end, ':r' => $reason]);
            $success = 'Cererea de concediu a fost trimisă spre aprobare.';
        }
    } elseif ($action === 'request_equipment') {
        $item = trim($_POST['item'] ?? '');
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');
        if ($item) {
            $pdo->prepare('INSERT INTO equipment_requests (courier_id, item, quantity, notes, status) VALUES (:id,:item,:qty,:notes,"pending")')
                ->execute([':id' => $courier['id'], ':item' => $item, ':qty' => $qty, ':notes' => $notes]);
            $success = 'Cererea de echipament a fost trimisă.';
        }
    }
}

$vacStmt = $pdo->prepare('SELECT * FROM vacation_requests WHERE courier_id = :id ORDER BY created_at DESC');
$vacStmt->execute([':id' => $courier['id']]);
$vacations = $vacStmt->fetchAll();

$eqStmt = $pdo->prepare('SELECT * FROM equipment_requests WHERE courier_id = :id ORDER BY created_at DESC');
$eqStmt->execute([':id' => $courier['id']]);
$equipment = $eqStmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
$statusLabels = ['pending'=>'În Așteptare','approved'=>'Aprobat','rejected'=>'Respins','fulfilled'=>'Livrat'];
?>

<?php if ($success): ?><div class="admin-alert admin-alert-success"><?= h($success) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
  <div>
    <div class="portal-panel">
      <div class="portal-panel-head"><h2>Cerere Concediu</h2></div>
      <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="request_vacation">
        <div class="form-row">
          <div class="form-field"><label>De la</label><input class="input" type="date" name="start_date" required></div>
          <div class="form-field"><label>Până la</label><input class="input" type="date" name="end_date" required></div>
        </div>
        <div class="form-field"><label>Motiv (opțional)</label><textarea class="input" name="reason" rows="2"></textarea></div>
        <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Trimite Cererea</button></div>
      </form>
    </div>
    <div class="portal-panel">
      <div class="portal-panel-head"><h2>Istoric Concedii</h2></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>Perioadă</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (!$vacations): ?><tr><td colspan="2" class="admin-empty">Nicio cerere încă.</td></tr><?php endif; ?>
            <?php foreach ($vacations as $v): ?>
              <tr><td class="cell-muted"><?= h(date('d.m.Y',strtotime($v['start_date']))) ?> – <?= h(date('d.m.Y',strtotime($v['end_date']))) ?></td>
              <td><span class="badge badge-<?= $v['status']==='pending'?'in_review':($v['status']==='approved'?'active':'rejected') ?>"><?= h($statusLabels[$v['status']]) ?></span></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="portal-panel">
      <div class="portal-panel-head"><h2>Cerere Echipament</h2></div>
      <form method="post" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="request_equipment">
        <div class="form-row">
          <div class="form-field"><label>Articol</label>
            <select name="item">
              <option>Geantă termică</option><option>Vestă reflectorizantă</option>
              <option>Suport telefon</option><option>Cască/mănuși</option><option>Altele</option>
            </select>
          </div>
          <div class="form-field"><label>Cantitate</label><input class="input" type="number" name="quantity" value="1" min="1"></div>
        </div>
        <div class="form-field"><label>Note (opțional)</label><textarea class="input" name="notes" rows="2"></textarea></div>
        <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Trimite Cererea</button></div>
      </form>
    </div>
    <div class="portal-panel">
      <div class="portal-panel-head"><h2>Istoric Echipament</h2></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>Articol</th><th>Cant.</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (!$equipment): ?><tr><td colspan="3" class="admin-empty">Nicio cerere încă.</td></tr><?php endif; ?>
            <?php foreach ($equipment as $e): ?>
              <tr><td><?= h($e['item']) ?></td><td class="cell-muted"><?= (int)$e['quantity'] ?></td>
              <td><span class="badge badge-<?= $e['status']==='pending'?'in_review':($e['status']==='fulfilled'||$e['status']==='approved'?'active':'rejected') ?>"><?= h($statusLabels[$e['status']]) ?></span></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
