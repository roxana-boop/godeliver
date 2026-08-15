<?php
/**
 * Admin layout — top include.
 * Expects $pageTitle (string) and $activeNav (string key) to be set
 * by the including page before this file is required.
 */
$admin = requireAdminLogin();
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Admin';

$navItems = [
    'dashboard'    => ['index.php', '📊', 'Dashboard'],
    'applications' => ['applications.php', '📝', 'Aplicații'],
    'couriers'     => ['couriers.php', '🛵', 'Curieri'],
    'payments'     => ['payments.php', '💳', 'Plăți'],
    'contracts'    => ['contracts.php', '📄', 'Contracte'],
    'tickets'      => ['tickets.php', '🎫', 'Tickete Suport'],
    'blog'         => ['blog.php', '📰', 'Blog'],
    'careers'      => ['careers.php', '💼', 'Cariere'],
    'reports'      => ['reports.php', '📈', 'Rapoarte'],
    'settings'     => ['settings.php', '⚙️', 'Setări'],
];
?><!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> — Admin GoDeliver</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../assets/images/logo-icon.png">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">

  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-brand">
      <img src="../assets/images/logo-icon.png" alt="GoDeliver">
      <span>Admin</span>
    </div>
    <nav class="admin-nav">
      <?php foreach ($navItems as $key => [$href, $icon, $label]): ?>
        <a href="<?= $href ?>" class="<?= $activeNav === $key ? 'active' : '' ?>">
          <span class="icon"><?= $icon ?></span> <?= h($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-footer">
      <div class="admin-user">
        <div class="avatar"><?= h(firstLetter($admin['name'])) ?></div>
        <div>
          <b><?= h($admin['name']) ?></b>
          <span><?= h($admin['role']) ?></span>
        </div>
      </div>
      <a href="logout.php" class="admin-logout">↩ Deconectare</a>
    </div>
  </aside>

  <div class="admin-main">
    <div class="admin-topbar">
      <div>
        <div class="breadcrumb">GoDeliver / Admin</div>
        <h1><?= h($pageTitle) ?></h1>
      </div>
      <button class="admin-mobile-toggle" id="adminMobileToggle">☰</button>
    </div>
    <div class="admin-content">
