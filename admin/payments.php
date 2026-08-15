<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Plăți';
$activeNav = 'payments';
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    if ($_POST['action'] === 'create') {
        $courierId = (int) ($_POST['courier_id'] ?? 0);
        $gross = (float) ($_POST['gross_amount'] ?? 0);
        $commission = (float) ($_POST['commission_amount'] ?? 0);
        $bonus = (float) ($_POST['bonus_amount'] ?? 0);
        $net = $gross - $commission + $bonus;
        if ($courierId && $gross > 0) {
            $pdo->prepare('
                INSERT INTO payments (courier_id, period_start, period_end, gross_amount, commission_amount, bonus_amount, net_amount, status)
                VALUES (:cid, :start, :end, :gross, :comm, :bonus, :net, "pending")
            ')->execute([
                ':cid' => $courierId, ':start' => $_POST['period_start'], ':end' => $_POST['period_end'],
                ':gross' => $gross, ':comm' => $commission, ':bonus' => $bonus, ':net' => $net,
            ]);
            logAudit('payment_created', 'courier', $courierId, "net: $net RON");
        }
    } elseif ($_POST['action'] === 'mark_paid') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = NOW() WHERE id = :id")->execute([':id' => $id]);
        logAudit('payment_marked_paid', 'payment', $id);
    } elseif ($_POST['action'] === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM payments WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: payments.php');
    exit;
}

$payments = $pdo->query('
    SELECT p.*, c.first_name, c.last_name, c.courier_code FROM payments p
    JOIN couriers c ON c.id = p.courier_id ORDER BY p.created_at DESC LIMIT 300
')->fetchAll();
$couriers = $pdo->query('SELECT id, courier_code, first_name, last_name FROM couriers WHERE status = "active" ORDER BY first_name')->fetchAll();

$totals = $pdo->query("SELECT
    SUM(CASE WHEN status = 'pending' THEN net_amount ELSE 0 END) AS pending_total,
    SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END) AS paid_total
    FROM payments")->fetch();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-stats" style="grid-template-columns:repeat(2,1fr);">
  <div class="admin-stat-card">
    <div class="label">Total în Așteptare</div>
    <div class="value"><?= number_format($totals['pending_total'] ?? 0, 0, ',', '.') ?> RON</div>
  </div>
  <div class="admin-stat-card">
    <div class="label">Total Plătit</div>
    <div class="value"><?= number_format($totals['paid_total'] ?? 0, 0, ',', '.') ?> RON</div>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Adaugă Plată</h2></div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="create">
    <div class="form-row">
      <div class="form-field"><label>Curier *</label>
        <select name="courier_id" required>
          <option value="">Alege...</option>
          <?php foreach ($couriers as $c): ?>
            <option value="<?= (int) $c['id'] ?>"><?= h($c['courier_code'] . ' — ' . $c['first_name'] . ' ' . $c['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field"><label>Perioadă Start *</label><input class="input" type="date" name="period_start" required></div>
      <div class="form-field"><label>Perioadă Sfârșit *</label><input class="input" type="date" name="period_end" required></div>
      <div class="form-field"><label>Sumă Brută (RON) *</label><input class="input" type="number" step="0.01" name="gross_amount" required></div>
      <div class="form-field"><label>Comision (RON)</label><input class="input" type="number" step="0.01" name="commission_amount" value="0"></div>
      <div class="form-field"><label>Bonus (RON)</label><input class="input" type="number" step="0.01" name="bonus_amount" value="0"></div>
    </div>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">+ Adaugă Plată</button></div>
  </form>
</div>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Istoric Plăți</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Curier</th><th>Perioadă</th><th>Brut</th><th>Comision</th><th>Bonus</th><th>Net</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$payments): ?>
          <tr><td colspan="8" class="admin-empty">Nicio plată înregistrată încă.</td></tr>
        <?php endif; ?>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><strong><?= h($p['first_name'] . ' ' . $p['last_name']) ?></strong><br><span class="cell-muted"><?= h($p['courier_code']) ?></span></td>
            <td class="cell-muted"><?= h(date('d.m', strtotime($p['period_start']))) ?> – <?= h(date('d.m.Y', strtotime($p['period_end']))) ?></td>
            <td><?= number_format($p['gross_amount'], 2) ?></td>
            <td class="cell-muted">-<?= number_format($p['commission_amount'], 2) ?></td>
            <td class="cell-muted">+<?= number_format($p['bonus_amount'], 2) ?></td>
            <td><strong><?= number_format($p['net_amount'], 2) ?> RON</strong></td>
            <td><span class="badge <?= $p['status'] === 'paid' ? 'badge-active' : 'badge-in_review' ?>"><?= $p['status'] === 'paid' ? 'Plătit' : 'În Așteptare' ?></span></td>
            <td>
              <div class="row-actions">
                <?php if ($p['status'] !== 'paid'): ?>
                <form method="post"><input type="hidden" name="csrf_token" value="<?= h($token) ?>"><input type="hidden" name="action" value="mark_paid"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="btn btn-ghost btn-sm">Marchează Plătit</button>
                </form>
                <?php endif; ?>
                <form method="post" onsubmit="return confirm('Ștergi această plată?');"><input type="hidden" name="csrf_token" value="<?= h($token) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                  <button type="submit" class="icon-btn danger">🗑</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div style="margin-top:16px;"><a href="export.php?type=payments" class="btn btn-ghost btn-sm">⬇ Exportă CSV</a></div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
