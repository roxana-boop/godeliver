<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = getDbConnection();

$code = $_GET['code'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM applications WHERE application_code = :code LIMIT 1');
$stmt->execute([':code' => $code]);
$app = $stmt->fetch();

if (!$app) {
    header('Location: applications.php');
    exit;
}

$pageTitle = 'Aplicație ' . $app['application_code'];
$activeNav = 'applications';
$flash = '';
$flashType = 'success';

// ---- Actions: approve & create courier / reject / save notes ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_notes') {
        $pdo->prepare('UPDATE applications SET admin_notes = :n WHERE id = :id')
            ->execute([':n' => trim($_POST['notes'] ?? ''), ':id' => $app['id']]);
        $flash = 'Notițele au fost salvate.';

    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE applications SET status = 'rejected' WHERE id = :id")->execute([':id' => $app['id']]);
        logAudit('application_rejected', 'application', $app['id'], $app['application_code']);
        $flash = 'Aplicația a fost respinsă.';

    } elseif ($action === 'approve_create_courier') {
        // Guard against creating a duplicate courier for the same application/email.
        $check = $pdo->prepare('SELECT id FROM couriers WHERE application_id = :aid OR email = :email LIMIT 1');
        $check->execute([':aid' => $app['id'], ':email' => $app['email']]);
        $existing = $check->fetch();

        if ($existing) {
            $flash = 'Există deja un cont de curier pentru această aplicație.';
            $flashType = 'error';
        } else {
            $courierCode = 'CUR-' . strtoupper(bin2hex(random_bytes(3)));
            $tempPassword = bin2hex(random_bytes(5)); // shown once below; courier resets it in the portal (phase 3)
            $referralCode = 'REF-' . strtoupper(bin2hex(random_bytes(3)));

            $pdo->prepare('
                INSERT INTO couriers (application_id, courier_code, first_name, last_name, email, phone, password_hash, city, platform, vehicle, contract_type, referral_code, status)
                VALUES (:aid, :code, :first, :last, :email, :phone, :hash, :city, :platform, :vehicle, :contract, :ref, "active")
            ')->execute([
                ':aid' => $app['id'],
                ':code' => $courierCode,
                ':first' => $app['first_name'],
                ':last' => $app['last_name'],
                ':email' => $app['email'],
                ':phone' => $app['phone'],
                ':hash' => password_hash($tempPassword, PASSWORD_DEFAULT),
                ':city' => $app['city'],
                ':platform' => $app['platform'],
                ':vehicle' => $app['vehicle'],
                ':contract' => $app['contract_type'],
                ':ref' => $referralCode,
            ]);
            $pdo->prepare("UPDATE applications SET status = 'activated' WHERE id = :id")->execute([':id' => $app['id']]);
            logAudit('courier_created_from_application', 'courier', null, "$courierCode ({$app['application_code']})");

            $flash = "Cont de curier creat: <strong>$courierCode</strong> · parolă temporară: <strong>$tempPassword</strong> (comunic-o curierului printr-un canal sigur — nu e trimisă automat).";
            $stmt->execute([':code' => $code]);
            $app = $stmt->fetch();
        }
    }
}

