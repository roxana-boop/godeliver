<?php
require_once __DIR__ . '/includes/auth.php';
$pdo = getDbConnection();

$id = (int) ($_GET['id'] ?? 0);
$post = ['title' => '', 'excerpt' => '', 'content' => '', 'category' => 'Noutăți', 'published' => 0];
$isEdit = false;

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $found = $stmt->fetch();
    if ($found) { $post = $found; $isEdit = true; }
}

$pageTitle = $isEdit ? 'Editează Articol' : 'Articol Nou';
$activeNav = 'blog';
$error = '';

function slugify(string $text): string
{
    $map = ['ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ş'=>'s','ț'=>'t','ţ'=>'t','Ă'=>'a','Â'=>'a','Î'=>'i','Ș'=>'s','Ț'=>'t'];
    $text = strtr($text, $map);
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'articol';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'category' => trim($_POST['category'] ?? 'Noutăți'),
        'published' => isset($_POST['published']) ? 1 : 0,
    ];
    if ($data['title'] === '' || $data['content'] === '') {
        $error = 'Titlul și conținutul sunt obligatorii.';
    } else {
        try {
            $data['admin_id'] = requireAdminLogin()['id'];
            if ($isEdit) {
                $data['id'] = $id;
                $pdo->prepare('UPDATE blog_posts SET title=:title, excerpt=:excerpt, content=:content, category=:category, published=:published WHERE id=:id')->execute($data);
            } else {
                $base = slugify($data['title']);
                $slug = $base;
                $n = 1;
                $check = $pdo->prepare('SELECT COUNT(*) FROM blog_posts WHERE slug = :s');
                do {
                    $check->execute([':s' => $slug]);
                    if ($check->fetchColumn() == 0) break;
                    $slug = $base . '-' . (++$n);
                } while (true);
                $data['slug'] = $slug;
                $data['published_at'] = $data['published'] ? date('Y-m-d H:i:s') : null;
                $pdo->prepare('INSERT INTO blog_posts (title, excerpt, content, category, published, slug, published_at, created_by)
                    VALUES (:title, :excerpt, :content, :category, :published, :slug, :published_at, :admin_id)')->execute($data);
            }
            header('Location: blog.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Eroare la salvare.';
        }
    }
    $post = array_merge($post, $data);
}

require __DIR__ . '/includes/layout_head.php';
$token = csrfToken();
?>

<div class="admin-panel" style="max-width:820px;">
  <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>
  <form method="post" class="admin-form">
    <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
    <div class="form-field"><label>Titlu *</label><input class="input" name="title" value="<?= h($post['title']) ?>" required></div>
    <div class="form-row">
      <div class="form-field"><label>Categorie</label>
        <select name="category">
          <?php foreach (['Noutăți','Sfaturi','Actualizări'] as $c): ?>
            <option value="<?= $c ?>" <?= $post['category']===$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-field" style="display:flex;align-items:center;gap:10px;margin-top:26px;">
        <input type="checkbox" name="published" id="published" <?= $post['published'] ? 'checked' : '' ?> style="width:auto;">
        <label for="published" style="margin:0;">Publică articolul (vizibil pe /blog.html)</label>
      </div>
    </div>
    <div class="form-field"><label>Rezumat scurt</label><textarea class="input" name="excerpt" rows="2"><?= h($post['excerpt']) ?></textarea></div>
    <div class="form-field"><label>Conținut *</label><textarea class="input" name="content" rows="12" required><?= h($post['content']) ?></textarea>
      <p class="hint">Poți folosi paragrafe simple. Randarea completă cu formatare avansată pe pagina publică e un pas viitor.</p>
    </div>
    <div class="admin-form-actions">
      <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează Modificările' : 'Creează Articol' ?></button>
      <a href="blog.php" class="btn btn-ghost">Anulează</a>
    </div>
  </form>
</div>

<?php require __DIR__ . '/includes/layout_foot.php'; ?>
