<?php
require_once '../config/security.php';
require_once 'auth_check.php'; 

$msg = '';
$msgType = ''; 

// --- 1. PROCESAR CONFIGURACIÓN GLOBAL ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) die('Error CSRF');
    
    $ip = filter_input(INPUT_POST, 'server_ip', FILTER_SANITIZE_SPECIAL_CHARS);
    $discord = filter_input(INPUT_POST, 'discord_link', FILTER_SANITIZE_URL);

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'server_ip'");
    $stmt->execute([$ip]);
    
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'discord_link'");
    $stmt->execute([$discord]);
    
    $msg = "Configuración actualizada correctamente.";
    $msgType = "success";
}

// --- 2. PROCESAR AGREGAR JUGADOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_player') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) die('Error CSRF');

    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $discord_id  = filter_input(INPUT_POST, 'discord', FILTER_SANITIZE_SPECIAL_CHARS);
    $token = bin2hex(random_bytes(16));
    $skinPath = null;
    $uploadError = false;

    if (isset($_FILES['skin']) && $_FILES['skin']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['skin']['tmp_name'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($fileTmpPath) !== 'image/png') {
            $msg = "Solo PNG permitido."; $msgType = 'error'; $uploadError = true;
        }
        if (!$uploadError) {
            $newFileName = 'skin_' . $token . '.png';
            if(move_uploaded_file($fileTmpPath, '../assets/skins/' . $newFileName)) {
                $skinPath = $newFileName;
            }
        }
    }

    if (!$uploadError && $username) {
        $stmt = $pdo->prepare("INSERT INTO players (username, token, discord_id, skin_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $token, $discord_id, $skinPath]);
        $msg = "Jugador invitado."; $msgType = 'success';
    }
}

// --- 3. PROCESAR ELIMINAR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) die('Error CSRF');
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
    $stmt->execute([$id]);
    $msg = "Eliminado."; $msgType = 'success';
}

// Obtener Configuración y Jugadores
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$players = $pdo->query("SELECT * FROM players ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="icon" href="assets/img/Tula_Logo.ico" type="image/x-icon">
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .btn { padding: 8px 15px; border-radius: 4px; color: white; border: none; cursor: pointer; text-decoration: none; }
        .btn-logout { background: #dc3545; }
        .btn-add { background: #28a745; width: 100%; }
        .btn-save { background: #6c757d; }
        .btn-del { background: #dc3545; padding: 5px 10px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .token-box { font-family: monospace; background: #eee; padding: 4px; border-radius: 4px; }
        .dash-logo { width: 50px; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><img src="../assets/img/logo.png" class="dash-logo">Dashboard</h1>
        <div>
            <span>Hola, <?= sanitize($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert <?= $msgType ?>"><?= sanitize($msg) ?></div>
    <?php endif; ?>

    <div style="display: flex; gap: 20px; margin-bottom: 30px;">
        <div style="flex: 1; background: #fff8e1; padding: 15px; border-radius: 5px; border: 1px solid #ffe082;">
            <h3>Configuración del Servidor</h3>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="update_settings">
                <div class="form-group">
                    <label>IP del Servidor (Se muestra en la carta)</label>
                    <input type="text" name="server_ip" value="<?= sanitize($settings['server_ip'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Link de Discord (Botón unirse)</label>
                    <input type="text" name="discord_link" value="<?= sanitize($settings['discord_link'] ?? '') ?>" required>
                </div>
                <button type="submit" class="btn btn-save">Guardar Cambios</button>
            </form>
        </div>

        <div style="flex: 1; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
            <h3>Invitar Nuevo Jugador</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="add_player">
                <div class="form-group">
                    <input type="text" name="username" required placeholder="Nombre Minecraft">
                </div>
                <div class="form-group">
                    <input type="file" name="skin" accept="image/png">
                </div>
                <button type="submit" class="btn btn-add">Generar Token</button>
            </form>
        </div>
    </div>

    <h3>Jugadores Activos</h3>
    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Enlace de Invitación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players as $p): ?>
            <tr>
                <td><?= sanitize($p['username']) ?></td>
                <td><span class="token-box">index.php?t=<?= $p['token'] ?></span></td>
                <td>
                    <form method="POST" onsubmit="return confirm('¿Eliminar?');">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-del">Borrar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>