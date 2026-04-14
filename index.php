<?php
require_once 'config/db.php';
require_once 'config/security.php';

// 1. Obtener y Validar Token
$token = filter_input(INPUT_GET, 't', FILTER_SANITIZE_STRING);
$player = null;
$error = null;

if (!$token) {
    $error = "Acceso denegado. Se requiere un token de invitación.";
} else {
    $stmt = $pdo->prepare("SELECT * FROM players WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $player = $stmt->fetch();

    if (!$player) {
        $error = "Token inválido o expirado.";
    }
}

// Obtener Configuración Dinámica (IP y Discord)
$res = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$server_ip = $res['server_ip'] ?? 'play.tuserver.com';
$discord_link = $res['discord_link'] ?? '#';

// 2. Determinar la URL de la Skin
$skinUrl = '';
if ($player) {
    if (!empty($player['skin_path'])) {
        $skinUrl = 'assets/skins/' . sanitize($player['skin_path']);
    } else {
        $skinUrl = 'https://minotar.net/skin/' . sanitize($player['username']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación al Servidor</title>
    <link rel="icon" href="assets/img/Tula_Logo.ico" type="image/x-icon">
    <script src="https://bs-community.github.io/skinview3d/js/skinview3d.bundle.js"></script>
    <style>
        :root {
            --bg-color: #121212;
            --card-bg: #1e1e1e;
            --accent: #dca337; 
            --Color1: rgba(254, 254, 7, 0.7);
            --Color2: #72bd4cff;
            --text: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* --- LOGO POSICIONADO --- */
      .main-logo {
    position: absolute;
    top: 6px;
    left: 3px;
    width: 150px;
    z-index: 100;

    filter:
        drop-shadow(0 0 10px rgba(254, 254, 7, 0.5))
        drop-shadow(0 0 25px rgba(254, 254, 7, 0.35));

    animation: glowBreath 3s ease-in-out infinite;
}

@keyframes glowBreath {
    0% {
        filter:
            drop-shadow(0 0 8px rgba(254, 254, 7, 0.35))
            drop-shadow(0 0 18px rgba(254, 254, 7, 0.25));
    }
    50% {
        filter:
            drop-shadow(0 0 16px rgba(254, 254, 7, 0.7))
            drop-shadow(0 0 35px rgba(254, 254, 7, 0.55));
    }
    100% {
        filter:
            drop-shadow(0 0 8px rgba(254, 254, 7, 0.35))
            drop-shadow(0 0 18px rgba(254, 254, 7, 0.25));
    }
}

        .envelope-container {
            width: 90%;
            max-width: 400px;
            perspective: 1000px;
            cursor: pointer;
            transition: transform 0.5s;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid #333;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            overflow: hidden;
            height: 535spx; 
            display: flex;
            flex-direction: column;
        }

        .cover {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
            transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .seal {
            font-size: 3rem;
            margin-bottom: 20px;
            filter: drop-shadow(0 0 10px rgba(220, 163, 55, 0.5));
        }

        .content {
            padding: 20px;
            opacity: 0;
            transition: opacity 1s ease 0.5s;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 { color: var(--Color1); margin-bottom: 5px; }
        p { color: #aaa; font-size: 0.9em; }

        #skin-container {
            width: 200px;
            height: 300px;
            margin: 10px auto;
        }

        .ip-box {
            background: #000;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #333;
            font-family: monospace;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-top: auto; 
            box-sizing: border-box;
        }

        .btn-copy {
            background: var(--Color2);
            border: none;
            color: #000;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
        }

 .btn-discord {
    position: fixed;
    bottom: 80px;
    right: 25px;
    width: 56px;
    height: 56px;
    padding: 0;
    border-radius: 50%;
    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;

    box-shadow: 0 0 15px rgba(88, 101, 242, 0.6);
}

/* Ocultamos el texto y dejamos solo el icono */
.btn-discord svg {
    width: 26px;
    height: 26px;
}

.btn-discord {
    font-size: 0; /* oculta el texto sin tocar HTML */
}


        .envelope-container.open .cover {
            transform: translateY(-100%) rotateX(20deg);
            opacity: 0;
            pointer-events: none;
        }
        .envelope-container.open .content { opacity: 1; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .envelope-container:not(.open) {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>

    <img src="assets/img/logo.png" alt="Logo" class="main-logo">

    <?php if ($error): ?>
        <div class="error-msg" style="text-align:center;">
            <h2>⛔ Error</h2>
            <p><?php echo sanitize($error); ?></p>
        </div>
    <?php else: ?>

        <div class="envelope-container" id="envelope">
            <div class="card">
                <div class="cover">
                    <div class="seal">✉️</div>
                    <h3>Tienes una invitación</h3>
                    <p>Click para abrir</p>
                    <small>Para: <?php echo sanitize($player['username']); ?></small>
                </div>

                <div class="content">
                    <h1>Bienvenido</h1>
                    <p>Hola <strong><?php echo sanitize($player['username']); ?></strong>, has sido invitado a TulaCraftRebooted.</p>
                    
                    <canvas id="skin-container"></canvas>

                    <div class="ip-box">
                        <span id="server-ip"><?php echo sanitize($server_ip); ?></span>
                        <button class="btn-copy" onclick="copyIP()">COPIAR</button>
                    </div>

                    <a href="<?php echo sanitize($discord_link); ?>" target="_blank" class="btn-discord">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36" style="width: 20px; fill: white;">
            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.71,32.65-1.82,56.6.48,80.1a105.73,105.73,0,0,0,32.22,16.26,77.7,77.7,0,0,0,7.34-11.97,68.58,68.58,0,0,1-11.85-5.66c.94-.69,1.85-1.41,2.71-2.14a74.84,74.84,0,0,0,65.23,0c.86.73,1.77,1.45,2.71,2.14a68,68,0,0,1-11.85,5.66,78.05,78.05,0,0,0,7.34,11.97,105.32,105.32,0,0,0,32.27-16.26C129.58,50.79,121,27.12,107.7,8.07ZM42.45,65.69c-6.22,0-11.38-5.71-11.38-12.69S36,40.31,42.45,40.31s11.38,5.71,11.38,12.69S48.87,65.69,42.45,65.69Zm42.24,0c-6.22,0-11.38-5.71-11.38-12.69S78.33,40.31,84.69,40.31s11.38,5.71,11.38,12.69S91.11,65.69,84.69,65.69Z"/>
        </svg>
        UNIRSE AL DISCORD
    </a>
                </div>
            </div>
        </div>

        <script>
            const envelope = document.getElementById('envelope');
            const skinCanvas = document.getElementById('skin-container');
            let viewerInitialized = false;
            const skinUrl = "<?php echo $skinUrl; ?>";

            envelope.addEventListener('click', function() {
                if (!this.classList.contains('open')) {
                    this.classList.add('open');
                    if (!viewerInitialized) {
                        initSkinViewer();
                        viewerInitialized = true;
                    }
                }
            });

            function initSkinViewer() {
                const skinViewer = new skinview3d.SkinViewer({
                    canvas: skinCanvas,
                    width: 200,
                    height: 300,
                    skin: skinUrl
                });
                skinViewer.animation = new skinview3d.WalkingAnimation();
                skinViewer.autoRotate = true;
                skinViewer.autoRotateSpeed = 0.5;
                skinViewer.controls.enableZoom = true;
                skinViewer.controls.enableRotate = true;
            }

            function copyIP() {
                const ipText = document.getElementById('server-ip').innerText;
                navigator.clipboard.writeText(ipText).then(() => {
                    const btn = document.querySelector('.btn-copy');
                    btn.innerText = "¡LISTO!";
                    setTimeout(() => btn.innerText = "COPIAR", 2000);
                });
            }
        </script>
    <?php endif; ?>
</body>
</html>