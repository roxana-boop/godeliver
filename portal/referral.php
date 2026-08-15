<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Recomandă un Prieten';
$activeNav = 'referral';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT referral_code FROM couriers WHERE id = :id');
$stmt->execute([':id' => $courier['id']]);
$referralCode = $stmt->fetchColumn();

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $email = trim($_POST['referred_email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pdo->prepare('INSERT INTO referrals (referrer_courier_id, referred_email, status) VALUES (:id, :email, "pending")')
            ->execute([':id' => $courier['id'], ':email' => $email]);
        $success = 'Recomandarea a fost înregistrată! Te anunțăm când prietenul tău devine curier activ.';
    }
}

$refStmt = $pdo->prepare('SELECT * FROM referrals WHERE referrer_courier_id = :id ORDER BY created_at DESC');
$refStmt->execute([':id' => $courier['id']]);
$referrals = $refStmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
$statusLabels = ['pending' => 'În Așteptare', 'activated' => 'Activat', 'rewarded' => 'Recompensat'];
?>

<?php if ($success): ?><div class="admin-alert admin-alert-success"><?= h($success) ?></div><?php endif; ?>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Codul Tău de Recomandare</h2></div>
  <div class="referral-code-box">
    <div class="code"><?= h($referralCode) ?></div>
    <div style="font-size:13.5px;color:var(--text-secondary);">Trimite acest cod prietenilor tăi. Pentru fiecare curier activat prin recomandarea ta, primești un bonus.</div>
  </div>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Recomandă prin Email</h2></div>
  <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input class="input" type="email" name="referred_email" placeholder="email@prieten.ro" required style="flex:1;min-width:220px;">
    <button type="submit" class="btn btn-primary">Trimite Recomandarea</button>
  </form>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Recomandările Tale</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Email</th><th>Status</th><th>Data</th></tr></thead>
      <tbody>
        <?php if (!$referrals): ?><tr><td colspan="3" class="admin-empty">Nicio recomandare încă.</td></tr><?php endif; ?>
        <?php foreach ($referrals as $r): ?>
          <tr>
            <td><?= h($r['referred_email'] ?: '—') ?></td>
            <td><span class="badge badge-<?= $r['status']==='pending'?'in_review':'active' ?>"><?= h($statusLabels[$r['status']] ?? $r['status']) ?></span></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($r['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
