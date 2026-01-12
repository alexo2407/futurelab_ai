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
            // Verificar configuración en tiempo real por si se activó durante la ejecución
            $fallbackMode = ($configModel->get('ai_fallback_mode') === '1');
            
            if ($fallbackMode) {
                echo "[$id] ⚠ MODO CONTINGENCIA ACTIVO: Omitiendo IA y usando foto original.\n";
                
                // Copiar original a resultados para mantener consistencia
                $ext = pathinfo($participante['photo_original_path'], PATHINFO_EXTENSION);
                if (empty($ext)) $ext = 'jpg'; // Fallback ext
                
                $filename = 'fallback_' . $id . '_' . time() . '.' . $ext;
                $resultPath = RESULTS_PATH . '/' . $filename;
                
                if (copy($photoPath, $resultPath)) {
                    $resultadoPath = '/storage/results/' . $filename;
                    echo "[$id] ✓ Imagen original copiada a: $resultadoPath\n";
                    
                    // Marcar como completado
                    $participanteModel->marcarComoCompletado($id, $resultadoPath);
                    echo "[$id] ✓ Participante completado (Modo Contingencia)\n";
                } else {
                    throw new Exception("Error al copiar imagen en Modo Contingencia");
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
