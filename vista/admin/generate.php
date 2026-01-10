<?php
require_once __DIR__ . '/../../config/auth.php';
requireRole(['admin', 'operator']);
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Participante - FutureLab AI</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        #video-container {
            position: relative;
            background: #000;
            aspect-ratio: 4/3;
            max-height: 400px;
            margin: 20px auto;
            border-radius: 10px;
            overflow: hidden;
        }
        
        #video, #preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        #canvas {
            display: none;
        }
        
        .btn-capture {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .btn-capture:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        #qr-modal .modal-dialog {
            max-width: 600px;
        }
        
        #qr-image {
            max-width: 100%;
            height: auto;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="bi bi-camera-fill me-2"></i>Generar Participante</h1>
            <p class="mb-0">Captura la foto y completa los datos</p>
            <div class="mt-3">
                <small>Usuario: <?php echo htmlspecialchars($user['username']); ?> | 
                <a href="<?php echo BASE_URL; ?>/admin/participants" class="text-white">
                    <i class="bi bi-list-ul"></i> Participantes
                </a> |
                <a href="<?php echo BASE_URL; ?>/admin/careers" class="text-white">
                    <i class="bi bi-mortarboard"></i> Carreras
                </a> |
                <a href="<?php echo BASE_URL; ?>/admin/config" class="text-white">
                    <i class="bi bi-gear"></i> Config
                </a> |
                <a href="<?php echo BASE_URL; ?>/auth/logout" class="text-white">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
                </small>
            </div>
        </div>
        
        <!-- Formulario Principal -->
        <div id="capture-form" class="p-4">
            <div id="video-container">
                <video id="video" autoplay playsinline></video>
                <img id="preview-image" style="display: none;" alt="Preview">
            </div>
            <canvas id="canvas"></canvas>
            
            <div class="text-center mb-4">
                <button id="btn-start-camera" class="btn btn-primary btn-lg">
                    <i class="bi bi-camera-video"></i> Iniciar Cámara
                </button>
                <button id="btn-capture" class="btn btn-capture btn-lg" style="display: none;">
                    <i class="bi bi-camera"></i> Capturar Foto
                </button>
                <button id="btn-retake" class="btn btn-warning btn-lg" style="display: none;">
                    <i class="bi bi-arrow-counterclockwise"></i> Tomar Otra
                </button>
            </div>
            
            <form id="form-participant">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="career_id" class="form-label">Carrera</label>
                    <select class="form-select" id="career_id" name="career_id" required>
                        <option value="">Selecciona una carrera...</option>
                        <?php foreach($carreras as $carrera): ?>
                            <option value="<?php echo $carrera['id']; ?>">
                                <?php echo htmlspecialchars($carrera['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg" id="btn-submit" disabled>
                        <i class="bi bi-check-circle"></i> Generar Participante
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Pantalla de Resultado -->
        <div id="result-screen" class="p-4" style="display: none;">
            <div class="text-center">
                <div id="loading-state">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h3>Procesando imagen con IA...</h3>
                    <p class="text-muted">Esto puede tomar unos segundos</p>
                </div>
                
                <div id="success-state" style="display: none;">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">¡Participante Generado!</h3>
                    
                    <div id="result-image-container" class="my-4">
                        <img id="result-image" class="img-fluid rounded" alt="Resultado" style="max-height: 300px;">
                    </div>
                    
                    <button class="btn btn-primary btn-lg mb-3" onclick="showQR()">
                        <i class="bi bi-qr-code"></i> Ver Código QR
                    </button>
                    
                    <br>
                    
                    <button class="btn btn-success btn-lg" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Generar Otro Participante
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal QR -->
    <div class="modal fade" id="qr-modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Código QR</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="qr-image" alt="QR Code">
                    <p class="mt-3 mb-0">
                        <strong>Código:</strong> <span id="qr-public-code"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const previewImage = document.getElementById('preview-image');
        const btnStartCamera = document.getElementById('btn-start-camera');
        const btnCapture = document.getElementById('btn-capture');
        const btnRetake = document.getElementById('btn-retake');
        const btnSubmit = document.getElementById('btn-submit');
        const form = document.getElementById('form-participant');
        
        let stream = null;
        let capturedBlob = null;
        let currentParticipantId = null;
        let qrUrl = null;
        let statusUrl = null;
        
        // Iniciar cámara
        btnStartCamera.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'user', width: 1280, height: 720 } 
                });
                video.srcObject = stream;
                video.style.display = 'block';
                previewImage.style.display = 'none';
                btnStartCamera.style.display = 'none';
                btnCapture.style.display = 'inline-block';
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Cámara',
                    text: 'No se pudo acceder a la cámara: ' + err.message,
                    confirmButtonColor: '#667eea'
                });
            }
        });
        
        // Capturar foto
        btnCapture.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            canvas.toBlob((blob) => {
                capturedBlob = blob;
                previewImage.src = URL.createObjectURL(blob);
                video.style.display = 'none';
                previewImage.style.display = 'block';
                btnCapture.style.display = 'none';
                btnRetake.style.display = 'inline-block';
                btnSubmit.disabled = false;
            }, 'image/jpeg', 0.9);
        });
        
        // Tomar otra foto
        btnRetake.addEventListener('click', () => {
            video.style.display = 'block';
            previewImage.style.display = 'none';
            btnRetake.style.display = 'none';
            btnCapture.style.display = 'inline-block';
            btnSubmit.disabled = true;
            capturedBlob = null;
        });
        
        // Submit formulario
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!capturedBlob) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Foto Requerida',
                    text: 'Debes capturar una foto primero',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            
            const formData = new FormData(form);
            formData.append('photo', capturedBlob, 'photo.jpg');
            
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generando...';
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/participants/create', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (!data.ok) {
                    throw new Error(data.error || 'Error desconocido');
                }
                
                // Guardar datos
                currentParticipantId = data.participant_id;
                qrUrl = data.qr_url;
                statusUrl = data.status_url;
                
                // Mostrar pantalla de resultado
                document.getElementById('capture-form').style.display = 'none';
                document.getElementById('result-screen').style.display = 'block';
                
                // Iniciar polling
                pollStatus();
                
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message,
                    confirmButtonColor: '#667eea'
                });
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Generar Participante';
            }
        });
        
        // Polling de estado
        async function pollStatus() {
            try {
                const response = await fetch(statusUrl);
                const data = await response.json();
                
                if (data.status === 'done') {
                    // Completado!
                    document.getElementById('loading-state').style.display = 'none';
                    document.getElementById('success-state').style.display = 'block';
                    
                    if (data.result_image_url) {
                        document.getElementById('result-image').src = data.result_image_url;
                    } else {
                        // Si no hay imagen generada, mostrar la original
                        document.getElementById('result-image').src = previewImage.src;
                    }
                } else if (data.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Procesar',
                        text: data.error_message || 'Error desconocido',
                        confirmButtonColor: '#667eea'
                    }).then(() => {
                        resetForm();
                    });
                } else {
                    // Seguir esperando
                    setTimeout(pollStatus, 3000);
                }
            } catch (err) {
                console.error('Error en polling:', err);
                setTimeout(pollStatus, 5000);
            }
        }
        
        // Mostrar QR
        function showQR() {
            if (qrUrl) {
                document.getElementById('qr-image').src = qrUrl;
                document.getElementById('qr-public-code').textContent = currentParticipantId;
                new bootstrap.Modal(document.getElementById('qr-modal')).show();
            }
        }
        
        // Reset formulario
        function resetForm() {
            document.getElementById('result-screen').style.display = 'none';
            document.getElementById('capture-form').style.display = 'block';
            form.reset();
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="bi bi-check-circle"></i> Generar Participante';
            btnCapture.style.display = 'none';
            btnRetake.style.display = 'none';
            btnStartCamera.style.display = 'inline-block';
            video.style.display = 'none';
            previewImage.style.display = 'none';
            capturedBlob = null;
            currentParticipantId = null;
            
            // Reiniciar estados
            document.getElementById('loading-state').style.display = 'block';
            document.getElementById('success-state').style.display = 'none';
            
            // Detener stream si existe
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }
    </script>
</body>
</html>
