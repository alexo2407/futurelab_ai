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
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/vista/css/estilos.css">
    
    <style>
        .api-status {
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
        }
        
        .api-status.success {
            background: rgba(212, 237, 218, 0.3);
            color: #22c55e;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .api-status.error {
            background: rgba(248, 215, 218, 0.3);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
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
        <!-- Selector de Proveedor de IA -->
        <div class="card">
            <h3><i class="bi bi-cpu text-primary me-2"></i>Proveedor de IA para Generación de Imágenes</h3>
            <p class="text-muted">Selecciona el proveedor de IA que deseas usar para generar las imágenes de participantes</p>
            
            <form id="form-ai-provider">
                <div class="mb-3">
                    <label for="ai_provider" class="form-label">
                        <i class="bi bi-gear"></i> Proveedor Activo
                    </label>
                    <select class="form-select" id="ai_provider" name="ai_provider">
                        <?php
                        $currentProvider = 'openai';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'ai_provider') {
                                $currentProvider = $c['config_value'];
                            }
                        }
                        
                        $providers = [
                            'openai' => 'OpenAI (GPT-Image-1 / DALL-E 3)',
                            'falai' => 'fal.ai (Gemini 3 Pro Image Preview)'
                        ];
                        
                        foreach ($providers as $value => $label) {
                            $selected = ($value === $currentProvider) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">
                        Cambia entre proveedores según tus necesidades. Asegúrate de configurar el API Key correspondiente.
                    </div>
                </div>

                <!-- UNIFIED FALLBACK MODE TOGGLE (Global) -->
                <div class="alert alert-warning d-flex align-items-center justify-content-between shadow-sm border-warning mb-4" role="alert">
                    <div>
                        <div class="fw-bold fs-5 text-warning-emphasis">
                            <i class="bi bi-shield-exclamation me-2"></i>Modo Contingencia
                        </div>
                        <div class="small text-muted mt-1">
                            Forzar generación local (sin consumo de API). Útil para emergencias.
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input 
                            class="form-check-input" 
                            type="checkbox" 
                            role="switch" 
                            id="ai_fallback_mode" 
                            name="ai_fallback_mode"
                            style="cursor: pointer; width: 3.5em; height: 1.75em;"
                            <?php
                            foreach ($configs as $c) {
                                if ($c['config_key'] === 'ai_fallback_mode' && $c['config_value'] === '1') {
                                    echo 'checked';
                                }
                            }
                            ?>>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Proveedor
                </button>
            </form>
        </div>
        
        <!-- API de fal.ai -->
        <div class="card" id="falai-config">
            <h3><i class="bi bi-image text-primary me-2"></i>Configuración de fal.ai</h3>
            <p class="text-muted">Configura tu integración con fal.ai para generación de imágenes con Gemini 3 Pro Image Preview</p>
            
            <form id="form-falai-config">
                <div class="mb-3">
                    <label for="falai_api_key" class="form-label">
                        <i class="bi bi-key"></i> API Key
                    </label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            class="form-control" 
                            id="falai_api_key" 
                            name="falai_api_key"
                            value="<?php 
                                foreach ($configs as $c) {
                                    if ($c['config_key'] === 'falai_api_key') {
                                        echo htmlspecialchars($c['config_value']);
                                    }
                                }
                            ?>"
                            placeholder="Ingresa tu API Key de fal.ai">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-falai-key">
                            <i class="bi bi-eye" id="eye-icon-falai"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        Obtén tu API key en: 
                        <a href="https://fal.ai/dashboard/keys" target="_blank">
                            fal.ai Dashboard
                        </a>
                    </small>
                </div>
                
                <div class="mb-3">
                    <label for="falai_model" class="form-label">
                        <i class="bi bi-cpu"></i> Modelo
                    </label>
                    <select class="form-select" id="falai_model" name="falai_model">
                        <?php
                        $currentModel = 'fal-ai/gemini-3-pro-image-preview/edit';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_model') {
                                $currentModel = $c['config_value'];
                            }
                        }
                        
                        $models = [
                            'fal-ai/gemini-3-pro-image-preview/edit' => 'Gemini 3 Pro Image Preview (Edit)',
                            'fal-ai/flux-pro/v1.1' => 'FLUX Pro v1.1',
                            'fal-ai/flux/dev' => 'FLUX Dev'
                        ];
                        
                        foreach ($models as $value => $label) {
                            $selected = ($value === $currentModel) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">
                        <strong>Gemini 3 Pro:</strong> Recomendado para transformación de imágenes con alta fidelidad facial.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="falai_image_size" class="form-label">
                        <i class="bi bi-aspect-ratio"></i> Aspect Ratio
                    </label>
                    <select class="form-select" id="falai_image_size" name="falai_image_size">
                        <?php
                        $currentSize = '1024x1024';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_image_size') {
                                $currentSize = $c['config_value'];
                            }
                        }
                        
                        $sizes = [
                            '1:1' => '1:1 (Cuadrado)',
                            '9:16' => '9:16 (Vertical - Historias)',
                            '16:9' => '16:9 (Horizontal - Cine)',
                            '3:2' => '3:2 (Foto Horizontal)',
                            '2:3' => '2:3 (Foto Vertical)',
                            '4:3' => '4:3 (Monitor/tablet)',
                            '3:4' => '3:4 (Tablet vertical)',
                            '5:4' => '5:4',
                            '4:5' => '4:5',
                            '21:9' => '21:9 (Ultra Panorámico)',
                            'auto' => 'Auto (Automático)'
                        ];
                        
                        foreach ($sizes as $value => $label) {
                            // Si la config actual es una resolución antigua (ej. 1024x1024), mostrarla seleccionada pero mapeada
                            $selected = ($value === $currentSize || ($currentSize == '1024x1024' && $value == '1:1')) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="falai_resolution" class="form-label">
                        <i class="bi bi-stars"></i> Resolución
                    </label>
                    <select class="form-select" id="falai_resolution" name="falai_resolution">
                        <?php
                        $currentResolution = '1K';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_resolution') {
                                $currentResolution = $c['config_value'];
                            }
                        }
                        
                        $resolutions = [
                            '1K' => '1K (Estándar - Más rápido y económico)',
                            '2K' => '2K (Alta calidad)',
                            '4K' => '4K (Ultra HD - Más lento y costoso)'
                        ];
                        
                        foreach ($resolutions as $value => $label) {
                            $selected = ($value === $currentResolution) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">
                        Mayor resolución = mejor calidad pero más tiempo y costo por imagen.
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="falai_output_format" class="form-label">
                        <i class="bi bi-file-earmark-image"></i> Formato de Salida
                    </label>
                    <select class="form-select" id="falai_output_format" name="falai_output_format">
                        <?php
                        $currentFormat = 'png';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_output_format') {
                                $currentFormat = $c['config_value'];
                            }
                        }
                        
                        $formats = [
                            'png' => 'PNG (Recomendado - Sin pérdida)',
                            'jpeg' => 'JPEG (Menor tamaño de archivo)',
                            'webp' => 'WebP (Moderno y eficiente)'
                        ];
                        
                        foreach ($formats as $value => $label) {
                            $selected = ($value === $currentFormat) ? 'selected' : '';
                            echo "<option value='$value' $selected>$label</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="falai_num_images" class="form-label">
                        <i class="bi bi-images"></i> Número de Imágenes
                    </label>
                    <select class="form-select" id="falai_num_images" name="falai_num_images">
                        <?php
                        $currentNum = '1';
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_num_images') {
                                $currentNum = $c['config_value'];
                            }
                        }
                        
                        for ($i = 1; $i <= 4; $i++) {
                            $selected = ($i == $currentNum) ? 'selected' : '';
                            $cost = $i > 1 ? " (x{$i} costo)" : '';
                            echo "<option value='$i' $selected>{$i} imagen" . ($i > 1 ? 'es' : '') . "$cost</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">
                        Generar múltiples variaciones (aumenta el costo proporcionalmente).
                    </div>
                </div>
                
                
                <div class="mb-3 form-check">
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="falai_enable_web_search" 
                        name="falai_enable_web_search"
                        <?php
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_enable_web_search' && $c['config_value'] === '1') {
                                echo 'checked';
                            }
                        }
                        ?>>
                    <label class="form-check-label" for="falai_enable_web_search">
                        <i class="bi bi-search"></i> Habilitar Búsqueda Web
                    </label>
                    <div class="form-text">
                        Permite al modelo usar información reciente de internet para mejorar resultados.
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="falai_sync_mode" 
                        name="falai_sync_mode"
                        <?php
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_sync_mode' && $c['config_value'] === '1') {
                                echo 'checked';
                            }
                        }
                        ?>>
                    <label class="form-check-label" for="falai_sync_mode">
                        <i class="bi bi-lightning-charge"></i> Modo Síncrono (Experimental)
                    </label>
                    <div class="form-text">
                        Devuelve la imagen como data URI directamente (no se guarda en historial).
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input 
                        type="checkbox" 
                        class="form-check-input" 
                        id="falai_enabled" 
                        name="falai_enabled"
                        <?php
                        foreach ($configs as $c) {
                            if ($c['config_key'] === 'falai_enabled' && $c['config_value'] === '1') {
                                echo 'checked';
                            }
                        }
                        ?>>
                    <label class="form-check-label" for="falai_enabled">
                        Habilitar integración con fal.ai
                    </label>
                </div>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 
                    <strong>Ventaja de fal.ai:</strong> Soporta múltiples imágenes de entrada (participante + imagen de referencia de la carrera) para mejores resultados.
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Configuración
                    </button>
                    <button type="button" class="btn btn-info" id="btn-test-falai">
                        <i class="bi bi-lightning"></i> Probar Conexión
                    </button>
                </div>
            </form>
        </div>
        
        <!-- API de OpenAI -->
        <div class="card" id="openai-config">
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
        <div class="card">
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
    
    <footer class="text-center py-4 mt-5 text-muted">
        <div class="container">
            <p class="mb-0">Desarrollado por Alberto Calero</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ======================
        // MOSTRAR/OCULTAR SEGÚN PROVEEDOR SELECCIONADO
        // ======================
        
        function toggleProviderConfigs() {
            const provider = document.getElementById('ai_provider').value;
            const openaiConfig = document.getElementById('openai-config');
            const falaiConfig = document.getElementById('falai-config');
            
            if (provider === 'falai') {
                // Mostrar solo fal.ai
                if (falaiConfig) falaiConfig.style.display = 'block';
                if (openaiConfig) openaiConfig.style.display = 'none';
            } else {
                // Mostrar solo OpenAI (default)
                if (openaiConfig) openaiConfig.style.display = 'block';
                if (falaiConfig) falaiConfig.style.display = 'none';
            }
        }
        
        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            toggleProviderConfigs();
        });
        
        // Ejecutar cuando cambie el selector
        document.getElementById('ai_provider').addEventListener('change', toggleProviderConfigs);
        
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
        
        // ======================
        // FAL.AI HANDLERS
        // ======================
        
        // Toggle fal.ai password visibility
        document.getElementById('toggle-falai-key').addEventListener('click', function() {
            const input = document.getElementById('falai_api_key');
            const icon = document.getElementById('eye-icon-falai');
            
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
        
        // Guardar selector de proveedor
        document.getElementById('form-ai-provider').addEventListener('submit', async function(e) {
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
                        text: 'Proveedor de IA actualizado exitosamente',
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
        
        
        // Auto-guardar modo contingencia
        document.getElementById('ai_fallback_mode').addEventListener('change', function() {
            const isChecked = this.checked;
            const modeName = isChecked ? 'ACTIVADO' : 'DESACTIVADO';
            
            // Simular submit del formulario
            document.getElementById('form-ai-provider').dispatchEvent(new Event('submit'));
            
            // Notificación visual rápida
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            Toast.fire({
                icon: isChecked ? 'warning' : 'info',
                title: `Modo Contingencia ${modeName}`
            });
        });
        
        // Guardar configuración de fal.ai
        document.getElementById('form-falai-config').addEventListener('submit', async function(e) {
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
                        text: 'Configuración de fal.ai guardada exitosamente',
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
        
        // Probar conexión fal.ai
        document.getElementById('btn-test-falai').addEventListener('click', async function() {
            const apiKey = document.getElementById('falai_api_key').value;
            
            if (!apiKey) {
                Swal.fire({
                    icon: 'warning',
                    title: 'API Key Requerida',
                    text: 'Por favor ingresa tu API Key de fal.ai primero',
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
                
                const response = await fetch('<?php echo BASE_URL; ?>/api/config/test-falai', {
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
                    text: error.message || 'No se pudo conectar con fal.ai. Verifica tu API Key.',
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
