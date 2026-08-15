<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

$stats = [
    'new_applications' => 0,
    'total_couriers' => 0,
    'published_posts' => 0,
    'open_jobs' => 0,
];
$recentApplications = [];
$dbError = null;

try {
    $pdo = getDbConnection();
    $stats['new_applications'] = (int) $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'new'")->fetchColumn();
    $stats['total_couriers'] = (int) $pdo->query("SELECT COUNT(*) FROM couriers WHERE status = 'active'")->fetchColumn();
    $stats['published_posts'] = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE published = 1")->fetchColumn();
    $stats['open_jobs'] = (int) $pdo->query("SELECT COUNT(*) FROM job_listings WHERE active = 1")->fetchColumn();
    $recentApplications = $pdo->query("SELECT application_code, first_name, last_name, city, platform, status, created_at FROM applications ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch (PDOException $e) {
    $dbError = 'Nu m-am putut conecta la baza de date. Verifică backend/config.php.';
}

require __DIR__ . '/includes/layout_head.php';
?>

<?php if ($dbError): ?>
  <div class="admin-alert admin-alert-error"><?= h($dbError) ?></div>
<?php endif; ?>

<div class="admin-stats">
  <div class="admin-stat-card">
    <div class="label">Aplicații Noi</div>
    <div class="value"><?= $stats['new_applications'] ?></div>
    <div class="delta">de revizuit</div>
  </div>
  <div class="admin-stat-card">
    <div class="label">Curieri Activi</div>
    <div class="value"><?= $stats['total_couriers'] ?></div>
    <div class="delta">în flotă</div>
  </div>
  <div class="admin-stat-card">
    <div class="label">Articole Publicate</div>
    <div class="value"><?= $stats['published_posts'] ?></div>
    <div class="delta">pe blog</div>
  </div>
  <div class="admin-stat-card">
    <div class="label">Joburi Active</div>
    <div class="value"><?= $stats['open_jobs'] ?></div>
    <div class="delta">pe pagina Cariere</div>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel-head">
    <h2>Aplicații Recente</h2>
    <a href="applications.php" class="btn btn-ghost btn-sm">Vezi Toate →</a>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead>
        <tr><th>Cod</th><th>Nume</th><th>Oraș</th><th>Platformă</th><th>Status</th><th>Data</th></tr>
      </thead>
      <tbody>
        <?php if (!$recentApplications): ?>
          <tr><td colspan="6" class="admin-empty">Nu există aplicații încă.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentApplications as $app): ?>
          <tr>
            <td><a href="application_view.php?code=<?= h($app['application_code']) ?>"><?= h($app['application_code']) ?></a></td>
            <td><?= h($app['first_name'] . ' ' . $app['last_name']) ?></td>
            <td><?= h($app['city']) ?></td>
            <td><?= h($app['platform']) ?></td>
            <td><span class="badge badge-<?= h($app['status']) ?>"><?= h($app['status']) ?></span></td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($app['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
