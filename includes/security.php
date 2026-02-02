<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape HTML for output (XSS Protection)
 */
function e($string) {
    if ($string === null) return "";
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * API Response Helper for CSRF validation
 */
function verify_csrf_api() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') return;

    // 1. Check Headers (X-CSRF-TOKEN)
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

    // 2. Check $_POST
    if (!$token) {
        $token = $_POST['csrf_token'] ?? null;
    }

    // 3. Check JSON Body (Cache it so it can be read again)
    if (!$token) {
        $data = get_json_input();
        $token = $data['csrf_token'] ?? null;
    }
    
    if (!validate_csrf_token($token)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'error' => 'CSRF token invalid or missing'
        ]);
        exit;
    }
}

/**
 * Helper to get JSON input without exhausting the stream
 */
function get_json_input() {
    static $json_data = null;
    if ($json_data === null) {
        $raw = file_get_contents("php://input");
        $json_data = json_decode($raw, true) ?: [];
    }
    return $json_data;
}
?>
