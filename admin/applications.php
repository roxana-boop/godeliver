<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Aplicații Curieri';
$activeNav = 'applications';

$pdo = getDbConnection();

// ---- Quick status update / delete actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'set_status' && $id) {
        $allowed = ['new', 'in_review', 'approved', 'rejected', 'contract_sent', 'activated'];
        $status = $_POST['status'] ?? '';
        if (in_array($status, $allowed, true)) {
            $pdo->prepare('UPDATE applications SET status = :s WHERE id = :id')->execute([':s' => $status, ':id' => $id]);
        }
    } elseif ($_POST['action'] === 'delete' && $id) {
        $pdo->prepare('DELETE FROM applications WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: applications.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$sql = 'SELECT id, application_code, first_name, last_name, email, phone, city, platform, vehicle, contract_type, status, created_at FROM applications WHERE 1=1';
$params = [];
if ($statusFilter) {
    $sql .= ' AND status = :status';
    $params[':status'] = $statusFilter;
}
if ($search) {
    $sql .= ' AND (first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR application_code LIKE :q)';
    $params[':q'] = "%$search%";
}
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

$statusLabels = [
    'new' => 'Nouă', 'in_review' => 'În Verificare', 'approved' => 'Aprobată',
    'rejected' => 'Respinsă', 'contract_sent' => 'Contract Trimis', 'activated' => 'Activată',
];

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel">
  <form class="admin-toolbar" method="get">
    <input class="input admin-search" type="text" name="q" placeholder="Caută după nume, email sau cod..." value="<?= h($search) ?>">
    <select name="status" onchange="this.form.submit()">
      <option value="">Toate statusurile</option>
      <?php foreach ($statusLabels as $key => $label): ?>
        <option value="<?= h($key) ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= h($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-ghost btn-sm">Filtrează</button>
  </form>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Cod</th><th>Nume</th><th>Contact</th><th>Oraș</th><th>Platformă / Vehicul</th><th>Contract</th><th>Status</th><th>Data</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (!$applications): ?>
          <tr><td colspan="9" class="admin-empty">Nu s-au găsit aplicații.</td></tr>
        <?php endif; ?>
        <?php foreach ($applications as $app): ?>
          <tr>
            <td><a href="application_view.php?code=<?= h($app['application_code']) ?>"><strong><?= h($app['application_code']) ?></strong></a></td>
            <td><?= h($app['first_name'] . ' ' . $app['last_name']) ?></td>
            <td class="cell-muted"><?= h($app['email']) ?><br><?= h($app['phone']) ?></td>
            <td><?= h($app['city']) ?></td>
            <td class="cell-muted"><?= h($app['platform']) ?> · <?= h($app['vehicle']) ?></td>
            <td class="cell-muted"><?= h($app['contract_type']) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="id" value="<?= (int) $app['id'] ?>">
                <select name="status" onchange="this.form.submit()" class="badge badge-<?= h($app['status']) ?>" style="border:none;padding:4px 10px;">
                  <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= $app['status'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($app['created_at']))) ?></td>
            <td>
              <div class="row-actions">
                <a href="application_view.php?code=<?= h($app['application_code']) ?>" class="icon-btn" title="Vezi detalii">👁</a>
                <form method="post" onsubmit="return confirm('Ștergi definitiv această aplicație?');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $app['id'] ?>">
                  <button type="submit" class="icon-btn danger" title="Șterge">🗑</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
