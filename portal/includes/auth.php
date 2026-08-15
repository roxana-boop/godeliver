<?php
/**
 * GoDeliver Courier Portal — auth guard
 * require this file at the top of every protected portal page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../backend/config.php';

function requireCourierLogin(): array
{
    if (empty($_SESSION['courier_id'])) {
        header('Location: login.php');
        exit;
    }
    return [
        'id' => $_SESSION['courier_id'],
        'name' => $_SESSION['courier_name'] ?? 'Curier',
        'code' => $_SESSION['courier_code'] ?? '',
    ];
}

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

function firstLetter(?string $name): string
{
    $name = trim((string) $name);
    if ($name === '') return '?';
    return function_exists('mb_substr') ? mb_substr($name, 0, 1) : substr($name, 0, 1);
}
