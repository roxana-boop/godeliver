<?php
session_start();
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Introdu emailul și parola.';
    } else {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, active FROM admin_users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && $user['active'] && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['full_name'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['admin_role'] = $user['role'];

                $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id')->execute([':id' => $user['id']]);

                header('Location: index.php');
                exit;
            }
            $error = 'Email sau parolă incorectă.';
        } catch (PDOException $e) {
            error_log('[GoDeliver Admin] login DB error: ' . $e->getMessage());
            $error = 'Eroare de conectare la baza de date.';
        }
    }
}
$token = csrfToken();
?><!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Autentificare Admin — GoDeliver</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../assets/images/logo-icon.png">
<link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">
  <div class="admin-login-shell">
    <div class="admin-login-card">
      <div class="brand">
        <img src="../assets/images/logo-icon.png" alt="GoDeliver">
        <span>GoDeliver</span>
      </div>
      <h1>Panou Admin</h1>
      <p class="sub">Autentifică-te pentru a administra platforma.</p>

      <?php if ($error): ?>
        <div class="admin-alert admin-alert-error"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-field">
          <label>Email</label>
          <input class="input" type="email" name="email" required autofocus>
        </div>
        <div class="form-field">
          <label>Parolă</label>
          <input class="input" type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Autentificare</button>
      </form>
    </div>
  </div>
</body>
</html>
