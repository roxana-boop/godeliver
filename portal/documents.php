<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Documente';
$activeNav = 'documents';
$courier = requireCourierLogin();
$pdo = getDbConnection();

const COURIER_DOC_DIR = __DIR__ . '/../backend/uploads/courier_documents';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $docType = $_POST['doc_type'] ?? '';
    $allowedTypes = ['id_card', 'driving_license', 'vehicle_registration', 'insurance', 'iban_proof', 'selfie_id', 'other'];

    if (!in_array($docType, $allowedTypes, true) || empty($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Alege un tip de document și un fișier valid.';
    } else {
        $file = $_FILES['document'];
        if ($file['size'] > 10 * 1024 * 1024) {
            $error = 'Fișierul depășește 10MB.';
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
                $error = 'Tip de fișier neacceptat (doar JPG, PNG sau PDF).';
            } else {
                if (!is_dir(COURIER_DOC_DIR)) mkdir(COURIER_DOC_DIR, 0755, true);
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safeName = $courier['code'] . '_' . $docType . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = COURIER_DOC_DIR . '/' . $safeName;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    $pdo->prepare('INSERT INTO courier_documents (courier_id, doc_type, file_path, original_filename) VALUES (:cid, :type, :path, :name)')
                        ->execute([
                            ':cid' => $courier['id'], ':type' => $docType,
                            ':path' => 'backend/uploads/courier_documents/' . $safeName, ':name' => $file['name'],
                        ]);
                    $success = 'Documentul a fost încărcat cu succes.';
                } else {
                    $error = 'Nu am putut salva fișierul. Încearcă din nou.';
                }
            }
        }
    }
}

$docLabels = [
    'id_card' => 'Carte de Identitate', 'driving_license' => 'Permis de Conducere',
    'vehicle_registration' => 'Talon Vehicul', 'insurance' => 'Asigurare (RCA)',
    'iban_proof' => 'Dovadă IBAN', 'selfie_id' => 'Selfie cu Actul de Identitate', 'other' => 'Altele',
];

$stmt = $pdo->prepare('SELECT * FROM courier_documents WHERE courier_id = :id ORDER BY uploaded_at DESC');
$stmt->execute([':id' => $courier['id']]);
$myDocs = $stmt->fetchAll();

// Original application documents, read-only reference.
$origStmt = $pdo->prepare('
    SELECT ad.doc_type, ad.file_path, ad.original_filename, ad.uploaded_at FROM application_documents ad
    JOIN couriers c ON c.application_id = ad.application_id
    WHERE c.id = :id
');
$origStmt->execute([':id' => $courier['id']]);
$originalDocs = $origStmt->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<?php if ($success): ?><div class="admin-alert admin-alert-success"><?= h($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Încarcă Document Nou</h2></div>
  <form method="post" enctype="multipart/form-data" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <div class="form-row">
      <div class="form-field"><label>Tip Document</label>
        <select name="doc_type" required>
          <?php foreach ($docLabels as $k => $v): ?><option value="<?= $k ?>"><?= $v ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="form-field"><label>Fișier (JPG, PNG sau PDF, max 10MB)</label><input class="input" type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required></div>
    </div>
    <div class="admin-form-actions"><button type="submit" class="btn btn-primary">Încarcă</button></div>
  </form>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Documentele Mele Actualizate</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Tip</th><th>Fișier</th><th>Încărcat</th><th></th></tr></thead>
      <tbody>
        <?php if (!$myDocs): ?><tr><td colspan="4" class="admin-empty">Niciun document încărcat prin portal încă.</td></tr><?php endif; ?>
        <?php foreach ($myDocs as $d): ?>
          <tr>
            <td><?= h($docLabels[$d['doc_type']] ?? $d['doc_type']) ?></td>
            <td class="cell-muted"><?= h($d['original_filename']) ?></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($d['uploaded_at']))) ?></td>
            <td><a href="../<?= h($d['file_path']) ?>" target="_blank" class="btn btn-ghost btn-sm">Vezi</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="portal-panel">
  <div class="portal-panel-head"><h2>Documente din Aplicația Inițială</h2></div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Tip</th><th>Fișier</th><th>Încărcat</th><th></th></tr></thead>
      <tbody>
        <?php if (!$originalDocs): ?><tr><td colspan="4" class="admin-empty">Nicio referință găsită.</td></tr><?php endif; ?>
        <?php foreach ($originalDocs as $d): ?>
          <tr>
            <td><?= h($docLabels[$d['doc_type']] ?? $d['doc_type']) ?></td>
            <td class="cell-muted"><?= h($d['original_filename']) ?></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($d['uploaded_at']))) ?></td>
            <td><a href="../<?= h($d['file_path']) ?>" target="_blank" class="btn btn-ghost btn-sm">Vezi</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
