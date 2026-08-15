<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../backend/lib/simple_pdf.php';

$pageTitle = 'Contracte';
$activeNav = 'contracts';
$pdo = getDbConnection();

const CONTRACT_DIR = __DIR__ . '/../backend/uploads/contracts';

/** Builds the actual contract PDF for one courier and returns the relative file path. */
function generateContractFile(array $courier, ?string $signatureDataUrl): string
{
    if (!is_dir(CONTRACT_DIR)) {
        mkdir(CONTRACT_DIR, 0755, true);
    }

    $pdf = new SimplePdf();
    $left = 56;
    $y = 780;

    $pdf->text($left, $y, 'CIORGOVEAN LIVIU FLORIN PFA', 10, true); $y -= 14;
    $pdf->text($left, $y, 'CUI 48540021 · Activitate 100% remote', 9, false, [0.5, 0.5, 0.55]); $y -= 30;

    $titleMap = [
        'Angajare' => 'CONTRACT INDIVIDUAL DE MUNCĂ',
        'PFA' => 'CONTRACT DE COLABORARE (PFA)',
        'SRL' => 'CONTRACT DE COLABORARE (SRL)',
        'Colaborare' => 'CONTRACT DE COLABORARE',
    ];
    $pdf->text($left, $y, $titleMap[$courier['contract_type']] ?? 'CONTRACT DE COLABORARE', 15, true); $y -= 10;
    $pdf->text($left, $y, 'Platforma GoDeliver', 10, false, [0.6, 0.5, 0.02]); $y -= 34;

    $pdf->text($left, $y, 'ÎNTRE:', 11, true); $y -= 18;
    $pdf->text($left, $y, 'CIORGOVEAN LIVIU FLORIN PFA, CUI 48540021, denumit în continuare "GoDeliver",', 10); $y -= 15;
    $pdf->text($left, $y, 'activitate desfășurată 100% remote, fără sediu destinat curierilor,', 10); $y -= 30;

    $pdf->text($left, $y, 'ȘI:', 11, true); $y -= 18;
    $pdf->text($left, $y, $courier['first_name'] . ' ' . $courier['last_name'] . ', denumit în continuare "Curierul",', 10); $y -= 15;
    $pdf->text($left, $y, 'Email: ' . $courier['email'] . '   ·   Telefon: ' . $courier['phone'], 10); $y -= 15;
    $pdf->text($left, $y, 'Oraș: ' . $courier['city'], 10); $y -= 34;

    $pdf->text($left, $y, 'OBIECTUL CONTRACTULUI', 11, true); $y -= 18;
    $lines = [
        'Curierul va desfășura activități de livrare pentru platformele partenere',
        '(' . $courier['platform'] . '), folosind vehicul propriu de tip ' . strtolower($courier['vehicle']) . '.',
        'Colaborarea se desfășoară integral remote, fără prezență la un sediu fizic.',
        'Plata se efectuează săptămânal, conform structurii comunicate Curierului.',
    ];
    foreach ($lines as $line) { $pdf->text($left, $y, $line, 10); $y -= 15; }
    $y -= 20;

    $pdf->text($left, $y, 'Data: ' . date('d.m.Y'), 10); $y -= 15;
    $pdf->text($left, $y, 'Cod Curier: ' . $courier['courier_code'], 10); $y -= 50;

    $pdf->text($left, $y, 'Semnătură GoDeliver:', 9, false, [0.5, 0.5, 0.55]);
    $pdf->text($left + 260, $y, 'Semnătură Curier:', 9, false, [0.5, 0.5, 0.55]);
    $y -= 8;
    $pdf->rect($left, $y - 40, 200, 1, [0.8, 0.8, 0.82]);
    $pdf->rect($left + 260, $y - 40, 200, 1, [0.8, 0.8, 0.82]);

    // Embed the courier's signature (captured at application time) as a JPEG, if we can decode it.
    if ($signatureDataUrl && str_starts_with($signatureDataUrl, 'data:image')) {
        $base64 = substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1);
        $pngData = base64_decode($base64);
        if ($pngData !== false && function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($pngData);
            if ($img) {
                // Flatten transparency onto white before JPEG conversion (JPEG has no alpha channel).
                $w = imagesx($img); $h = imagesy($img);
                $flat = imagecreatetruecolor($w, $h);
                imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
                imagecopy($flat, $img, 0, 0, 0, 0, $w, $h);
                ob_start();
                imagejpeg($flat, null, 88);
                $jpegBytes = ob_get_clean();
                $pdf->image($jpegBytes, $left + 260, $y - 38, 180, 45);
            }
        }
    }

    $filename = $courier['courier_code'] . '_' . date('Ymd_His') . '.pdf';
    $fullPath = CONTRACT_DIR . '/' . $filename;
    $pdf->save($fullPath);

    return 'backend/uploads/contracts/' . $filename;
}

