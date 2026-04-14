<?php
require_once '../config/security.php';

$error = '';
//$hash = '';
$ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar CSRF
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_STRING);
    if (!verifyCsrfToken($token)) {
        die('Error de validación de seguridad (CSRF).');
    }

    // 2. Verificar Rate Limiting
    if (!checkLoginAttempts($pdo, $ip)) {
        $error = "Demasiados intentos. Por favor espera 15 minutos.";
    } else {
        // 3. Sanitizar Entradas
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = $_POST['password']; // No sanitizar pass, se hashea o verifica

        // 4. Buscar Usuario (Prepared Statement)
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        // solo si necesitas cambiar la password de admin -> $hash = password_hash($password, PASSWORD_DEFAULT);
        /*<?php if ($hash): ?>
            //<div class="error"><?php echo sanitize($hash); ?></div>
        <?php endif; ?>*/
        

        // 5. Verificar Contraseña
        if ($user && password_verify($password, $user['password_hash'])) {
            // LOGIN EXITOSO
            
            // Regenerar ID de sesión para prevenir Session Fixation
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Limpiar intentos fallidos previos
            clearLoginAttempts($pdo, $ip);
            
            header("Location: dashboard.php");
            exit;
        } else {
            // LOGIN FALLIDO
            logFailedLogin($pdo, $ip);
            // Mensaje genérico para no revelar existencia de usuario
            $error = "Credenciales inválidas."; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>LoginTula</title>
    <link rel="icon" href="assets/img/Tula_Logo.ico" type="image/x-icon">
    <style>
        /* CSS Básico para centrar */
        body { background: #1a1a1a; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; }
        .login-box { background: #2d2d2d; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); width: 300px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #444; background: #333; color: white; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { color: #ff6b6b; text-align: center; margin-bottom: 10px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="text-align:center">Admin Panel</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo sanitize($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            
            <label>Usuario</label>
            <input type="text" name="username" required autocomplete="off">
            
            <label>Contraseña</label>
            <input type="password" name="password" required autocomplete="current-password">
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>