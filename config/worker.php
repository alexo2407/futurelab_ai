<?php
/**
 * Worker para procesar participantes en cola
 * Ejecutar: php -f config/worker.php
 * 
 * Este script procesa participantes con status='queued',
 * llama a OpenAI GPT-Image-1 para generar imágenes y actualiza el estado
 */

// Solo permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde la línea de comandos');
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/OpenAIClient.php';
require_once __DIR__ . '/FalAIClient.php';
require_once __DIR__ . '/../modelo/conexion.php';
require_once __DIR__ . '/../modelo/ParticipanteModel.php';
require_once __DIR__ . '/../modelo/CarreraModel.php';
require_once __DIR__ . '/../modelo/ConfigModel.php';

echo "=== FutureLab AI Worker ===\n";
echo "Iniciando procesamiento de participantes...\n\n";

$participanteModel = new ParticipanteModel();
$carreraModel = new CarreraModel();
$configModel = new ConfigModel();

// Leer configuración desde BD
echo "Leyendo configuración de IA...\n";
$allConfig = $configModel->getAll();

// Variables de configuración
$aiProvider = 'openai'; // default
$openaiApiKey = null;
$openaiModel = 'gpt-image-1';
$openaiImageSize = '1024x1024';
$openaiImageQuality = 'medium';
$openaiInputFidelity = 'high';
$openaiEnabled = false;

$falaiApiKey = null;
$falaiModel = 'fal-ai/gemini-3-pro-image-preview/edit';
$falaiImageSize = '1024x1024';
$falaiResolution = '1K';
$falaiOutputFormat = 'png';
$falaiNumImages = 1;
$falaiEnableWebSearch = false;
$falaiSyncMode = false;
$falaiEnabled = false;

foreach ($allConfig as $config) {
    switch ($config['config_key']) {
        // Proveedor general
        case 'ai_provider':
            $aiProvider = $config['config_value'];
            break;
            
        // OpenAI
        case 'openai_api_key':
            $openaiApiKey = $config['config_value'];
            break;
        case 'openai_model':
            $openaiModel = $config['config_value'];
            break;
        case 'openai_image_size':
            $openaiImageSize = $config['config_value'];
            break;
        case 'openai_image_quality':
            $openaiImageQuality = $config['config_value'];
            break;
        case 'openai_input_fidelity':
            $openaiInputFidelity = $config['config_value'];
            break;
        case 'openai_enabled':
            $openaiEnabled = ($config['config_value'] === '1');
            break;
            
        // fal.ai
        case 'falai_api_key':
            $falaiApiKey = $config['config_value'];
            break;
        case 'falai_model':
            $falaiModel = $config['config_value'];
            break;
        case 'falai_image_size':
            $falaiImageSize = $config['config_value'];
            break;
        case 'falai_resolution':
            $falaiResolution = $config['config_value'];
            break;
        case 'falai_output_format':
            $falaiOutputFormat = $config['config_value'];
            break;
        case 'falai_num_images':
            $falaiNumImages = (int)$config['config_value'];
            break;
        case 'falai_enable_web_search':
            $falaiEnableWebSearch = ($config['config_value'] === '1');
            break;
        case 'falai_sync_mode':
            $falaiSyncMode = ($config['config_value'] === '1');
            break;
        case 'falai_enabled':
            $falaiEnabled = ($config['config_value'] === '1');
            break;
    }
}

// Inicializar cliente según el proveedor seleccionado
$aiClient = null;

