<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT * FROM couriers WHERE id = :id');
$stmt->execute([':id' => $courier['id']]);
$me = $stmt->fetch();

$paidThisMonth = $pdo->prepare("
    SELECT COALESCE(SUM(net_amount),0) FROM payments
    WHERE courier_id = :id AND status = 'paid' AND MONTH(paid_at) = MONTH(CURDATE()) AND YEAR(paid_at) = YEAR(CURDATE())
");
$paidThisMonth->execute([':id' => $courier['id']]);
$monthTotal = (float) $paidThisMonth->fetchColumn();

$pendingStmt = $pdo->prepare("SELECT COALESCE(SUM(net_amount),0) FROM payments WHERE courier_id = :id AND status = 'pending'");
$pendingStmt->execute([':id' => $courier['id']]);
$pendingTotal = (float) $pendingStmt->fetchColumn();

$referralsStmt = $pdo->prepare('SELECT COUNT(*) FROM referrals WHERE referrer_courier_id = :id');
$referralsStmt->execute([':id' => $courier['id']]);
$referralCount = (int) $referralsStmt->fetchColumn();

$openTicketsStmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE courier_id = :id AND status != 'closed'");
$openTicketsStmt->execute([':id' => $courier['id']]);
$openTickets = (int) $openTicketsStmt->fetchColumn();

require __DIR__ . '/includes/layout_head.php';
?>

<div class="portal-panel" style="background:linear-gradient(135deg, rgba(255,196,0,0.08), transparent);">
  <h2 style="margin:0 0 6px;">Bine ai revenit, <?= h($me['first_name']) ?>! 👋</h2>
  <p style="margin:0;">Cod curier: <strong style="color:var(--gold);"><?= h($me['courier_code']) ?></strong> · Status:
    <span class="badge badge-<?= h($me['status']) ?>"><?= h($me['status']) ?></span>
  </p>
</div>

<div class="portal-stats">
  <div class="portal-stat-card"><div class="label">Încasat Luna Asta</div><div class="value"><?= number_format($monthTotal,0,',','.') ?> RON</div></div>
  <div class="portal-stat-card"><div class="label">În Așteptare</div><div class="value"><?= number_format($pendingTotal,0,',','.') ?> RON</div></div>
  <div class="portal-stat-card"><div class="label">Recomandări</div><div class="value"><?= $referralCount ?></div></div>
  <div class="portal-stat-card"><div class="label">Tickete Deschise</div><div class="value"><?= $openTickets ?></div></div>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Informațiile Tale</h2></div>
  <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;font-size:14px;">
    <div><span style="color:var(--text-muted);font-size:12.5px;">ORAȘ</span><br><?= h($me['city']) ?></div>
    <div><span style="color:var(--text-muted);font-size:12.5px;">PLATFORMĂ</span><br><?= h($me['platform']) ?></div>
    <div><span style="color:var(--text-muted);font-size:12.5px;">VEHICUL</span><br><?= h($me['vehicle']) ?></div>
    <div><span style="color:var(--text-muted);font-size:12.5px;">TIP CONTRACT</span><br><?= h($me['contract_type']) ?></div>
  </div>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Acces Rapid</h2></div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;">
    <a href="payments.php" class="btn btn-ghost btn-sm">💳 Vezi Plățile</a>
    <a href="documents.php" class="btn btn-ghost btn-sm">📁 Documentele Mele</a>
    <a href="referral.php" class="btn btn-ghost btn-sm">🔗 Recomandă un Prieten</a>
    <a href="support.php" class="btn btn-ghost btn-sm">🎫 Deschide Ticket</a>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
