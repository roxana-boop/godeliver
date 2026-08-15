<?php
/**
 * GoDeliver Admin — auth guard
 * require this file at the top of every protected admin page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config.php';

/** Redirects to login if no admin session exists. Returns the current admin as an array. */
function requireAdminLogin(): array
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
    return [
        'id' => $_SESSION['admin_id'],
        'name' => $_SESSION['admin_name'] ?? 'Admin',
        'email' => $_SESSION['admin_email'] ?? '',
        'role' => $_SESSION['admin_role'] ?? 'support',
    ];
}

/** Simple CSRF token helpers to protect POST forms in the admin panel. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

function csrfCheck(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Token de securitate invalid. Reîncarcă pagina și încearcă din nou.');
    }
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** First letter of a name, safe even without the mbstring extension (avoids a hard host dependency). */
function firstLetter(?string $name): string
{
    $name = trim((string) $name);
    if ($name === '') {
        return '?';
    }
    return function_exists('mb_substr') ? mb_substr($name, 0, 1) : substr($name, 0, 1);
}

/** Truncates text for table previews, safe even without the mbstring extension. */
function truncateText(?string $text, int $length = 90): string
{
    $text = (string) $text;
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $length, '…');
    }
    return strlen($text) > $length ? substr($text, 0, $length) . '…' : $text;
}

/** Records an admin action for traceability (visible on admin/activity.php). */
function logAudit(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    try {
        $pdo = getDbConnection();
        $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (:aid, :action, :etype, :eid, :details, :ip)')
            ->execute([
                ':aid' => $_SESSION['admin_id'] ?? null,
                ':action' => $action,
                ':etype' => $entityType,
                ':eid' => $entityId,
                ':details' => $details,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
    } catch (Throwable $e) {
        error_log('[GoDeliver Admin] audit log failed: ' . $e->getMessage());
    }
}

/** Guards a page to a set of roles; super_admin always passes. */
function requireRole(array $allowedRoles): void
{
    $role = $_SESSION['admin_role'] ?? 'support';
    if ($role === 'super_admin' || in_array($role, $allowedRoles, true)) {
        return;
    }
    http_response_code(403);
    die('Nu ai permisiunea de a accesa această secțiune. Contactează un super admin.');
}
