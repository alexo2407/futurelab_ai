<?php
require_once __DIR__ . '/../../config/auth.php';
requireRole('admin');
$user = currentUser();

// Organizar configs por grupo
$configsByGroup = [];
foreach ($configs as $config) {
    $group = $config['config_group'] ?: 'general';
    $configsByGroup[$group][] = $config;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - FutureLab AI</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            background: #f5f7fa;
        }
        
        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .config-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .btn-test {
            background: #28a745;
            color: white;
            border: none;
        }
        
        .btn-test:hover {
            background: #218838;
            color: white;
        }
        
        .api-status {
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
        }
        
        .api-status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .api-status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="bi bi-gear-fill me-2"></i>Configuración del Sistema</h1>
                </div>
                <div class="col-auto">
                    <a href="<?php echo BASE_URL; ?>/admin/generate" class="btn btn-light me-2">
                        <i class="bi bi-camera"></i> Generar
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/participants" class="btn btn-light me-2">
                        <i class="bi bi-list-ul"></i> Participantes
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/careers" class="btn btn-light me-2">
                        <i class="bi bi-mortarboard"></i> Carreras
                    </a>
                    <a href="<?php echo BASE_URL; ?>/wall" class="btn btn-outline-light me-2" target="_blank">
                        <i class="bi bi-display"></i> Muro
                    </a>
                    <span class="text-white me-2">
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
        <!-- API de OpenAI -->
        <div class="config-card">
            <h3><i class="bi bi-image text-success me-2"></i>Configuración de OpenAI GPT-Image-1</h3>
            <p class="text-muted">Configura tu integración con OpenAI para generación de imágenes con GPT-Image-1 (multimodal)</p>
            
            <form id="form-openai-config">
                <div class="mb-3">
                    <label for="openai_api_key" class="form-label">
                        <i class="bi bi-key"></i> API Key
                    </label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="openai_api_key" 
                            name="openai_api_key"
                            value="<?php 
                                foreach ($configs as $c) {
                                    if ($c['config_key'] === 'openai_api_key') {
                                        echo htmlspecialchars($c['config_value']);
                                    }
                                }
                            ?>"
                            placeholder="Ingresa tu API Key de OpenAI">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-openai-key">
                            <i class="bi bi-eye" id="eye-icon-openai"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        Obtén tu API key en: 
                        <a href="https://platform.openai.com/api-keys" target="_blank">
                            OpenAI Platform
                        </a>
                    </small>
                </div>
                
                <div class="mb-3">
                    <label for="openai_model" class="form-label">
                        <i class="bi bi-cpu"></i> Modelo
                    </label>
                    <select class="form-select" id="openai_model" name="openai_model">
                        <?php
                        $currentModel = 'gpt-image-1';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'openai_model') {
                                $currentModel = $c['config_value'];
                            }
                        }
                        
                        $models = [
                            'gpt-image-1' => 'GPT-Image-1 (Editor - Transforma foto del participante)',
                            'dall-e-3' => 'DALL-E 3 (Generador - Crea imagen nueva desde texto)'
                        ];
                        
                        foreach ($models as $value => $label) {
                            $selected = ($value === $currentModel) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">
                        <strong>GPT-Image-1:</strong> Recomendado. Mantiene la cara del participante y transforma el estilo.<br>
                        <strong>DALL-E 3:</strong> Ignora la foto del participante y crea una persona ficticia basada solo en el prompt.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="openai_image_size" class="form-label">
                        <i class="bi bi-aspect-ratio"></i> Tamaño de Imagen
                    </label>
                    <select class="form-select" id="openai_image_size" name="openai_image_size">
                        <?php
                        $currentSize = '1024x1024';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'openai_image_size') {
                                $currentSize = $c['config_value'];
                            }
                        }
                        
                        $sizes = [
                            '1024x1024' => '1024x1024 (Cuadrado 1:1)',
                            '1024x1792' => '1024x1792 (Vertical 9:16 - Historias)',
                            '1792x1024' => '1792x1024 (Horizontal 16:9)'
                        ];
                        
                        foreach ($sizes as $value => $label) {
                            $selected = ($value === $currentSize) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="openai_image_quality" class="form-label">
                        <i class="bi bi-stars"></i> Calidad
                    </label>
                    <select class="form-select" id="openai_image_quality" name="openai_image_quality">
                        <?php
                        $currentQuality = 'standard';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'openai_image_quality') {
                                $currentQuality = $c['config_value'];
                            }
                        }
                        
                        // Mapear valores antiguos a nuevos válidos
                        if ($currentQuality === 'medium' || $currentQuality === 'low') $currentQuality = 'standard';
                        if ($currentQuality === 'high' || $currentQuality === 'best') $currentQuality = 'hd';
                        
                        $qualities = [
                            'standard' => 'Standard (Calidad Normal - Más rápido)',
                            'hd' => 'HD (Alta Definición - Más detalle)'
                        ];
                        
                        foreach ($qualities as $value => $label) {
                            $selected = ($value === $currentQuality) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="openai_input_fidelity" class="form-label">
                        <i class="bi bi-person-bounding-box"></i> Fidelidad de Entrada (GPT-Image-1)
                    </label>
                    <select class="form-select" id="openai_input_fidelity" name="openai_input_fidelity">
                        <?php
                        $currentFidelity = 'high';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'openai_input_fidelity') {
                                $currentFidelity = $c['config_value'];
                            }
                        }
                        
                        $fidelities = [
                            'high' => 'Alta (Recomendado - Preserva identidad)',
                            'low' => 'Baja (Más creatividad - Menos parecido)'
                        ];
                        
                        foreach ($fidelities as $value => $label) {
                            $selected = ($value === $currentFidelity) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3 form-check">
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="openai_enabled" 
                        name="openai_enabled"
                        <?php
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'openai_enabled' && $c['config_value'] === '1') {
                                echo 'checked';
                            }
                        }
                        ?>>
                    <label class="form-check-label" for="openai_enabled">
                        Habilitar integración con OpenAI
                    </label>
                </div>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Importante:</strong> Para usar la función "Editar con foto", asegúrese de seleccionar <code>gpt-image-1</code>. Si selecciona DALL-E 3, la foto subida por el participante será ignorada.
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Configuración
                    </button>
                    <button type="button" class="btn btn-info" id="btn-test-openai">
                        <i class="bi bi-lightning"></i> Probar Conexión
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Información del Sistema -->
        <div class="config-card">
            <h3><i class="bi bi-info-circle text-info me-2"></i>Información del Sistema</h3>
            <table class="table table-sm">
                <tr>
                    <td><strong>Versión PHP:</strong></td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong>Directorio de Storage:</strong></td>
                    <td><?php echo STORAGE_PATH; ?></td>
                </tr>
                <tr>
                    <td><strong>Base URL:</strong></td>
                    <td><?php echo BASE_URL; ?></td>
                </tr>
                <tr>
                    <td><strong>Tamaño máximo de upload:</strong></td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle OpenAI password visibility
        document.getElementById('toggle-openai-key').addEventListener('click', function() {
            const input = document.getElementById('openai_api_key');
            const icon = document.getElementById('eye-icon-openai');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Guardar configuración de OpenAI
        document.getElementById('form-openai-config').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>/api/config/save', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Guardado!',
                        text: 'Configuración de OpenAI guardada exitosamente',
                        confirmButtonColor: '#28a745',
                        timer: 2000
                    });
                } else {
                    throw new Error(data.error || 'Error al guardar');
                }
                
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // Probar conexión OpenAI
        document.getElementById('btn-test-openai').addEventListener('click', async function() {
            const apiKey = document.getElementById('openai_api_key').value;
            
            if (!apiKey) {
                Swal.fire({
                    icon: 'warning',
                    title: 'API Key Requerida',
                    text: 'Por favor ingresa tu API Key de OpenAI primero',
                    confirmButtonColor: '#28a745'
                });
                return;
            }
            
            const btn = this;
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Probando...';
            
            try {
                const formData = new FormData();
                formData.append('api_key', apiKey);
                
                const response = await fetch('<?php echo BASE_URL; ?>/api/config/test-openai', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Conexión Exitosa!',
                        text: data.message,
                        confirmButtonColor: '#28a745'
                    });
                } else {
                    throw new Error(data.error || 'Error al probar conexión');
                }
                
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: error.message || 'No se pudo conectar con OpenAI. Verifica tu API Key.',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    </script>
</body>
</html>
