<?php
/**
 * GoDeliver — Public blog feed
 * Read-only, no auth required. Powers the dynamic grid on /blog.html.
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getDbConnection();
    $posts = $pdo->query("
        SELECT title, slug, excerpt, category, published_at
        FROM blog_posts WHERE published = 1
        ORDER BY published_at DESC LIMIT 24
    ")->fetchAll();
    echo json_encode(['success' => true, 'posts' => $posts], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    error_log('[GoDeliver] api_blog error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'posts' => []]);
}
