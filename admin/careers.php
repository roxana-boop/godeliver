<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Cariere';
$activeNav = 'careers';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'delete' && $id) {
        $pdo->prepare('DELETE FROM job_listings WHERE id = :id')->execute([':id' => $id]);
    } elseif ($_POST['action'] === 'toggle_active' && $id) {
        $pdo->prepare('UPDATE job_listings SET active = 1 - active WHERE id = :id')->execute([':id' => $id]);
    } elseif ($_POST['action'] === 'create') {
        $title = trim($_POST['title'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $city = trim($_POST['city'] ?? 'Remote');
        $type = trim($_POST['employment_type'] ?? 'Full-time');
        $description = trim($_POST['description'] ?? '');
        if ($title !== '' && $department !== '') {
            $pdo->prepare('INSERT INTO job_listings (title, department, city, employment_type, description, active) VALUES (:t,:d,:c,:e,:desc,1)')
                ->execute([':t'=>$title, ':d'=>$department, ':c'=>$city, ':e'=>$type, ':desc'=>$description]);
        }
    }
    header('Location: careers.php');
    exit;
}

$jobs = $pdo->query('SELECT * FROM job_listings ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Adaugă Job Nou</h2></div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="create">
    <div class="form-row">
      <div class="form-field"><label>Titlu Job *</label><input class="input" name="title" required></div>
      <div class="form-field"><label>Departament *</label><input class="input" name="department" required></div>
      <div class="form-field"><label>Oraș</label><input class="input" name="city" value="Remote"></div>
      <div class="form-field"><label>Tip Angajare</label>
        <select name="employment_type">
          <option>Full-time</option><option>Part-time</option><option>Colaborare</option>
        </select>
      </div>
    </div>
    <div class="form-field"><label>Descriere</label><textarea class="input" name="description" rows="3"></textarea></div>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">+ Adaugă Job</button></div>
  </form>
</div>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Joburi Publicate</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Titlu</th><th>Departament</th><th>Oraș</th><th>Tip</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$jobs): ?>
          <tr><td colspan="6" class="admin-empty">Niciun job listat încă.</td></tr>
        <?php endif; ?>
        <?php foreach ($jobs as $j): ?>
          <tr>
            <td><strong><?= h($j['title']) ?></strong></td>
            <td><?= h($j['department']) ?></td>
            <td class="cell-muted"><?= h($j['city']) ?></td>
            <td class="cell-muted"><?= h($j['employment_type']) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="toggle_active">
                <input type="hidden" name="id" value="<?= (int) $j['id'] ?>">
                <button type="submit" class="badge <?= $j['active'] ? 'badge-published' : 'badge-draft' ?>" style="border:none;cursor:pointer;">
                  <?= $j['active'] ? 'Activ' : 'Inactiv' ?>
                </button>
              </form>
            </td>
            <td>
              <form method="post" onsubmit="return confirm('Ștergi acest job?');">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $j['id'] ?>">
                <button type="submit" class="icon-btn danger" title="Șterge">🗑</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
