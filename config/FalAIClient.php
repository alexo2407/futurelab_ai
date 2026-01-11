<?php
/**
 * Cliente para fal.ai - Gemini 3 Pro Image Preview (Edit)
 * Genera y transforma imágenes usando la API de fal.ai con sistema de cola
 */

class FalAIClient {
    private $apiKey;
    private $model;
    private $imageSize;
    private $resolution;
    private $outputFormat;
    private $numImages;
    private $enableWebSearch;
    private $syncMode;
    
    // Endpoints correctos de fal.ai
    private $queueUrl = 'https://queue.fal.run/';
    private $statusUrl = 'https://queue.fal.run/';
    
    /**
     * Constructor
     * @param string $apiKey API Key de fal.ai
     * @param string $model Modelo a usar
     * @param string $imageSize Tamaño de imagen
     * @param string $resolution Resolución (1K, 2K, 4K)
     * @param string $outputFormat Formato (jpeg, png, webp)
     * @param int $numImages Número de imágenes (1-4)
     * @param bool $enableWebSearch Habilitar búsqueda web
     * @param bool $syncMode Modo síncrono
     */
    public function __construct($apiKey, $model = 'fal-ai/gemini-3-pro-image-preview/edit', $imageSize = '1024x1024', $resolution = '1K', $outputFormat = 'png', $numImages = 1, $enableWebSearch = false, $syncMode = false) {
        if (empty($apiKey)) {
            throw new Exception('API Key de fal.ai es requerida');
        }
        
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->imageSize = $imageSize;
        $this->resolution = $resolution;
        $this->outputFormat = $outputFormat;
        $this->numImages = (int)$numImages;
        $this->enableWebSearch = (bool)$enableWebSearch;
        $this->syncMode = (bool)$syncMode;
    }
    
    /**
     * Generar/transformar imagen usando fal.ai
     * @param string $prompt Descripción de la transformación
     * @param string|null $imageBase64 Imagen en base64 para transformar
     * @param string|null $imageMimeType MIME type (image/jpeg, image/png)
     * @param string|null $referenceImageBase64 Segunda imagen de referencia opcional
     * @param string|null $referenceMimeType MIME type de la segunda imagen
     * @return array Respuesta con imagen en base64
     */
    public function generateImage($prompt, $imageBase64 = null, $imageMimeType = 'image/jpeg', $referenceImageBase64 = null, $referenceMimeType = 'image/jpeg') {
        $prompt = trim($prompt);
        if (empty($prompt)) {
            return ['success' => false, 'error' => 'El prompt no puede estar vacío'];
        }
        
        if (empty($imageBase64)) {
            return ['success' => false, 'error' => 'Se requiere al menos una imagen para transformar'];
        }
        
        try {
            // Paso 1: Subir imagen(es) y enviar a cola
            $requestId = $this->submitToQueue($prompt, $imageBase64, $imageMimeType, $referenceImageBase64, $referenceMimeType);
            
            if (!$requestId) {
                return ['success' => false, 'error' => 'No se pudo obtener request_id de la cola'];
            }
            
            // Paso 2: Esperar resultado (polling)
            $resultado = $this->waitForResult($requestId);
            
            return $resultado;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Enviar imágenes a la cola de procesamiento
     */
    private function submitToQueue($prompt, $imageBase64, $imageMimeType, $referenceImageBase64 = null, $referenceMimeType = null) {
        // Subir imagen(es) primero para obtener URLs
        $imageUrls = [];
        
        // Imagen principal del participante
        $mainImageUrl = $this->uploadImage($imageBase64, $imageMimeType);
        if ($mainImageUrl) {
            $imageUrls[] = $mainImageUrl;
        }
        
        // Imagen de referencia opcional (si existe)
        if ($referenceImageBase64) {
            $refImageUrl = $this->uploadImage($referenceImageBase64, $referenceMimeType);
            if ($refImageUrl) {
                $imageUrls[] = $refImageUrl;
            }
        }
        
        if (empty($imageUrls)) {
            throw new Exception('No se pudieron subir las imágenes');
        }
        
        // Mapear tamaño a aspect_ratio de fal.ai
        $aspectRatioMap = [
            '1024x1024' => '1:1',
            '1024x1792' => '9:16',
            '1792x1024' => '16:9',
            '1024x1536' => '2:3',
            '1536x1024' => '3:2'
        ];
        $aspectRatio = $aspectRatioMap[$this->imageSize] ?? 'auto';
        
        // Enviar a cola con parámetros correctos
        $endpoint = $this->queueUrl . $this->model;
        
        $payload = [
            'prompt' => $prompt,
            'image_urls' => $imageUrls,
            'num_images' => $this->numImages,
            'aspect_ratio' => $aspectRatio,
            'output_format' => $this->outputFormat,
            'resolution' => $this->resolution
        ];
        
        // Opcionales
        if ($this->enableWebSearch) {
            $payload['enable_web_search'] = true;
        }
        
        if ($this->syncMode) {
            $payload['sync_mode'] = true;
        }
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Key ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception('Curl Error al enviar a cola: ' . $curlError);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            $errorMsg = $result['error'] ?? $result['detail'] ?? "HTTP Error: $httpCode";
            error_log("fal.ai Error Response: " . $response);
            throw new Exception("Error al enviar a cola: $errorMsg");
        }
        
        // Extraer request_id
        return $result['request_id'] ?? null;
    }
    
    /**
     * Subir imagen a fal.ai storage y obtener URL
     * 
     * NOTA: fal.ai soporta data URIs directamente en el payload.
     * El upload a su storage requiere un endpoint específico que puede variar.
     * Por simplicidad y compatibilidad, usamos data URIs.
     */
    private function uploadImage($imageBase64, $mimeType) {
        // fal.ai soporta data URIs inline directamente
        // Esto es más simple y compatible que intentar usar su storage API
        $dataUri = "data:$mimeType;base64," . $imageBase64;
        
        // Verificar tamaño (fal.ai tiene límite de ~10MB para data URIs)
        $sizeKB = strlen($imageBase64) * 0.75 / 1024; // Aproximado
        
        if ($sizeKB > 10240) { // > 10MB
            error_log("ADVERTENCIA: Imagen muy grande ($sizeKB KB). Considera reducir resolución.");
        }
        
        return $dataUri;
    }
    
    /**
     * Esperar resultado del trabajo en cola
     */
    private function waitForResult($requestId, $maxAttempts = 150, $pollInterval = 2) {
        // 150 intentos * 2 segundos = 300 segundos (5 minutos)
        // Formato correcto según docs de fal.ai: https://queue.fal.run/{modelo}/requests/{request_id}
        $statusUrl = $this->statusUrl . $this->model . '/requests/' . $requestId;
        
        echo "Esperando resultado (máx " . ($maxAttempts * $pollInterval) . "s)...\n";
        echo "Status URL: $statusUrl\n"; // Debug
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            $ch = curl_init($statusUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Key ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("fal.ai status check failed (HTTP $httpCode): $response");
                sleep($pollInterval);
                continue;
            }
            
            $result = json_decode($response, true);
            $status = $result['status'] ?? 'UNKNOWN';
            
            // Mostrar progreso cada 10 intentos (20 segundos)
            if ($i % 10 === 0 && $i > 0) {
                $elapsed = $i * $pollInterval;
                echo "  ... esperando {$elapsed}s (estado: $status)\n";
            }
            
            if ($status === 'COMPLETED') {
                echo "✓ Imagen generada exitosamente\n";
                return $this->processCompletedResult($result);
            } elseif ($status === 'FAILED') {
                $error = $result['error'] ?? 'Error desconocido';
                error_log("fal.ai job failed: " . json_encode($result));
                return ['success' => false, 'error' => "fal.ai falló: $error"];
            }
            
            sleep($pollInterval);
        }
        
        $totalTime = $maxAttempts * $pollInterval;
        return ['success' => false, 'error' => "Timeout esperando resultado (máx {$totalTime}s). El trabajo puede estar en cola todavía."];
    }
    
