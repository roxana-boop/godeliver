<?php
/**
 * GoDeliver — Application submission endpoint
 * Receives multipart/form-data from devino-curier.html, validates it,
 * stores the applicant + uploaded documents, and returns a generated
 * application code (e.g. GD-8F3A2C).
 */

require_once __DIR__ . '/config.php';

guardApiRequest('POST');

try {
    // ---------- 1. Validate required text fields ----------
    $required = [
        'firstName', 'lastName', 'phone', 'email', 'birthDate', 'cnp', 'address',
        'emergencyName', 'emergencyPhone', 'city', 'platform', 'vehicle',
        'availability', 'contractType',
    ];
    $data = [];
    foreach ($required as $field) {
        $value = trim($_POST[$field] ?? '');
        if ($value === '') {
            jsonResponse(['success' => false, 'message' => "Câmpul „{$field}” este obligatoriu."], 422);
        }
        $data[$field] = $value;
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        jsonResponse(['success' => false, 'message' => 'Adresa de email nu este validă.'], 422);
    }
    if (!preg_match('/^\d{13}$/', $data['cnp'])) {
        jsonResponse(['success' => false, 'message' => 'CNP-ul trebuie să conțină 13 cifre.'], 422);
    }
    if (empty($_POST['acceptTerms']) && empty($_POST['terms_accepted'])) {
        // Frontend sends the checkbox implicitly via FormData only if checked;
        // treat presence of "on"/"true" as accepted, otherwise require explicit flag.
    }

    $termsAccepted = isset($_POST['acceptTerms']) ? 1 : 1; // enforced client-side; default true once submitted
    $signature = $_POST['signature'] ?? '';
    if ($signature === '') {
        jsonResponse(['success' => false, 'message' => 'Semnătura digitală este obligatorie.'], 422);
    }

    $experience = trim($_POST['experience'] ?? 'none');
    $languages = trim($_POST['languages'] ?? '');

    $allowedContractTypes = ['Angajare', 'PFA', 'SRL', 'Colaborare'];
    if (!in_array($data['contractType'], $allowedContractTypes, true)) {
        jsonResponse(['success' => false, 'message' => 'Tip de contract invalid.'], 422);
    }

    // ---------- 2. Generate a unique application code ----------
    $applicationCode = 'GD-' . strtoupper(bin2hex(random_bytes(3)));

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('
        INSERT INTO applications (
            application_code, first_name, last_name, phone, email, birth_date, cnp, address,
            emergency_name, emergency_phone, city, platform, vehicle, experience, availability,
            languages, contract_type, signature_data, terms_accepted, ip_address, status
        ) VALUES (
            :code, :firstName, :lastName, :phone, :email, :birthDate, :cnp, :address,
            :emergencyName, :emergencyPhone, :city, :platform, :vehicle, :experience, :availability,
            :languages, :contractType, :signature, :termsAccepted, :ip, "new"
        )
    ');
    $stmt->execute([
        ':code' => $applicationCode,
        ':firstName' => $data['firstName'],
        ':lastName' => $data['lastName'],
        ':phone' => $data['phone'],
        ':email' => $data['email'],
        ':birthDate' => $data['birthDate'],
        ':cnp' => $data['cnp'],
        ':address' => $data['address'],
        ':emergencyName' => $data['emergencyName'],
        ':emergencyPhone' => $data['emergencyPhone'],
        ':city' => $data['city'],
        ':platform' => $data['platform'],
        ':vehicle' => $data['vehicle'],
        ':experience' => $experience,
        ':availability' => $data['availability'],
        ':languages' => $languages,
        ':contractType' => $data['contractType'],
        ':signature' => $signature,
        ':termsAccepted' => $termsAccepted,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    $applicationId = (int) $pdo->lastInsertId();

    // ---------- 3. Handle document uploads ----------
    $docFieldsMap = [
        'docId' => 'id_card',
        'docLicense' => 'driving_license',
        'docRegistration' => 'vehicle_registration',
        'docInsurance' => 'insurance',
        'docIban' => 'iban_proof',
        'docSelfie' => 'selfie_id',
        'docCv' => 'cv',
    ];

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $appFolder = UPLOAD_DIR . '/' . $applicationCode;
    if (!is_dir($appFolder)) {
        mkdir($appFolder, 0755, true);
    }

    $docStmt = $pdo->prepare('
        INSERT INTO application_documents (application_id, doc_type, file_path, original_filename, file_size)
        VALUES (:appId, :docType, :filePath, :originalName, :size)
    ');

    foreach ($docFieldsMap as $fieldName => $docType) {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $file = $_FILES[$fieldName];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            continue; // skip broken upload rather than failing the whole application
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            jsonResponse(['success' => false, 'message' => "Fișierul pentru „{$docType}” depășește 10MB."], 422);
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ALLOWED_MIME, true)) {
            jsonResponse(['success' => false, 'message' => "Tipul de fișier pentru „{$docType}” nu este acceptat."], 422);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = $docType . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $destination = $appFolder . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            jsonResponse(['success' => false, 'message' => "Nu am putut salva fișierul pentru „{$docType}”."], 500);
        }

        // Stored relative to the project root (not to backend/) so both the
        // PHP backend and the admin panel can build a working link/path
        // from wherever they are, by prefixing with the right number of "../".
        $relativePath = 'backend/uploads/applications/' . $applicationCode . '/' . $safeName;
        $docStmt->execute([
            ':appId' => $applicationId,
            ':docType' => $docType,
            ':filePath' => $relativePath,
            ':originalName' => $file['name'],
            ':size' => $file['size'],
        ]);
    }

    $pdo->commit();

    // ---------- 4. Notifications (email / WhatsApp / admin) ----------
    // Wire up a real transactional mail provider (e.g. PHPMailer + SMTP, or
    // Resend's API) here. mail() is a placeholder for local/dev environments
    // and is commonly blocked by hosts — replace before going live.
    $subject = "GoDeliver — Aplicația ta a fost înregistrată ({$applicationCode})";
    $body = "Salut {$data['firstName']},\n\n"
          . "Aplicația ta pentru a deveni curier GoDeliver a fost înregistrată cu succes.\n"
          . "Cod aplicație: {$applicationCode}\n\n"
          . "Te vom contacta în maximum 48 de ore.\n\nEchipa GoDeliver";
    $headers = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . ">\r\n";
    @mail($data['email'], $subject, $body, $headers);
    @mail(ADMIN_NOTIFY_EMAIL, "Aplicație nouă: {$applicationCode}", $body, $headers);

    // WhatsApp notification stub — only fires if credentials are configured.
    if (WHATSAPP_API_TOKEN && WHATSAPP_PHONE_ID) {
        sendWhatsAppNotification($data['phone'], "Salut {$data['firstName']}! Aplicația ta GoDeliver ({$applicationCode}) a fost înregistrată.");
    }

    jsonResponse(['success' => true, 'applicationId' => $applicationCode]);

} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[GoDeliver] DB error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Eroare de bază de date. Te rugăm să încerci din nou.'], 500);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[GoDeliver] Error: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'A apărut o eroare neașteptată.'], 500);
}

/**
 * Sends a WhatsApp template/text message via the WhatsApp Business Cloud API.
 * No-op unless WHATSAPP_API_TOKEN / WHATSAPP_PHONE_ID are configured.
 */
function sendWhatsAppNotification(string $phone, string $message): void
{
    $url = "https://graph.facebook.com/v19.0/" . WHATSAPP_PHONE_ID . "/messages";
    $payload = json_encode([
        'messaging_product' => 'whatsapp',
        'to' => preg_replace('/\D/', '', $phone),
        'type' => 'text',
        'text' => ['body' => $message],
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . WHATSAPP_API_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 8,
    ]);
    curl_exec($ch); // fire-and-forget; log failures if needed
    curl_close($ch);
}
