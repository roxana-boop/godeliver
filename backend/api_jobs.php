<?php
/**
 * GoDeliver — Public job listings feed
 * Read-only, no auth required. Powers the dynamic list on /cariere.html.
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDbConnection();
    $jobs = $pdo->query("
        SELECT title, department, city, employment_type, description
        FROM job_listings WHERE active = 1
        ORDER BY created_at DESC LIMIT 50
    ")->fetchAll();
    echo json_encode(['success' => true, 'jobs' => $jobs], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log('[GoDeliver] api_jobs error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'jobs' => []]);
}