// ---- Actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();

    if ($_POST['action'] === 'generate') {
        $courierId = (int) ($_POST['courier_id'] ?? 0);
        $stmt = $pdo->prepare('SELECT * FROM couriers WHERE id = :id');
        $stmt->execute([':id' => $courierId]);
        $courier = $stmt->fetch();

        if ($courier) {
            $signature = null;
            if ($courier['application_id']) {
                $sigStmt = $pdo->prepare('SELECT signature_data FROM applications WHERE id = :id');
                $sigStmt->execute([':id' => $courier['application_id']]);
                $signature = $sigStmt->fetchColumn() ?: null;
            }
            $relativePath = generateContractFile($courier, $signature);

            $pdo->prepare('
                INSERT INTO contracts (courier_id, contract_type, file_path, signed, signed_at, valid_from, valid_until)
                VALUES (:cid, :ctype, :path, :signed, :signedAt, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR))
            ')->execute([
                ':cid' => $courierId,
                ':ctype' => $courier['contract_type'],
                ':path' => $relativePath,
                ':signed' => $signature ? 1 : 0,
                ':signedAt' => $signature ? date('Y-m-d H:i:s') : null,
            ]);
            logAudit('contract_generated', 'courier', $courierId, $relativePath);
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM contracts WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: contracts.php' . (isset($_GET['courier_id']) ? '?courier_id=' . (int) $_GET['courier_id'] : ''));
    exit;
}

$courierFilter = (int) ($_GET['courier_id'] ?? 0);
$sql = 'SELECT ct.*, c.first_name, c.last_name, c.courier_code FROM contracts ct
        JOIN couriers c ON c.id = ct.courier_id WHERE 1=1';
$params = [];
if ($courierFilter) {
    $sql .= ' AND ct.courier_id = :cid';
    $params[':cid'] = $courierFilter;
}
$sql .= ' ORDER BY ct.created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contracts = $stmt->fetchAll();

$couriers = $pdo->query('SELECT id, courier_code, first_name, last_name, contract_type FROM couriers ORDER BY first_name')->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel">
  <div class="admin-panel-head"><h2>Generează Contract Nou</h2></div>
  <form method="post" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <input type="hidden" name="action" value="generate">
    <div class="form-field" style="margin:0;min-width:280px;">
      <label>Curier</label>
      <select name="courier_id" required>
        <option value="">Alege curierul...</option>
        <?php foreach ($couriers as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= $courierFilter === (int)$c['id'] ? 'selected' : '' ?>>
            <?= h($c['courier_code'] . ' — ' . $c['first_name'] . ' ' . $c['last_name'] . ' (' . $c['contract_type'] . ')') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">📄 Generează PDF</button>
  </form>
  <p class="hint" style="margin-top:12px;">Contractul include automat semnătura din aplicația originală a curierului, dacă există.</p>
</div>

<div class="admin-panel">
  <div class="admin-panel-head">
    <h2>Contracte Generate<?= $courierFilter ? ' — filtrat' : '' ?></h2>
    <?php if ($courierFilter): ?><a href="contracts.php" class="btn btn-ghost btn-sm">Elimină Filtrul</a><?php endif; ?>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Curier</th><th>Tip</th><th>Semnat</th><th>Valabil</th><th>Generat</th><th></th></tr></thead>
      <tbody>
        <?php if (!$contracts): ?>
          <tr><td colspan="6" class="admin-empty">Niciun contract generat încă.</td></tr>
        <?php endif; ?>
        <?php foreach ($contracts as $ct): ?>
          <tr>
            <td><strong><?= h($ct['first_name'] . ' ' . $ct['last_name']) ?></strong><br><span class="cell-muted"><?= h($ct['courier_code']) ?></span></td>
            <td><?= h($ct['contract_type']) ?></td>
            <td><span class="badge <?= $ct['signed'] ? 'badge-active' : 'badge-draft' ?>"><?= $ct['signed'] ? 'Semnat' : 'Nesemnat' ?></span></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($ct['valid_from']))) ?> → <?= h(date('d.m.Y', strtotime($ct['valid_until']))) ?></td>
            <td class="cell-muted"><?= h(date('d.m.Y H:i', strtotime($ct['created_at']))) ?></td>
            <td>
              <div class="row-actions">
                <a href="../<?= h($ct['file_path']) ?>" target="_blank" class="btn btn-ghost btn-sm">Descarcă</a>
                <form method="post" onsubmit="return confirm('Ștergi acest contract?');">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $ct['id'] ?>">
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