    /**
     * Procesar resultado completado
     */
    private function processCompletedResult($result) {
        // Schema de fal.ai:
        // {
        //   "output": {
        //     "images": [
        //       {
        //         "url": "https://...",
        //         "content_type": "image/png",
        //         "file_name": "...",
        //         "width": 1024,
        //         "height": 1024
        //       }
        //     ],
        //     "description": "..."
        //   }
        // }
        
        $output = $result['output'] ?? null;
        
        if (!$output || !isset($output['images'])) {
            error_log("fal.ai result missing output.images: " . json_encode($result));
            return ['success' => false, 'error' => 'No se encontró output.images en resultado'];
        }
        
        $images = $output['images'];
        
        if (empty($images) || !isset($images[0]['url'])) {
            error_log("fal.ai images array empty or missing URL: " . json_encode($images));
            return ['success' => false, 'error' => 'No se encontró URL de imagen en resultado'];
        }
        
        $imageUrl = $images[0]['url'];
        
        // Descargar imagen y convertir a base64
        $imageData = @file_get_contents($imageUrl);
        
        if (!$imageData) {
            return ['success' => false, 'error' => 'No se pudo descargar la imagen generada desde: ' . $imageUrl];
        }
        
        $imageBase64 = base64_encode($imageData);
        
        return [
            'success' => true,
            'imageData' => $imageBase64,
            'revised_prompt' => $output['description'] ?? '',
            'description' => "Generado con fal.ai - {$this->model}",
            'metadata' => [
                'width' => $images[0]['width'] ?? null,
                'height' => $images[0]['height'] ?? null,
                'content_type' => $images[0]['content_type'] ?? 'image/png'
            ]
        ];
    }
    
    /**
     * Verificar que la API key es válida
     */
    public function testConnection() {
        try {
            // Test con imagen pequeña dummy
            $dummyImage = base64_encode(file_get_contents('data://text/plain;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
            $result = $this->generateImage('test', $dummyImage, 'image/png');
            return $result['success'];
        } catch (Exception $e) {
            return false;
        }
    }
}
