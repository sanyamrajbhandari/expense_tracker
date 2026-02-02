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
?>
