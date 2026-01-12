<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Futuro - FutureLab AI</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #001B67;
            --secondary-color: #195C9C;
            --accent-color: #F2AE3D;
            --primary-glow: radial-gradient(circle at 50% 50%, rgba(0, 27, 103, 0.5), transparent 70%);
            --secondary-glow: radial-gradient(circle at 100% 0%, rgba(25, 92, 156, 0.4), transparent 50%);
        }

        body {
            background-color: #000a1f;
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            overflow-x: hidden;
            position: relative;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -1;
            background: 
                radial-gradient(circle at 15% 50%, rgba(0, 27, 103, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(25, 92, 156, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(242, 174, 61, 0.1) 0%, transparent 50%);
            animation: pulseGlow 8s ease-in-out infinite alternate;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem 2rem;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -50%;
            width: 200%;
            height: 100%;
            background: linear-gradient(
                to right,
                transparent,
                rgba(255, 255, 255, 0.05),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite linear;
            pointer-events: none;
        }

        @keyframes shine {
            0% { left: -50%; }
            100% { left: 150%; }
        }

        /* Typography */
        h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff 0%, #F2AE3D 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        h2.status-text {
            font-weight: 400;
            font-size: 1.1rem;
            color: #bdcce0;
            margin-bottom: 2rem;
            min-height: 1.5em;
        }

        /* Premium Loader */
        .premium-loader {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
        }

        .premium-loader svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .premium-loader circle {
            fill: none;
            stroke-width: 4;
            stroke-linecap: round;
        }

        .loader-bg {
            stroke: rgba(255, 255, 255, 0.1);
        }

        .loader-progress {
            stroke: url(#gradient);
            stroke-dasharray: 283;
            stroke-dashoffset: 283;
            animation: progress 20s linear forwards; /* Simulated approximate time */
        }

        @keyframes progress {
            0% { stroke-dashoffset: 283; }
            90% { stroke-dashoffset: 20; } /* Stall at 90% */
            100% { stroke-dashoffset: 0; }
        }
        
        /* Pulse Rings */
        .pulse-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 1px solid rgba(242, 174, 61, 0.3);
            animation: ripple 2s infinite cubic-bezier(0, 0.2, 0.8, 1);
        }

        @keyframes ripple {
            0% { width: 0; height: 0; opacity: 1; border-width: 0; }
            100% { width: 200%; height: 200%; opacity: 0; border-width: 1px; }
        }

        /* Result Image */
        .result-image-container {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.1), 0 20px 40px rgba(0,0,0,0.4);
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .result-image-container.visible {
            transform: scale(1);
            opacity: 1;
        }

        .result-image {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Buttons */
        .btn-premium {
            background: linear-gradient(135deg, #195C9C 0%, #001B67 100%);
            color: white;
            border: 1px solid #F2AE3D;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.02em;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            text-decoration: none;
            display: inline-block;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 27, 103, 0.6);
            color: #F2AE3D;
        }
        
        .logo {
            margin-bottom: 2rem;
            font-size: 1.5rem;
            font-weight: 700;
            opacity: 0.8;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Transitions */
        .fade-out {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
            position: absolute;
            width: 100%;
            transition: all 0.5s ease;
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeUp 0.6s ease forwards;
        }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Message rotator */
        .message-rotator {
            height: 20px;
            overflow: hidden;
            position: relative;
        }

        /* Mobile Fullscreen Optimization & Immersive Experience */
        @media (max-width: 768px) {
            body {
                align-items: flex-start;
                background: #000a1f;
                padding: 0;
                margin: 0;
            }
            
            .glass-card {
                max-width: 100%;
                min-height: 100vh;
                border: none;
                border-radius: 0;
                margin: 0;
                padding: 0; /* Remove padding to allow image to touch edges */
                display: flex;
                flex-direction: column;
                justify-content: center;
                background: transparent;
                backdrop-filter: none;
                box-shadow: none;
            }
            
            .logo {
                margin-top: 1.5rem;
                position: relative;
                z-index: 20;
                text-shadow: 0 2px 4px rgba(0,0,0,0.8);
            }

            /* WAITING STATE (Centered with dark background) */
            #waiting-state {
                padding: 2rem;
                background: radial-gradient(circle at center, rgba(0,27,103,1), #000a1f);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                z-index: 10;
            }
            
            /* SUCCESS STATE (Immersive Overlay) */
            #success-state {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 100;
                display: flex;
                flex-direction: column;
                justify-content: flex-end; /* Controls at bottom */
                padding-bottom: 2rem;
            }
            
            /* Image covers entire screen */
            #result-container {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                z-index: 0;
            }
            
            #final-image {
                width: 100%;
                height: 100%;
                object-fit: cover; /* Fills screen cropping excess */
                object-position: center;
            }
            
            /* Floating Title */
            #success-state > .mb-4:first-child {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 5rem 1rem 3rem; /* Space for top bars */
                background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
                z-index: 10;
                text-shadow: 0 2px 4px rgba(0,0,0,0.8);
                pointer-events: none;
            }
            
            #success-state h1 { font-size: 2.5rem; margin-bottom: 0; }
            #success-state p { opacity: 0.9; font-size: 1rem; }

            /* Buttons & Bottom Controls */
            #download-link {
                margin: 0 1.5rem 1rem;
                position: relative;
                z-index: 20;
                box-shadow: 0 4px 15px rgba(0,0,0,0.5);
                border: 1px solid rgba(255,255,255,0.3);
                background: rgba(0, 27, 103, 0.8);
                backdrop-filter: blur(10px);
            }
            
            #success-state p.small {
                position: relative;
                z-index: 20;
                text-shadow: 0 1px 2px rgba(0,0,0,1);
                margin-bottom: 1rem;
            }

            /* Bottom Gradient for readability */
            #success-state::before {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 40%;
                background: linear-gradient(0deg, rgba(0,0,0,0.95) 0%, transparent 100%);
                z-index: 5;
                pointer-events: none;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    
    <div class="glass-card text-center">
        <div class="logo">
            <i class="bi bi-robot me-2"></i> FutureLab AI
        </div>

        <!-- Waiting State -->
        <div id="waiting-state">
            <div class="premium-loader">
                <div class="pulse-ring"></div>
                <!-- Define Gradient -->
                <svg>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#195C9C" />
                            <stop offset="100%" stop-color="#F2AE3D" />
                        </linearGradient>
                    </defs>
                    <circle cx="60" cy="60" r="45" class="loader-bg"></circle>
                    <circle cx="60" cy="60" r="45" class="loader-progress"></circle>
                </svg>
                <div class="position-absolute top-50 start-50 translate-middle">
                    <i class="bi bi-stars text-white" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            
            <h1 class="mb-2">Creando tu Futuro</h1>
            <p class="text-white-50 mb-4"><?php echo htmlspecialchars($participante['first_name']); ?></p>
            
            <h2 class="status-text" id="status-text">Analizando parámetros...</h2>
            <p class="text-white-50 mt-3 small opacity-75">
                <i class="bi bi-clock-history me-1"></i> Tu imagen estará lista en aproximadamente un minuto
            </p>
        </div>

        <!-- Success State -->
        <div id="success-state" style="display: none;">
            <div class="mb-4">
                <h1 class="mb-2">¡Completado!</h1>
                <p class="text-white-50">Aquí tienes tu visión</p>
            </div>
            
            <div class="result-image-container mb-4" id="result-container">
                <img id="final-image" class="result-image" alt="Resultado IA">
            </div>
            
            <a id="download-link" href="#" download="futurelab_ai_result.jpg" class="btn-premium mb-3">
                <i class="bi bi-download me-2"></i> Guardar Imagen
            </a>
            <p class="text-white-50 small mb-0">Mantén presionado para descargar</p>
        </div>
        
        <!-- Error State -->
        <div id="error-state" style="display: none;">
            <div class="mb-4 text-danger">
                <i class="bi bi-exclamation-octagon" style="font-size: 3.5rem;"></i>
            </div>
            <h1 class="mb-2">Algo salió mal</h1>
            <p id="error-msg" class="text-white-50 mb-4">No pudimos completar el proceso.</p>
            <button onclick="location.reload()" class="btn btn-outline-light rounded-pill px-4">
                Intentar de nuevo
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        const participantId = <?php echo $participante['id']; ?>;
        const statusUrl = '<?php echo BASE_URL; ?>/api/participants/status?id=' + participantId;
        
        const waitingState = document.getElementById('waiting-state');
        const successState = document.getElementById('success-state');
        const errorState = document.getElementById('error-state');
        const statusText = document.getElementById('status-text');
        const finalImage = document.getElementById('final-image');
        const resultContainer = document.getElementById('result-container');
        
        // Status messages loop
        const messages = [
            "Analizando rasgos faciales...",
            "Consultando modelos de IA...",
            "Mejorando resolución...",
            "Aplicando estilo futurista...",
            "Finalizando detalles..."
        ];
        let msgIndex = 0;
        
        const msgInterval = setInterval(() => {
            if (waitingState.style.display !== 'none') {
                statusText.style.opacity = 0;
                setTimeout(() => {
                    msgIndex = (msgIndex + 1) % messages.length;
                    statusText.textContent = messages[msgIndex];
                    statusText.style.opacity = 1;
                }, 300);
            }
        }, 4000);
        
        // Transition Helper
        statusText.style.transition = 'opacity 0.3s ease';

        // Polling function
        async function checkStatus() {
            try {
                const response = await fetch(statusUrl);
                const data = await response.json();
                
                if (data.status === 'done') {
                    clearInterval(msgInterval);
                    showSuccess(data.result_image_url);
                } else if (data.status === 'error') {
                    clearInterval(msgInterval);
                    showError(data.error_message);
                } else {
                    // Keep polling
                    setTimeout(checkStatus, 2000);
                }
            } catch (err) {
                console.error('Error polling:', err);
                setTimeout(checkStatus, 3000);
            }
        }
        
        function showSuccess(imageUrl) {
            // Preload image
            const img = new Image();
            img.src = imageUrl;
            img.onload = () => {
                transitionToSuccess(imageUrl);
            };
        }
        
        function transitionToSuccess(imageUrl) {
            waitingState.classList.add('fade-out');
            
            setTimeout(() => {
                waitingState.style.display = 'none';
                successState.style.display = 'block';
                successState.classList.add('fade-in');
                
                finalImage.src = imageUrl;
                document.getElementById('download-link').href = imageUrl;
                
                // Trigger reveal animation
                setTimeout(() => {
                    resultContainer.classList.add('visible');
                    
                    // Celebration confetti
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: { y: 0.6 },
                        colors: ['#001B67', '#195C9C', '#F2AE3D', '#ffffff']
                    });
                }, 100);
                
            }, 500);
        }
        
        function showError(msg) {
            waitingState.style.display = 'none';
            errorState.style.display = 'block';
            errorState.classList.add('fade-in');
            document.getElementById('error-msg').textContent = msg || 'Error desconocido';
        }
        
        // Start logic
        <?php if ($participante['status'] === 'done'): ?>
            showSuccess('<?php echo !empty($participante['result_image_path']) ? STORAGE_URL . str_replace('/storage', '', $participante['result_image_path']) : ''; ?>');
        <?php elseif ($participante['status'] === 'error'): ?>
            showError('<?php echo $participante['error_message']; ?>');
        <?php else: ?>
            checkStatus();
        <?php endif; ?>
    </script>
</body>
</html>
