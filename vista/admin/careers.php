<?php
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Carreras - FutureLab AI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        body {
            color: var(--text-primary);
        }
        
        .header {
            background: rgba(0, 27, 103, 0.8);
            backdrop-filter: blur(10px);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .career-card {
            margin-bottom: 15px;
        }
        
        /* Handled by global .card-elevated class */
        
        .career-card-body {
            padding: 20px;
        }
        
        .has-prompt {
            border-left: 4px solid #28a745;
        }
        
        .has-image {
            border-left: 4px solid #007bff;
        }
        
        .has-both {
            border-left: 4px solid #6f42c1;
        }
        
        .reference-thumb {
            max-width: 80px;
            max-height: 80px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
               <div class="col">
                    <h1><i class="bi bi-mortarboard-fill me-2 text-accent"></i>Gestión de Carreras</h1>
                    <p class="mb-0">Personaliza prompts e imágenes de referencia para Gemini AI</p>
                </div>
                <div class="col-auto">
                    <a href="<?php echo BASE_URL; ?>/admin/generate" class="btn btn-primary me-2">
                        <i class="bi bi-camera"></i> Generar
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/participants" class="btn btn-primary me-2">
                        <i class="bi bi-list-ul"></i> Participantes
                    </a>
                    <a href="<?php echo BASE_URL; ?>/wall" class="btn btn-outline-light me-2" target="_blank">
                        <i class="bi bi-display"></i> Muro
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/config" class="btn btn-outline-light me-2">
                        <i class="bi bi-gear"></i> Config
                    </a>
                    <span class="text-white-50 me-2">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                    </span>
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="row mb-3">
            <div class="col">
                <div class="alert alert-info bg-transparent border-info text-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Personalización por carrera:</strong> Define un prompt único y/o imagen de referencia para cada carrera. 
                    El worker usará esta información al generar imágenes con Gemini AI.
                </div>
            </div>
        </div>
        
        <?php foreach ($carreras as $carrera): 
            $hasPrompt = !empty($carrera['ai_prompt']);
            $hasImage = !empty($carrera['reference_image_path']) || !empty($carrera['reference_image_url']);
            $cardClass = 'career-card';
            
            if ($hasPrompt && $hasImage) {
                $cardClass .= ' has-both';
            } elseif ($hasPrompt) {
                $cardClass .= ' has-prompt';
            } elseif ($hasImage) {
                $cardClass .= ' has-image';
            }
        ?>
            <div class="<?php echo $cardClass; ?> card fade-in">
                <div class="career-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">
                                <?php echo htmlspecialchars($carrera['name']); ?>
                                <?php if (!$carrera['is_active']): ?>
                                    <span class="badge bg-secondary">Inactiva</span>
                                <?php endif; ?>
                            </h5>
                            <small class="text-white-50">
                                <?php echo htmlspecialchars($carrera['category'] ?? 'Sin categoría'); ?>
                            </small>
                            
                            <?php if ($hasPrompt): ?>
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        <i class="bi bi-chat-text"></i> Tiene prompt personalizado
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($hasImage): ?>
                                <div class="mt-1">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-image"></i> Tiene imagen de referencia
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3 text-center">
                            <?php if (!empty($carrera['reference_image_path'])): ?>
                                <img src="<?php echo STORAGE_URL . str_replace('/storage', '', $carrera['reference_image_path']); ?>" 
                                     class="reference-thumb" 
                                     alt="Referencia">
                            <?php elseif (!empty($carrera['reference_image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($carrera['reference_image_url']); ?>" 
                                     class="reference-thumb" 
                                     alt="Referencia">
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="bi bi-image" style="font-size: 3rem; opacity: 0.3;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-3 text-end">
                            <a href="<?php echo BASE_URL; ?>/admin/careers/edit?id=<?php echo $carrera['id']; ?>" 
                               class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <footer class="text-center py-4 mt-5 text-white-50">
        <div class="container">
            <p class="mb-0">Desarrollado por Alberto Calero</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
