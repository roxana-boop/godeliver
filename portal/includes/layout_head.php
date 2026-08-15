<?php
$courier = requireCourierLogin();
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Portal Curier';

$navItems = [
    'dashboard'  => ['index.php', '📊', 'Dashboard'],
    'profile'    => ['profile.php', '👤', 'Profilul Meu'],
    'documents'  => ['documents.php', '📁', 'Documente'],
    'payments'   => ['payments.php', '💳', 'Plăți'],
    'contracts'  => ['contracts.php', '📄', 'Contracte'],
    'referral'   => ['referral.php', '🔗', 'Recomandă un Prieten'],
    'requests'   => ['requests.php', '🎒', 'Concediu & Echipament'],
    'support'    => ['support.php', '🎫', 'Suport'],
];
?><!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?> — Portal Curier GoDeliver</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../assets/images/logo-icon.png">
<link rel="stylesheet" href="assets/css/portal.css">
</head>
<body class="portal-body">
<div class="portal-shell">

  <aside class="portal-sidebar" id="portalSidebar">
    <div class="portal-brand">
      <img src="../assets/images/logo-icon.png" alt="GoDeliver">
      <span>Portal Curier</span>
    </div>
    <nav class="portal-nav">
      <?php foreach ($navItems as $key => [$href, $icon, $label]): ?>
        <a href="<?= $href ?>" class="<?= $activeNav === $key ? 'active' : '' ?>"><span><?= $icon ?></span> <?= h($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="portal-sidebar-footer">
      <div class="portal-user">
        <div class="avatar"><?= h(firstLetter($courier['name'])) ?></div>
        <div><b><?= h($courier['name']) ?></b><span><?= h($courier['code']) ?></span></div>
      </div>
      <a href="logout.php" class="portal-logout">↩ Deconectare</a>
    </div>
  </aside>

  <div class="portal-main">
    <div class="portal-topbar">
      <div><h1><?= h($pageTitle) ?></h1></div>
      <button class="portal-mobile-toggle" id="portalMobileToggle">☰</button>
    </div>
    <div class="portal-content">
