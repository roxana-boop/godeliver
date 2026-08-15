<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Contractele Mele';
$activeNav = 'contracts';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT * FROM contracts WHERE courier_id = :id ORDER BY created_at DESC');
$stmt->execute([':id' => $courier['id']]);
$contracts = $stmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
?>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Contractele Tale</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Tip</th><th>Semnat</th><th>Valabil</th><th>Generat</th><th></th></tr></thead>
      <tbody>
        <?php if (!$contracts): ?>
          <tr><td colspan="5" class="admin-empty">Niciun contract disponibil încă. Contactează echipa GoDeliver dacă e nevoie de unul.</td></tr>
        <?php endif; ?>
        <?php foreach ($contracts as $ct): ?>
          <tr>
            <td><?= h($ct['contract_type']) ?></td>
            <td><span class="badge <?= $ct['signed'] ? 'badge-active' : 'badge-draft' ?>"><?= $ct['signed'] ? 'Semnat' : 'Nesemnat' ?></span></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($ct['valid_from']))) ?> → <?= h(date('d.m.Y', strtotime($ct['valid_until']))) ?></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($ct['created_at']))) ?></td>
            <td><a href="../<?= h($ct['file_path']) ?>" target="_blank" class="btn btn-ghost btn-sm">Descarcă PDF</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
