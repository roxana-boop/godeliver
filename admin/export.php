<?php
require_once __DIR__ . '/includes/auth.php';
requireAdminLogin();
$pdo = getDbConnection();

$type = $_GET['type'] ?? '';
$allowed = ['applications', 'couriers', 'payments'];
if (!in_array($type, $allowed, true)) {
    die('Tip de export invalid.');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="godeliver_' . $type . '_' . date('Ymd') . '.csv"');

$out = fopen('php://output', 'w');
fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel renders diacritics correctly

if ($type === 'applications') {
    fputcsv($out, ['Cod', 'Nume', 'Prenume', 'Email', 'Telefon', 'Oraș', 'Platformă', 'Vehicul', 'Contract', 'Status', 'Data']);
    $rows = $pdo->query('SELECT application_code, last_name, first_name, email, phone, city, platform, vehicle, contract_type, status, created_at FROM applications ORDER BY created_at DESC')->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [$r['application_code'], $r['last_name'], $r['first_name'], $r['email'], $r['phone'], $r['city'], $r['platform'], $r['vehicle'], $r['contract_type'], $r['status'], $r['created_at']]);
    }
} elseif ($type === 'couriers') {
    fputcsv($out, ['Cod', 'Nume', 'Prenume', 'Email', 'Telefon', 'Oraș', 'Platformă', 'Vehicul', 'Contract', 'Status', 'Creat']);
    $rows = $pdo->query('SELECT courier_code, last_name, first_name, email, phone, city, platform, vehicle, contract_type, status, created_at FROM couriers ORDER BY created_at DESC')->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [$r['courier_code'], $r['last_name'], $r['first_name'], $r['email'], $r['phone'], $r['city'], $r['platform'], $r['vehicle'], $r['contract_type'], $r['status'], $r['created_at']]);
    }
} elseif ($type === 'payments') {
    fputcsv($out, ['Curier', 'Cod Curier', 'Perioadă Start', 'Perioadă Sfârșit', 'Brut', 'Comision', 'Bonus', 'Net', 'Status', 'Plătit La']);
    $rows = $pdo->query('
        SELECT c.first_name, c.last_name, c.courier_code, p.* FROM payments p
        JOIN couriers c ON c.id = p.courier_id ORDER BY p.created_at DESC
    ')->fetchAll();
    foreach ($rows as $r) {
        fputcsv($out, [
            $r['first_name'] . ' ' . $r['last_name'], $r['courier_code'], $r['period_start'], $r['period_end'],
            $r['gross_amount'], $r['commission_amount'], $r['bonus_amount'], $r['net_amount'], $r['status'], $r['paid_at'],
        ]);
    }
}

fclose($out);
logAudit('csv_export', $type);
exit;
