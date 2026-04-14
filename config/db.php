<?php
// Desactivar display_errors en producción
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'vixodevs_TulaCraft';
$user = 'Admin';
$pass = 'Admin123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // Importante para seguridad real
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Nunca mostrar el error real al usuario
    error_log($e->getMessage());
    die('Error de conexión.');
}
?>