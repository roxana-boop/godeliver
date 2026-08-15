<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = getDbConnection();

$id = (int) ($_GET['id'] ?? 0);
$courier = [
    'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'city' => '',
    'platform' => 'Glovo', 'vehicle' => 'Scuter', 'contract_type' => 'PFA', 'iban' => '', 'status' => 'active',
];
$isEdit = false;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM couriers WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if ($found) { $courier = $found; $isEdit = true; }
}

$pageTitle = $isEdit ? 'Editează Curier' : 'Curier Nou';
$activeNav = 'couriers';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'platform' => $_POST['platform'] ?? 'Glovo',
        'vehicle' => $_POST['vehicle'] ?? 'Scuter',
        'contract_type' => $_POST['contract_type'] ?? 'PFA',
        'iban' => trim($_POST['iban'] ?? ''),
        'status' => $_POST['status'] ?? 'active',
    ];
    if ($data['first_name'] === '' || $data['last_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Completează numele și un email valid.';
    } else {
        try {
            if ($isEdit) {
                $sql = 'UPDATE couriers SET first_name=:first_name, last_name=:last_name, email=:email, phone=:phone,
                        city=:city, platform=:platform, vehicle=:vehicle, contract_type=:contract_type, iban=:iban, status=:status
                        WHERE id = :id';
                $data['id'] = $id;
            } else {
                $data['courier_code'] = 'CUR-' . strtoupper(bin2hex(random_bytes(3)));
                $data['referral_code'] = 'REF-' . strtoupper(bin2hex(random_bytes(3)));
                $data['password_hash'] = password_hash(bin2hex(random_bytes(5)), PASSWORD_DEFAULT);
                $sql = 'INSERT INTO couriers (first_name, last_name, email, phone, city, platform, vehicle, contract_type, iban, status, courier_code, referral_code, password_hash)
                        VALUES (:first_name, :last_name, :email, :phone, :city, :platform, :vehicle, :contract_type, :iban, :status, :courier_code, :referral_code, :password_hash)';
            }
            $pdo->prepare($sql)->execute($data);
            header('Location: couriers.php');
            exit;
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate') ? 'Există deja un curier cu acest email.' : 'Eroare la salvare.';
        }
    }
    $courier = array_merge($courier, $data);
}

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel" style="max-width:720px;">
  <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <div class="form-row">
      <div class="form-field"><label>Prenume *</label><input class="input" name="first_name" value="<?= h($courier['first_name']) ?>" required></div>
      <div class="form-field"><label>Nume *</label><input class="input" name="last_name" value="<?= h($courier['last_name']) ?>" required></div>
      <div class="form-field"><label>Email *</label><input class="input" type="email" name="email" value="<?= h($courier['email']) ?>" required></div>
      <div class="form-field"><label>Telefon</label><input class="input" name="phone" value="<?= h($courier['phone']) ?>"></div>
      <div class="form-field"><label>Oraș</label><input class="input" name="city" value="<?= h($courier['city']) ?>"></div>
      <div class="form-field"><label>IBAN</label><input class="input" name="iban" value="<?= h($courier['iban']) ?>"></div>
      <div class="form-field"><label>Platformă</label>
        <select name="platform">
          <?php foreach (['Glovo','Bolt Food','Wolt','Toate'] as $p): ?>
            <option value="<?= $p ?>" <?= $courier['platform']===$p?'selected':'' ?>><?= $p ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field"><label>Vehicul</label>
        <select name="vehicle">
          <?php foreach (['Bicicletă','Scuter','Mașină'] as $v): ?>
            <option value="<?= $v ?>" <?= $courier['vehicle']===$v?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field"><label>Tip Contract</label>
        <select name="contract_type">
          <?php foreach (['Angajare','PFA','SRL','Colaborare'] as $ct): ?>
            <option value="<?= $ct ?>" <?= $courier['contract_type']===$ct?'selected':'' ?>><?= $ct ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field"><label>Status</label>
        <select name="status">
          <?php foreach (['active'=>'Activ','suspended'=>'Suspendat','vacation'=>'Concediu','terminated'=>'Încetat'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $courier['status']===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="admin-form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează Modificările' : 'Creează Curier' ?></button>
      <a href="couriers.php" class="btn btn-ghost">Anulează</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
