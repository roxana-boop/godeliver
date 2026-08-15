<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Blog';
$activeNav = 'blog';

$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    csrfCheck();
    $id = (int) ($_POST['id'] ?? 0);
    if ($_POST['action'] === 'delete' && $id) {
        $pdo->prepare('DELETE FROM blog_posts WHERE id = :id')->execute([':id' => $id]);
    } elseif ($_POST['action'] === 'toggle_publish' && $id) {
        $pdo->prepare('UPDATE blog_posts SET published = 1 - published, published_at = IF(published = 0, NOW(), published_at) WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: blog.php');
    exit;
}

$posts = $pdo->query('SELECT id, title, category, published, created_at FROM blog_posts ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel">
  <div class="admin-panel-head">
    <h2>Articole</h2>
    <a href="blog_form.php" class="btn btn-primary btn-sm">+ Articol Nou</a>
  </div>
  <div class="admin-table-wrap">
    <table class="admin-table">
      <thead><tr><th>Titlu</th><th>Categorie</th><th>Status</th><th>Data</th><th></th></tr></thead>
      <tbody>
        <?php if (!$posts): ?>
          <tr><td colspan="5" class="admin-empty">Niciun articol încă. Creează primul articol de blog.</td></tr>
        <?php endif; ?>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td><strong><?= h($p['title']) ?></strong></td>
            <td class="cell-muted"><?= h($p['category']) ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                <input type="hidden" name="action" value="toggle_publish">
                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                <button type="submit" class="badge <?= $p['published'] ? 'badge-published' : 'badge-draft' ?>" style="border:none;cursor:pointer;">
                  <?= $p['published'] ? 'Publicat' : 'Ciornă' ?>
                </button>
              </form>
            </td>
            <td class="cell-muted"><?= h(date('d.m.Y', strtotime($p['created_at']))) ?></td>
            <td>
              <div class="row-actions">
                <a href="blog_form.php?id=<?= (int) $p['id'] ?>" class="icon-btn" title="Editează">✎</a>
                <form method="post" onsubmit="return confirm('Ștergi acest articol?');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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
