<?php
session_start();
require_once 'db.php';

// 1. Sanitización General
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// 2. Generación y Validación CSRF
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 3. Protección contra Fuerza Bruta (Rate Limiting)
function checkLoginAttempts($pdo, $ip) {
    // Bloquear si hay más de 5 intentos en los últimos 15 minutos (900 seg)
    $stmt = $pdo->prepare("SELECT count(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > ?");
    $stmt->execute([$ip, time() - 900]);
    $count = $stmt->fetchColumn();
    
    return $count < 5;
}

function logFailedLogin($pdo, $ip) {
    $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, ?)");
    $stmt->execute([$ip, time()]);
}

function clearLoginAttempts($pdo, $ip) {
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
}
?>