$docStmt = $pdo->prepare('SELECT doc_type, file_path, original_filename FROM application_documents WHERE application_id = :id');
$docStmt->execute([':id' => $app['id']]);
$documents = $docStmt->fetchAll();
$docLabels = [
    'id_card' => 'Carte de Identitate', 'driving_license' => 'Permis de Conducere',
    'vehicle_registration' => 'Talon Vehicul', 'insurance' => 'Asigurare (RCA)',
    'iban_proof' => 'Dovadă IBAN', 'selfie_id' => 'Selfie cu Actul de Identitate', 'cv' => 'CV',
];

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<?php if ($flash): ?>
  <div class="admin-alert admin-alert-<?= $flashType === 'error' ? 'error' : 'success' ?>"><?= $flash ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
  <div>
    <div class="admin-panel">
      <div class="admin-panel-head">
        <h2>Informații Personale</h2>
        <span class="badge badge-<?= h($app['status']) ?>"><?= h($app['status']) ?></span>
      </div>
      <div class="admin-form">
        <div class="form-row">
          <div class="form-field"><label>Nume Complet</label><div><?= h($app['first_name'] . ' ' . $app['last_name']) ?></div></div>
          <div class="form-field"><label>CNP</label><div><?= h($app['cnp']) ?></div></div>
          <div class="form-field"><label>Telefon</label><div><?= h($app['phone']) ?></div></div>
          <div class="form-field"><label>Email</label><div><?= h($app['email']) ?></div></div>
          <div class="form-field"><label>Data Nașterii</label><div><?= h(date('d.m.Y', strtotime($app['birth_date']))) ?></div></div>
          <div class="form-field"><label>Adresă</label><div><?= h($app['address']) ?></div></div>
          <div class="form-field"><label>Contact Urgență</label><div><?= h($app['emergency_name']) ?> · <?= h($app['emergency_phone']) ?></div></div>
        </div>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Informații de Muncă</h2></div>
      <div class="form-row admin-form">
        <div class="form-field"><label>Oraș</label><div><?= h($app['city']) ?></div></div>
        <div class="form-field"><label>Platformă</label><div><?= h($app['platform']) ?></div></div>
        <div class="form-field"><label>Vehicul</label><div><?= h($app['vehicle']) ?></div></div>
        <div class="form-field"><label>Disponibilitate</label><div><?= h($app['availability']) ?></div></div>
        <div class="form-field"><label>Experiență</label><div><?= h($app['experience']) ?></div></div>
        <div class="form-field"><label>Tip Contract</label><div><?= h($app['contract_type']) ?></div></div>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Documente</h2></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <tbody>
            <?php if (!$documents): ?>
              <tr><td class="admin-empty">Niciun document încărcat.</td></tr>
            <?php endif; ?>
            <?php foreach ($documents as $doc): ?>
              <tr>
                <td><?= h($docLabels[$doc['doc_type']] ?? $doc['doc_type']) ?></td>
                <td class="cell-muted"><?= h($doc['original_filename']) ?></td>
                <td><a href="../<?= h($doc['file_path']) ?>" target="_blank" class="btn btn-ghost btn-sm">Vezi Fișier</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Semnătură Digitală</h2></div>
      <?php if ($app['signature_data']): ?>
        <img src="<?= h($app['signature_data']) ?>" alt="Semnătură" style="background:#fff;border-radius:8px;max-width:300px;">
      <?php else: ?>
        <p>Nicio semnătură înregistrată.</p>
      <?php endif; ?>
    </div>

    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Notițe Interne</h2></div>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <input type="hidden" name="action" value="save_notes">
        <textarea class="input" name="notes" rows="4" placeholder="Notițe vizibile doar pentru echipa admin..."><?= h($app['admin_notes']) ?></textarea>
        <div class="admin-form-actions"><button type="submit" class="btn btn-ghost btn-sm">Salvează Notițele</button></div>
      </form>
    </div>
  </div>

  <div>
    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Acțiuni</h2></div>
      <?php if ($app['status'] !== 'activated'): ?>
        <form method="post" style="margin-bottom:10px;">
          <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
          <input type="hidden" name="action" value="approve_create_courier">
          <button type="submit" class="btn btn-primary btn-block">✓ Aprobă și Creează Cont Curier</button>
        </form>
        <form method="post" onsubmit="return confirm('Respingi această aplicație?');">
          <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
          <input type="hidden" name="action" value="reject">
          <button type="submit" class="btn btn-ghost btn-block">✕ Respinge Aplicația</button>
        </form>
      <?php else: ?>
        <p style="font-size:13.5px;">Această aplicație a fost deja activată și are un cont de curier asociat.</p>
        <a href="couriers.php" class="btn btn-ghost btn-block">Vezi Curierii →</a>
      <?php endif; ?>
    </div>
    <div class="admin-panel">
      <div class="admin-panel-head"><h2>Meta</h2></div>
      <p class="cell-muted" style="font-size:13px;">Cod: <?= h($app['application_code']) ?><br>
      Trimisă: <?= h(date('d.m.Y H:i', strtotime($app['created_at']))) ?><br>
      IP: <?= h($app['ip_address']) ?><br>
      Termeni acceptați: <?= $app['terms_accepted'] ? 'Da' : 'Nu' ?></p>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