if ($aiProvider === 'falai') {
    echo "✓ Proveedor seleccionado: fal.ai\n";
    
    if (empty($falaiApiKey)) {
        die("✗ Error: API Key de fal.ai no configurada.\n" .
            "   Configúrala desde: " . BASE_URL . "/admin/config\n");
    }
    
    if (!$falaiEnabled) {
        die("✗ Error: fal.ai está deshabilitado.\n" .
            "   Habilítalo desde: " . BASE_URL . "/admin/config\n");
    }
    
    echo "✓ Modelo: $falaiModel\n";
    echo "✓ Tamaño: $falaiImageSize\n";
    echo "✓ Resolución: $falaiResolution\n";
    echo "✓ Formato: $falaiOutputFormat\n";
    echo "✓ Núm. imágenes: $falaiNumImages\n";
    echo "✓ Búsqueda web: " . ($falaiEnableWebSearch ? 'Sí' : 'No') . "\n";
    echo "✓ Modo síncrono: " . ($falaiSyncMode ? 'Sí' : 'No') . "\n";
    
    try {
        $aiClient = new FalAIClient(
            $falaiApiKey, 
            $falaiModel, 
            $falaiImageSize, 
            $falaiResolution, 
            $falaiOutputFormat, 
            $falaiNumImages, 
            $falaiEnableWebSearch, 
            $falaiSyncMode
        );
        echo "✓ Cliente fal.ai inicializado\n";
    } catch (Exception $e) {
        die("✗ Error inicializando fal.ai: " . $e->getMessage() . "\n");
    }
    
} else {
    // Default: OpenAI
    echo "✓ Proveedor seleccionado: OpenAI\n";
    
    if (empty($openaiApiKey)) {
        die("✗ Error: API Key de OpenAI no configurada.\n" .
            "   Configúrala desde: " . BASE_URL . "/admin/config\n");
    }
    
    if (!$openaiEnabled) {
        die("✗ Error: OpenAI está deshabilitado.\n" .
            "   Habilítalo desde: " . BASE_URL . "/admin/config\n");
    }
    
    echo "✓ Modelo: $openaiModel\n";
    echo "✓ Tamaño: $openaiImageSize\n";
    echo "✓ Calidad: $openaiImageQuality\n";
    echo "✓ Fidelidad: $openaiInputFidelity\n";
    
    try {
        $aiClient = new OpenAIClient($openaiApiKey, $openaiModel, $openaiImageSize, $openaiImageQuality, $openaiInputFidelity);
        echo "✓ Cliente OpenAI inicializado\n";
    } catch (Exception $e) {
        die("✗ Error inicializando OpenAI: " . $e->getMessage() . "\n");
    }
}

// Crear directorio de resultados si no existe
if (!file_exists(RESULTS_PATH)) {
    mkdir(RESULTS_PATH, 0777, true);
    echo "✓ Directorio de resultados creado\n";
}

// Procesar en loop infinito o una sola iteración
$continuar = true;
$iteracion = 0;

