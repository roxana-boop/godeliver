<?php
/**
 * GoDeliver — Contact form submission endpoint
 */

require_once __DIR__ . '/config.php';

guardApiRequest('POST');

try {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Altele');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        jsonResponse(['success' => false, 'message' => 'Completează numele, emailul și mesajul.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Adresa de email nu este validă.'], 422);
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('
        INSERT INTO contact_messages (name, email, phone, subject, message, ip_address)
        VALUES (:name, :email, :phone, :subject, :message, :ip)
    ');
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':subject' => $subject,
        ':message' => $message,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);

    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
    @mail(ADMIN_NOTIFY_EMAIL, "Mesaj nou de contact: $subject", "De la: $name <$email>\nTelefon: $phone\n\n$message", $headers);

    jsonResponse(['success' => true]);

} catch (PDOException $e) {
    error_log('[GoDeliver] DB error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Eroare de bază de date. Te rugăm să încerci din nou.'], 500);
}
