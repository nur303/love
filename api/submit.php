<?php
/**
 * API Endpoint: Submit Response
 * Records 'yes' / 'no' choices and optional messages
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

require_once __DIR__ . '/../db.php';

// Helper to determine visitor IP
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

// Read raw body (JSON) or standard POST parameters
$inputData = [];
$rawBody = file_get_contents('php://input');
if (!empty($rawBody)) {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $inputData = $decoded;
    }
}
if (empty($inputData)) {
    $inputData = $_POST;
}

$choice = isset($inputData['choice']) ? strtolower(trim($inputData['choice'])) : null;
$message = isset($inputData['message']) ? trim($inputData['message']) : null;
$responseId = isset($inputData['response_id']) ? intval($inputData['response_id']) : null;

// Validate
if ($choice !== null && !in_array($choice, ['yes', 'no'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid choice. Must be "yes" or "no".']);
    exit;
}

$visitorIp = getClientIp();
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);

try {
    $pdo = getDB();

    // If an existing responseId is provided and only updating a message
    if ($responseId && $message !== null) {
        $stmt = $pdo->prepare("UPDATE responses SET message = :message WHERE id = :id");
        $stmt->execute([
            ':message' => $message,
            ':id' => $responseId,
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Message updated successfully',
            'id' => $responseId,
        ]);
        exit;
    }

    // Default to 'yes' if choice wasn't provided but message was
    if (!$choice) {
        $choice = 'yes';
    }

    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("
        INSERT INTO responses (choice, message, visitor_ip, user_agent, created_at)
        VALUES (:choice, :message, :visitor_ip, :user_agent, :created_at)
    ");

    $stmt->bindValue(':choice', $choice, PDO::PARAM_STR);
    $stmt->bindValue(':message', $message, $message !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':visitor_ip', $visitorIp, PDO::PARAM_STR);
    $stmt->bindValue(':user_agent', $userAgent, PDO::PARAM_STR);
    $stmt->bindValue(':created_at', $now, PDO::PARAM_STR);
    $stmt->execute();

    $newId = (int) $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Response recorded successfully',
        'id' => $newId,
        'choice' => $choice,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save response: ' . $e->getMessage(),
    ]);
}
