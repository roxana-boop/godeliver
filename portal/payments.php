<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Plățile Mele';
$activeNav = 'payments';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT * FROM payments WHERE courier_id = :id ORDER BY period_start DESC');
$stmt->execute([':id' => $courier['id']]);
$payments = $stmt->fetchAll();

$totalPaid = array_sum(array_map(fn($p) => $p['status'] === 'paid' ? (float) $p['net_amount'] : 0, $payments));

require __DIR__ . '/includes/layout_head.php';
?>

<div class="portal-stats" style="grid-template-columns:repeat(2,1fr);">
  <div class="portal-stat-card"><div class="label">Total Încasat (all-time)</div><div class="value"><?= number_format($totalPaid,0,',','.') ?> RON</div></div>
  <div class="portal-stat-card"><div class="label">Număr Plăți</div><div class="value"><?= count($payments) ?></div></div>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Istoric Plăți</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Perioadă</th><th>Brut</th><th>Comision</th><th>Bonus</th><th>Net</th><th>Status</th></tr></thead>
      <tbody>
        <?php if (!$payments): ?><tr><td colspan="6" class="admin-empty">Nicio plată înregistrată încă.</td></tr><?php endif; ?>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= h(date('d.m', strtotime($p['period_start']))) ?> – <?= h(date('d.m.Y', strtotime($p['period_end']))) ?></td>
            <td><?= number_format($p['gross_amount'],2) ?></td>
            <td class="cell-muted">-<?= number_format($p['commission_amount'],2) ?></td>
            <td class="cell-muted">+<?= number_format($p['bonus_amount'],2) ?></td>
            <td><strong><?= number_format($p['net_amount'],2) ?> RON</strong></td>
            <td><span class="badge <?= $p['status']==='paid'?'badge-active':'badge-in_review' ?>"><?= $p['status']==='paid'?'Plătit':'În Așteptare' ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
