<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Setări';
$activeNav = 'settings';
$pdo = getDbConnection();
$me = requireAdminLogin();

$tab = $_GET['tab'] ?? 'users';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    requireRole(['super_admin']);

    if ($_POST['action'] === 'create_user') {
        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'support';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $error = 'Completează numele, un email valid și o parolă de minim 8 caractere.';
        } else {
            try {
                $pdo->prepare('INSERT INTO admin_users (full_name, email, password_hash, role, active) VALUES (:n,:e,:p,:r,1)')
                    ->execute([':n' => $name, ':e' => $email, ':p' => password_hash($password, PASSWORD_DEFAULT), ':r' => $role]);
                logAudit('admin_user_created', 'admin_user', null, $email);
            } catch (PDOException $e) {
                $error = 'Există deja un admin cu acest email.';
            }
        }
    } elseif ($_POST['action'] === 'toggle_active') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id !== (int) $me['id']) { // can't deactivate yourself
            $pdo->prepare('UPDATE admin_users SET active = 1 - active WHERE id = :id')->execute([':id' => $id]);
            logAudit('admin_user_toggled', 'admin_user', $id);
        }
    } elseif ($_POST['action'] === 'delete_user') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id !== (int) $me['id']) {
            $pdo->prepare('DELETE FROM admin_users WHERE id = :id')->execute([':id' => $id]);
            logAudit('admin_user_deleted', 'admin_user', $id);
        }
    }
    header('Location: settings.php?tab=' . urlencode($tab));
    exit;
}

$users = $pdo->query('SELECT * FROM admin_users ORDER BY created_at DESC')->fetchAll();
$logs = $pdo->query('
    SELECT al.*, au.full_name FROM audit_logs al
    LEFT JOIN admin_users au ON au.id = al.admin_id
    ORDER BY al.created_at DESC LIMIT 100
')->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
$roleLabels = ['super_admin'=>'Super Admin','manager'=>'Manager','recruiter'=>'Recrutor','support'=>'Suport','finance'=>'Financiar'];
?>

<div class="admin-toolbar">
  <a href="?tab=users" class="btn <?= $tab==='users'?'btn-primary':'btn-ghost' ?> btn-sm">Utilizatori Admin</a>
  <a href="?tab=activity" class="btn <?= $tab==='activity'?'btn-primary':'btn-ghost' ?> btn-sm">Jurnal Activitate</a>
</div>

<?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>

<?php if ($tab === 'users'): ?>
  <?php if ($me['role'] === 'super_admin'): ?>
  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Adaugă Utilizator Admin</h2></div>
    <form method="post" class="admin-form">
      <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
      <input type="hidden" name="action" value="create_user">
      <div class="form-row">
        <div class="form-field"><label>Nume Complet *</label><input class="input" name="full_name" required></div>
        <div class="form-field"><label>Email *</label><input class="input" type="email" name="email" required></div>
        <div class="form-field"><label>Parolă * (min. 8 caractere)</label><input class="input" type="password" name="password" required minlength="8"></div>
        <div class="form-field"><label>Rol</label>
          <select name="role">
            <?php foreach ($roleLabels as $k=>$v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="admin-form-actions"><button type="submit" class="btn btn-primary">+ Adaugă Admin</button></div>
    </form>
  </div>
  <?php endif; ?>

  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Toți Utilizatorii Admin</h2></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Nume</th><th>Email</th><th>Rol</th><th>Status</th><th>Ultima Autentificare</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><strong><?= h($u['full_name']) ?></strong><?= (int)$u['id'] === (int)$me['id'] ? ' <span class="cell-muted">(tu)</span>' : '' ?></td>
              <td class="cell-muted"><?= h($u['email']) ?></td>
              <td><?= h($roleLabels[$u['role']] ?? $u['role']) ?></td>
              <td><span class="badge <?= $u['active'] ? 'badge-active' : 'badge-suspended' ?>"><?= $u['active'] ? 'Activ' : 'Inactiv' ?></span></td>
              <td class="cell-muted"><?= $u['last_login_at'] ? h(date('d.m.Y H:i', strtotime($u['last_login_at']))) : '—' ?></td>
              <td>
                <?php if ($me['role'] === 'super_admin' && (int)$u['id'] !== (int)$me['id']): ?>
                <div class="row-actions">
                  <form method="post"><input type="hidden" name="csrf_token" value="<?= h($token) ?>"><input type="hidden" name="action" value="toggle_active"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm"><?= $u['active'] ? 'Dezactivează' : 'Activează' ?></button>
                  </form>
                  <form method="post" onsubmit="return confirm('Ștergi acest admin?');"><input type="hidden" name="csrf_token" value="<?= h($token) ?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="icon-btn danger">🗑</button>
                  </form>
                </div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="admin-panel">
    <div class="admin-panel-head"><h2>Ultimele 100 de Acțiuni</h2></div>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead><tr><th>Admin</th><th>Acțiune</th><th>Detalii</th><th>Data</th></tr></thead>
        <tbody>
          <?php if (!$logs): ?><tr><td colspan="4" class="admin-empty">Nicio acțiune înregistrată încă.</td></tr><?php endif; ?>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td><?= h($l['full_name'] ?? 'Sistem') ?></td>
              <td class="cell-muted"><?= h($l['action']) ?></td>
              <td class="cell-muted"><?= h($l['details']) ?></td>
              <td class="cell-muted"><?= h(date('d.m.Y H:i', strtotime($l['created_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
