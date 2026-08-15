<?php
session_start();
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['courier_id'])) {
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
            $stmt = $pdo->prepare('SELECT id, first_name, last_name, courier_code, password_hash, status FROM couriers WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $courier = $stmt->fetch();

            if ($courier && password_verify($password, $courier['password_hash'])) {
                if ($courier['status'] !== 'active') {
                    $error = 'Contul tău nu este activ momentan. Contactează echipa de suport GoDeliver.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['courier_id'] = $courier['id'];
                    $_SESSION['courier_name'] = $courier['first_name'] . ' ' . $courier['last_name'];
                    $_SESSION['courier_code'] = $courier['courier_code'];
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = 'Email sau parolă incorectă.';
            }
        } catch (PDOException $e) {
            error_log('[GoDeliver Portal] login DB error: ' . $e->getMessage());
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
<title>Portal Curier — GoDeliver</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="../assets/images/logo-icon.png">
<link rel="stylesheet" href="assets/css/portal.css">
</head>
<body class="portal-body">
  <div class="portal-login-shell">
    <div class="portal-login-card">
      <div class="brand">
        <img src="../assets/images/logo-icon.png" alt="GoDeliver">
        <span>GoDeliver</span>
      </div>
      <h1>Portal Curier</h1>
      <p class="sub">Autentifică-te cu contul tău de curier.</p>

      <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= h($error) ?></div><?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h($token) ?>">
        <div class="form-field"><label>Email</label><input class="input" type="email" name="email" required autofocus></div>
        <div class="form-field"><label>Parolă</label><input class="input" type="password" name="password" required></div>
        <button type="submit" class="btn btn-primary btn-block">Autentificare</button>
      </form>
      <p style="text-align:center;font-size:12.5px;color:var(--text-muted);margin-top:20px;">
        Nu ai cont? Aplică <a href="../devino-curier.html" style="color:var(--gold)">aici</a> pentru a deveni curier GoDeliver.
      </p>
    </div>
  </div>
</body>
</html>