while ($continuar) {
    $iteracion++;
    echo "\n--- Iteración #$iteracion ---\n";
    
    // Obtener participantes en cola
    $participantes = $participanteModel->obtenerEnCola(5);
    
    if (empty($participantes)) {
        // No hay tareas, esperar y reintentar
        echo "."; // Indicador de "vivo"
        sleep(5); // Esperar 5 segundos
        continue;
    }
    
    echo "Procesando " . count($participantes) . " participante(s)...\n\n";
    
    foreach ($participantes as $participante) {
        $id = $participante['id'];
        $nombre = $participante['first_name'] . ' ' . $participante['last_name'];
        
        echo "[$id] $nombre - Iniciando procesamiento...\n";
        
        // Marcar como en procesamiento
        $participanteModel->marcarComoProcesando($id);
        
        try {
            // Leer imagen original del participante
            $photoPath = STORAGE_PATH . str_replace('/storage', '', $participante['photo_original_path']);
            
            if (!file_exists($photoPath)) {
                throw new Exception("Archivo de foto no encontrado: $photoPath");
            }
            
            // Convertir imagen a base64
            $imageData = file_get_contents($photoPath);
            $imageBase64 = base64_encode($imageData);
            
            // Obtener información de la carrera
            $carrera = $carreraModel->obtenerPorId($participante['career_id']);
            $carreraNombre = $carrera ? $carrera['name'] : 'estudiante';
            
            // Preparar imagen de referencia si existe
            $referenceImageBase64 = null;
            $referenceImageMimeType = null;
            
            if ($carrera) {
                // Intentar cargar imagen de referencia (local o URL)
                if (!empty($carrera['reference_image_path'])) {
                    // Imagen local
                    $referencePath = STORAGE_PATH . str_replace('/storage', '', $carrera['reference_image_path']);
                    if (file_exists($referencePath)) {
                        $referenceData = file_get_contents($referencePath);
                        $referenceImageBase64 = base64_encode($referenceData);
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $referenceImageMimeType = finfo_file($finfo, $referencePath);
                        finfo_close($finfo);
                        echo "[$id] ✓ Usando imagen de referencia local\n";
                    }
                } elseif (!empty($carrera['reference_image_url'])) {
                    // Imagen desde URL
                    $imageUrl = $carrera['reference_image_url'];
                    $referenceData = @file_get_contents($imageUrl);
                    if ($referenceData) {
                        $referenceImageBase64 = base64_encode($referenceData);
                        // Detectar MIME type de la URL
                        $ext = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));
                        $mimeMap = [
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                            'webp' => 'image/webp'
                        ];
                        $referenceImageMimeType = $mimeMap[$ext] ?? 'image/jpeg';
                        echo "[$id] ✓ Usando imagen de referencia desde URL\n";
                    }
                }
            }
            
            // === MODO CONTINGENCIA (SIN IA) ===
            // Verificar configuración en tiempo real
            $valFallback = $configModel->get('ai_fallback_mode');
            
            // Debug menos intrusivo (solo si cambia)
            // echo "[$id] DEBUG: ai_fallback_mode = " . var_export($valFallback, true) . "\n";
            
            $fallbackMode = ($valFallback == '1');
            
            if ($fallbackMode) {
                echo "[$id] ⚠ MODO CONTINGENCIA: Generando imagen 'Story Format' sin IA...\n";
                
                // Determinar formato objetivo (Default: Story 9:16)
                $targetW = 1080;
                $targetH = 1920;
                
                // Intentar respetar la configuración activa si es posible
                if ($aiProvider === 'falai') {
                    // FalAI format parsing (e.g., "9:16", "1:1")
                    if ($falaiImageSize === '16:9') { $targetW = 1920; $targetH = 1080; }
                    elseif ($falaiImageSize === '1:1') { $targetW = 1080; $targetH = 1080; }
                    // Default to 9:16 for '9:16' or others
                } else {
                    // OpenAI format parsing (e.g., "1024x1792")
                    if (strpos($openaiImageSize, 'x') !== false) {
                        list($w, $h) = explode('x', $openaiImageSize);
                        $targetW = (int)$w;
                        $targetH = (int)$h;
                    }
                }

                $filename = 'fallback_' . $id . '_' . time() . '.jpg';
                $resultPath = RESULTS_PATH . '/' . $filename;
                
                // === PROCESAMIENTO DE IMAGEN CON GD ===
                try {
                    // 1. Cargar original
                    $srcImg = @imagecreatefromstring(file_get_contents($photoPath));
                    if (!$srcImg) throw new Exception("No se pudo cargar la imagen original");
                    
                    $origW = imagesx($srcImg);
                    $origH = imagesy($srcImg);
                    
                    // 2. Crear canvas destino negro
                    $destImg = imagecreatetruecolor($targetW, $targetH);
                    $black = imagecolorallocate($destImg, 10, 14, 31); // Dark Navy Background
                    imagefill($destImg, 0, 0, $black);
                    
                    // 3. Crear fondo borroso (Efecto Instagram)
                    // Escalar original para llenar el fondo (cover)
                    $ratioDest = $targetW / $targetH;
                    $ratioOrig = $origW / $origH;
                    
                    $bgW = $targetW;
                    $bgH = $targetH;
                    $bgX = 0;
                    $bgY = 0;
                    
                    if ($ratioOrig > $ratioDest) {
                        // Original mas ancho, ajustar a altura
                        $bgH = $targetH;
                        $bgW = $targetH * $ratioOrig;
                        $bgX = ($targetW - $bgW) / 2;
                    } else {
                        // Original mas alto, ajustar a ancho
                        $bgW = $targetW;
                        $bgH = $targetW / $ratioOrig;
                        $bgY = ($targetH - $bgH) / 2;
                    }
                    
                    // Copiar y redimensionar fondo
                    imagecopyresampled($destImg, $srcImg, (int)$bgX, (int)$bgY, 0, 0, (int)$bgW, (int)$bgH, $origW, $origH);
                    
                    // Aplicar blur fuerte al fondo
                    for ($i = 0; $i < 30; $i++) {
                        imagefilter($destImg, IMG_FILTER_GAUSSIAN_BLUR);
                    }
                    // Oscurecer fondo
                    imagefilter($destImg, IMG_FILTER_BRIGHTNESS, -40);

                    // 4. Colocar imagen principal centrada (Contain)
                    // Calcular dimensiones para "fit" dentro del canvas con margenes
                    $margin = 80; // Margen en pixeles
                    $availW = $targetW - ($margin * 2);
                    $availH = $targetH - ($margin * 2);
                    
                    $fgW = $availW;
                    $fgH = $availW / $ratioOrig;
                    
                    if ($fgH > $availH) {
                        $fgH = $availH;
                        $fgW = $availH * $ratioOrig;
                    }
                    
                    $fgX = ($targetW - $fgW) / 2;
                    $fgY = ($targetH - $fgH) / 2;
                    
                    // Sombra para la imagen principal (simulada con rectangulo negro semi-transparente atras)
                    $shadowColor = imagecolorallocatealpha($destImg, 0, 0, 0, 60);
                    imagefilledrectangle($destImg, $fgX + 10, $fgY + 10, $fgX + $fgW + 10, $fgY + $fgH + 10, $shadowColor);
                    
                    // Copiar imagen principal
                    imagecopyresampled($destImg, $srcImg, (int)$fgX, (int)$fgY, 0, 0, (int)$fgW, (int)$fgH, $origW, $origH);
                    
                    // 5. Guardar resultado
                    imagejpeg($destImg, $resultPath, 90);
                    
                    // Liberar memoria
                    imagedestroy($srcImg);
                    imagedestroy($destImg);
                    
                    $resultadoPath = '/storage/results/' . $filename;
                    echo "[$id] ✓ Imagen Fallback creada ($targetW x $targetH): $resultadoPath\n";
                    
                    $participanteModel->marcarComoCompletado($id, $resultadoPath);
                    echo "[$id] ✓ Participante completado (Modo Contingencia)\n";
                    
                } catch (Exception $gdError) {
                    echo "[$id] ⚠ Error generando fallback GD: " . $gdError->getMessage() . ". Usando copia simple.\n";
                    // Fallback del fallback: copia simple
                   if (copy($photoPath, $resultPath)) {
                        $resultadoPath = '/storage/results/' . $filename;
                        $participanteModel->marcarComoCompletado($id, $resultadoPath);
                    }
                }
                
                // Saltar al siguiente participante
                continue;
            }
            // ==================================
            
            // Construir prompt personalizado
            $prompt = '';
            
            // Usar prompt personalizado de la carrera si existe
            if ($carrera && !empty($carrera['ai_prompt'])) {
                $prompt = $carrera['ai_prompt'];
                
                // Reemplazar variables en el prompt
                $prompt = str_replace('{nombre}', $nombre, $prompt);
                $prompt = str_replace('{NOMBRE}', $nombre, $prompt);
                $prompt = str_replace('{carrera}', $carreraNombre, $prompt);
                $prompt = str_replace('{CARRERA}', $carreraNombre, $prompt);
                
                echo "[$id] ✓ Usando prompt personalizado de la carrera\n";
            } else {
                // Prompt por defecto solo si no hay uno en la BD
                $prompt = "Toma la primera imagen como referencia. Reemplaza al sujeto de esa imagen con la persona de la segunda foto ($nombre). " .
                          "Mantén el mismo estilo, ambiente y composición de la imagen de referencia.";
                echo "[$id] ⚠ Usando prompt por defecto (configura un prompt personalizado en la carrera)\n";
            }
            
            
            echo "[$id] Generando imagen con IA ($aiProvider)...\n";
            
            // Generar imagen con el proveedor configurado
            $resultado = $aiClient->generateImage(
                $prompt, 
                $imageBase64, 
                $participante['photo_original_mime'],
                $referenceImageBase64,  // Imagen de referencia (opcional)
                $referenceImageMimeType // MIME type de referencia
            );
            
            if ($resultado['success']) {
                echo "[$id] ✓ Imagen generada por GPT-Image-1!\n";
                
                // Guardar imagen generada
                $extension = 'png'; // GPT-Image-1 devuelve PNG
                $filename = 'result_' . $id . '_' . time() . '.' . $extension;
                $resultPath = RESULTS_PATH . '/' . $filename;
                
                if (file_put_contents($resultPath, base64_decode($resultado['imageData']))) {
                    $resultadoPath = '/storage/results/' . $filename;
                    echo "[$id] ✓ Imagen guardada: $resultadoPath\n";
                } else {
                    echo "[$id] ⚠ No se pudo guardar imagen, usando original\n";
                    $resultadoPath = $participante['photo_original_path'];
                }
            } else {
                $errorMsg = $resultado['error'] ?? 'Error desconocido';
                throw new Exception("Fallo en OpenAI: " . $errorMsg);
            }
            
            // Actualizar participante como completado
            // Nota: marcarComoCompletado($id, $resultPath, $width, $height, $sha256)
            $actualizado = $participanteModel->marcarComoCompletado($id, $resultadoPath);
            
            if ($actualizado) {
                echo "[$id] ✓ Completado exitosamente\n";
            } else {
                throw new Exception("Error al actualizar en base de datos");
            }
            
        } catch (Exception $e) {
            echo "[$id] ✗ Error: " . $e->getMessage() . "\n";
            
            // Marcar como error
            $participanteModel->marcarComoError($id, $e->getMessage());
        }
        
        // Pequeña pausa entre procesamiento
        sleep(1);
    }
    
    echo "\nIteración #$iteracion procesada\n";
    
    // Pequeño descanso después de procesar un lote
    sleep(2);
}

echo "\n=== Worker finalizado ===\n";
