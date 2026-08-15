<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Rapoarte';
$activeNav = 'reports';
$pdo = getDbConnection();

$byCity = $pdo->query("SELECT city, COUNT(*) AS total FROM couriers WHERE status='active' GROUP BY city ORDER BY total DESC LIMIT 10")->fetchAll();
$byPlatform = $pdo->query("SELECT platform, COUNT(*) AS total FROM couriers WHERE status='active' GROUP BY platform ORDER BY total DESC")->fetchAll();
$appsByStatus = $pdo->query("SELECT status, COUNT(*) AS total FROM applications GROUP BY status")->fetchAll();
$monthlyPayments = $pdo->query("
    SELECT DATE_FORMAT(period_start, '%Y-%m') AS ym, SUM(net_amount) AS total
    FROM payments GROUP BY ym ORDER BY ym DESC LIMIT 6
")->fetchAll();

$maxCity = max(array_column($byCity, 'total') ?: [1]);
$maxMonth = max(array_column($monthlyPayments, 'total') ?: [1]);

require __DIR__ . '/includes/layout_head.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Curieri Activi pe Oraș</h2></div>
    <?php if (!$byCity): ?><p class="admin-empty">Nicio dată încă.</p><?php endif; ?>
    <?php foreach ($byCity as $row): ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
          <span><?= h($row['city']) ?></span><strong><?= (int) $row['total'] ?></strong>
        </div>
        <div style="background:var(--surface-2);border-radius:6px;height:8px;overflow:hidden;">
          <div style="background:var(--gold);height:100%;width:<?= max(4, round($row['total'] / $maxCity * 100)) ?>%;"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Distribuție pe Platformă</h2></div>
    <?php if (!$byPlatform): ?><p class="admin-empty">Nicio dată încă.</p><?php endif; ?>
    <?php $totalP = array_sum(array_column($byPlatform,'total')) ?: 1; foreach ($byPlatform as $row): ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
          <span><?= h($row['platform']) ?></span><strong><?= (int) $row['total'] ?> (<?= round($row['total']/$totalP*100) ?>%)</strong>
        </div>
        <div style="background:var(--surface-2);border-radius:6px;height:8px;overflow:hidden;">
          <div style="background:var(--gold);height:100%;width:<?= round($row['total']/$totalP*100) ?>%;"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Aplicații pe Status</h2></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Status</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach ($appsByStatus as $row): ?>
            <tr><td><span class="badge badge-<?= h($row['status']) ?>"><?= h($row['status']) ?></span></td><td><?= (int) $row['total'] ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Plăți Nete pe Lună (ultimele 6)</h2></div>
    <?php if (!$monthlyPayments): ?><p class="admin-empty">Nicio plată înregistrată încă.</p><?php endif; ?>
    <?php foreach ($monthlyPayments as $row): ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
          <span><?= h($row['ym']) ?></span><strong><?= number_format($row['total'],0,',','.') ?> RON</strong>
        </div>
        <div style="background:var(--surface-2);border-radius:6px;height:8px;overflow:hidden;">
          <div style="background:var(--gold);height:100%;width:<?= max(4, round($row['total'] / $maxMonth * 100)) ?>%;"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Exporturi</h2></div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="export.php?type=applications" class="btn btn-ghost btn-sm">⬇ Aplicații (CSV)</a>
    <a href="export.php?type=couriers" class="btn btn-ghost btn-sm">⬇ Curieri (CSV)</a>
    <a href="export.php?type=payments" class="btn btn-ghost btn-sm">⬇ Plăți (CSV)</a>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
