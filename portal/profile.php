<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Profilul Meu';
$activeNav = 'profile';
$courier = requireCourierLogin();
$pdo = getDbConnection();

$stmt = $pdo->prepare('SELECT * FROM couriers WHERE id = :id');
$stmt->execute([':id' => $courier['id']]);
$me = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $iban = trim($_POST['iban'] ?? '');
        $pdo->prepare('UPDATE couriers SET phone = :phone, city = :city, iban = :iban WHERE id = :id')
            ->execute([':phone' => $phone, ':city' => $city, ':iban' => $iban, ':id' => $courier['id']]);
        $success = 'Profilul a fost actualizat.';
        $stmt->execute([':id' => $courier['id']]);
        $me = $stmt->fetch();

    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $me['password_hash'])) {
            $error = 'Parola curentă este incorectă.';
        } elseif (strlen($new) < 8) {
            $error = 'Parola nouă trebuie să aibă cel puțin 8 caractere.';
        } elseif ($new !== $confirm) {
            $error = 'Parolele nu coincid.';
        } else {
            $pdo->prepare('UPDATE couriers SET password_hash = :hash WHERE id = :id')
                ->execute([':hash' => password_hash($new, PASSWORD_DEFAULT), ':id' => $courier['id']]);
            $success = 'Parola a fost schimbată cu succes.';
        }
    }
}

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<?php if ($success): ?><div class="admin-alert admin-alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="portal-panel" style="max-width:680px;">
  <div class="portal-panel-head"><h2>Datele Mele</h2></div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="update_profile">
    <div class="form-row">
      <div class="form-field"><label>Nume Complet</label><input class="input" value="<?= h($me['first_name'] . ' ' . $me['last_name']) ?>" disabled></div>
      <div class="form-field"><label>Email</label><input class="input" value="<?= h($me['email']) ?>" disabled></div>
      <div class="form-field"><label>Telefon</label><input class="input" name="phone" value="<?= h($me['phone']) ?>"></div>
      <div class="form-field"><label>Oraș</label><input class="input" name="city" value="<?= h($me['city']) ?>"></div>
      <div class="form-field"><label>IBAN</label><input class="input" name="iban" value="<?= h($me['iban']) ?>"></div>
    </div>
    <p class="hint">Numele și emailul sunt fixate în contract — dacă trebuie schimbate, contactează suportul.</p>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Salvează Modificările</button></div>
  </form>
</div>

<div class="portal-panel" style="max-width:680px;">
  <div class="portal-panel-head"><h2>Schimbă Parola</h2></div>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="change_password">
    <div class="form-field"><label>Parola Curentă</label><input class="input" type="password" name="current_password" required></div>
    <div class="form-row">
      <div class="form-field"><label>Parola Nouă</label><input class="input" type="password" name="new_password" required minlength="8"></div>
      <div class="form-field"><label>Confirmă Parola Nouă</label><input class="input" type="password" name="confirm_password" required minlength="8"></div>
    </div>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Schimbă Parola</button></div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
