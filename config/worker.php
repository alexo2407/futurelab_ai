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
        
        // === UNIFIED CONTINGENCY LOGIC ===
        $lockFile = __DIR__ . '/fallback.lock';
        $useLocalGeneration = file_exists($lockFile);
        $fallbackReason = $useLocalGeneration ? "MANUAL (Modo Contingencia)" : "";

        // Si no está en modo manual, intentamos usar la API
        if (!$useLocalGeneration) {
            try {
                // 1. Validaciones previas
                $photoPath = STORAGE_PATH . str_replace('/storage', '', $participante['photo_original_path']);
                if (!file_exists($photoPath)) throw new Exception("Archivo no encontrado: $photoPath");

                // 2. Preparar datos para API
                $imageData = file_get_contents($photoPath);
                $imageBase64 = base64_encode($imageData);
                
                // Info carrera y prompt
                $carrera = $carreraModel->obtenerPorId($participante['career_id']);
                $carreraNombre = $carrera ? $carrera['name'] : 'estudiante';
                
                // Prompt setup...
                $prompt = '';
                if ($carrera && !empty($carrera['ai_prompt'])) {
                    $prompt = $carrera['ai_prompt'];
                    $prompt = str_replace('{nombre}', $nombre, $prompt);
                    $prompt = str_replace('{NOMBRE}', $nombre, $prompt);
                    $prompt = str_replace('{carrera}', $carreraNombre, $prompt);
                    $prompt = str_replace('{CARRERA}', $carreraNombre, $prompt);
                    echo "[$id] ✓ Usando prompt personalizado\n";
                } else {
                    $prompt = "Futurist portrait of $nombre"; 
                    echo "[$id] ⚠ Usando prompt por defecto\n";
                }

                // Imagen de referencia
                $referenceImageBase64 = null;
                $referenceImageMimeType = null;
                // (Logic de referencia simplificada para brevedad, asumiendo carga correcta si existe)
                 if ($carrera) {
                    if (!empty($carrera['reference_image_path'])) {
                        $refPath = STORAGE_PATH . str_replace('/storage', '', $carrera['reference_image_path']);
                        if (file_exists($refPath)) {
                            $referenceImageBase64 = base64_encode(file_get_contents($refPath));
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $referenceImageMimeType = finfo_file($finfo, $refPath);
                            finfo_close($finfo);
                        }
                    } elseif (!empty($carrera['reference_image_url'])) {
                         $refData = @file_get_contents($carrera['reference_image_url']);
                         if ($refData) {
                             $referenceImageBase64 = base64_encode($refData);
                             $referenceImageMimeType = 'image/jpeg'; // Default assumption
                         }
                    }
                }

                echo "[$id] Generando imagen con IA ($aiProvider)...\n";
                
                // 3. Llamada API
                $resultado = $aiClient->generateImage(
                    $prompt, 
                    $imageBase64, 
                    $participante['photo_original_mime'],
                    $referenceImageBase64, 
                    $referenceImageMimeType
                );
                
                if ($resultado['success']) {
                    $providerName = ($aiProvider === 'falai') ? 'FalAI' : 'OpenAI';
                    echo "[$id] ✓ Imagen generada por $providerName!\n";
                    
                    $extension = 'png'; 
                    $filename = 'result_' . $id . '_' . time() . '.' . $extension;
                    $resultPath = RESULTS_PATH . '/' . $filename;
                    
                    file_put_contents($resultPath, base64_decode($resultado['imageData']));
                    $resultadoPath = '/storage/results/' . $filename;
                    
                    $participanteModel->marcarComoCompletado($id, $resultadoPath);
                    echo "[$id] ✓ Completado exitosamente\n";
                } else {
                    throw new Exception("API Error: " . ($resultado['error'] ?? 'Unknown'));
                }

            } catch (Exception $e) {
                // 4. DETECCION DE ERROR PARA AUTO-FALLBACK
                $errorMsg = $e->getMessage();
                $isRescueNeeded = (
                    stripos($errorMsg, 'insufficient_quota') !== false || 
                    stripos($errorMsg, 'payment_required') !== false ||
                    stripos($errorMsg, 'balance') !== false ||
                    strpos($errorMsg, '402') !== false ||
                    strpos($errorMsg, '401') !== false ||
                    stripos($errorMsg, 'unauthorized') !== false ||
                    stripos($errorMsg, 'invalid_api_key') !== false
                );

                if ($isRescueNeeded) {
                    $useLocalGeneration = true;
                    $fallbackReason = "AUTO-RESCUE (Fallo API: $errorMsg)";
                    echo "[$id] ⚠ $fallbackReason\n";
                } else {
                    // Error fatal no recuperable
                    echo "[$id] ✗ Error: $errorMsg\n";
                    $participanteModel->marcarComoError($id, $errorMsg);
                }
            }
        }

        // === EJECUCION DE MODO CONTINGENCIA (Si aplica) ===
        if ($useLocalGeneration) {
            echo "[$id] ⚡ EJECUTANDO MODO CONTINGENCIA ($fallbackReason)...\n";
            try {
                $targetW = 1080; $targetH = 1920;
                $filename = 'fallback_' . $id . '_' . time() . '.jpg';
                $resultPath = RESULTS_PATH . '/' . $filename;
                $photoPath = STORAGE_PATH . str_replace('/storage', '', $participante['photo_original_path']);

                // Generación GD (Cover Clean)
                $srcImg = @imagecreatefromstring(file_get_contents($photoPath));
                if (!$srcImg) throw new Exception("No source image");
                
                $origW = imagesx($srcImg); $origH = imagesy($srcImg);
                $destImg = imagecreatetruecolor($targetW, $targetH);
                
                $ratioDest = $targetW / $targetH;
                $ratioOrig = $origW / $origH;
                
                $srcX = 0; $srcY = 0; $srcW = $origW; $srcH = $origH;

                if ($ratioOrig > $ratioDest) {
                    $srcW = $origH * $ratioDest;
                    $srcX = ($origW - $srcW) / 2;
                } else {
                    $srcH = $origW / $ratioDest;
                    $srcY = ($origH - $srcH) / 2;
                }

                imagecopyresampled($destImg, $srcImg, 0, 0, (int)$srcX, (int)$srcY, $targetW, $targetH, (int)$srcW, (int)$srcH);
                imagejpeg($destImg, $resultPath, 95);
                
                imagedestroy($srcImg); imagedestroy($destImg);
                
                $resultadoPath = '/storage/results/' . $filename;
                $participanteModel->marcarComoCompletado($id, $resultadoPath);
                echo "[$id] ✓ CONTINGENCIA EXITOSA: Imagen local generada.\n";
                
            } catch (Exception $localErr) {
                echo "[$id] ✗ FATAL: Contingencia falló: " . $localErr->getMessage() . "\n";
                $participanteModel->marcarComoError($id, "Total Failure: " . $localErr->getMessage());
            }
        }
        
        // Pequeña pausa entre procesamiento
        sleep(1);
    }
    
    echo "\nIteración #$iteracion procesada\n";
    
    // Pequeño descanso después de procesar un lote
    sleep(2);
}

echo "\n=== Worker finalizado ===\n";
