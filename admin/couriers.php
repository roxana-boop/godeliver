<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Curieri';
$activeNav = 'couriers';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'delete' && $id) {
        $pdo->prepare('DELETE FROM couriers WHERE id = :id')->execute([':id' => $id]);
        logAudit('courier_deleted', 'courier', $id);
    } elseif ($_POST['action'] === 'set_status' && $id) {
        $allowed = ['active', 'suspended', 'vacation', 'terminated'];
        $status = $_POST['status'] ?? '';
        if (in_array($status, $allowed, true)) {
            $pdo->prepare('UPDATE couriers SET status = :s WHERE id = :id')->execute([':s' => $status, ':id' => $id]);
            logAudit('courier_status_changed', 'courier', $id, $status);
        }
    }
    header('Location: couriers.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT id, courier_code, first_name, last_name, email, phone, city, platform, vehicle, contract_type, status, created_at FROM couriers WHERE 1=1';
$params = [];
if ($search) {
    $sql .= ' AND (first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR courier_code LIKE :q)';
    $params[':q'] = "%$search%";
}
$sql .= ' ORDER BY created_at DESC LIMIT 300';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$couriers = $stmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel">
  <div class="admin-toolbar">
    <form method="get" style="display:flex;gap:12px;flex:1;">
      <input class="input admin-search" type="text" name="q" placeholder="Caută curier după nume, email sau cod..." value="<?= h($search) ?>">
      <button type="submit" class="btn btn-ghost btn-sm">Caută</button>
    </form>
    <a href="courier_form.php" class="btn btn-primary btn-sm">+ Curier Nou</a>
  </div>

  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Cod</th><th>Nume</th><th>Contact</th><th>Oraș</th><th>Platformă</th><th>Contract</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (!$couriers): ?>
          <tr><td colspan="8" class="admin-empty">Niciun curier încă. Poți adăuga unul manual sau aprobă o aplicație.</td></tr>
        <?php endif; ?>
        <?php foreach ($couriers as $c): ?>
          <tr>
            <td><strong><?= h($c['courier_code']) ?></strong></td>
            <td><?= h($c['first_name'] . ' ' . $c['last_name']) ?></td>
            <td class="cell-muted"><?= h($c['email']) ?><br><?= h($c['phone']) ?></td>
            <td><?= h($c['city']) ?></td>
            <td class="cell-muted"><?= h($c['platform']) ?> · <?= h($c['vehicle']) ?></td>
            <td class="cell-muted"><?= h($c['contract_type']) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                <select name="status" onchange="this.form.submit()" class="badge badge-<?= h($c['status']) ?>" style="border:none;padding:4px 10px;">
                  <option value="active" <?= $c['status']==='active'?'selected':'' ?>>Activ</option>
                  <option value="suspended" <?= $c['status']==='suspended'?'selected':'' ?>>Suspendat</option>
                  <option value="vacation" <?= $c['status']==='vacation'?'selected':'' ?>>Concediu</option>
                  <option value="terminated" <?= $c['status']==='terminated'?'selected':'' ?>>Încetat</option>
                </select>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <a href="courier_form.php?id=<?= (int) $c['id'] ?>" class="icon-btn" title="Editează">✎</a>
                <a href="contracts.php?courier_id=<?= (int) $c['id'] ?>" class="icon-btn" title="Contract">📄</a>
                <form method="post" onsubmit="return confirm('Ștergi definitiv acest curier?');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
