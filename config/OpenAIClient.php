<?php
/**
 * Cliente para OpenAI DALL-E 3 y GPT-Image-1
 * Genera y edita imágenes usando la API de OpenAI
 */

class OpenAIClient {
    private $apiKey;
    private $model;
    private $imageSize;
    private $imageQuality;
    private $inputFidelity;
    private $apiUrl = 'https://api.openai.com/v1/images/generations';
    
    /**
     * Constructor
     * @param string $apiKey API Key de OpenAI
     * @param string $model Modelo a usar (gpt-image-1, dall-e-3, etc)
     * @param string $imageSize Tamaño de imagen (1024x1024, 1024x1792, etc)
     * @param string $imageQuality Calidad (standard, hd)
     * @param string $inputFidelity Fidelidad de entrada (high, low)
     */
    public function __construct($apiKey, $model = 'dall-e-3', $imageSize = '1024x1024', $imageQuality = 'standard', $inputFidelity = 'high') {
        if (empty($apiKey)) {
            throw new Exception('API Key de OpenAI es requerida');
        }
        
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->imageSize = $imageSize;
        $this->imageQuality = $imageQuality;
        $this->inputFidelity = $inputFidelity;
    }
    
    /**
     * Generar imagen (Soporte nativo para DALL-E 3 y GPT-Image-1 Edits)
     * @param string $prompt Descripción de la imagen a generar
     * @param string|null $imageBase64 Imagen base64 opcional para edición/transformación
     * @param string|null $imageMimeType MIME type de la imagen (image/jpeg, image/png)
     * @param string|null $referenceImageBase64 Segunda imagen de referencia (ignorada en OpenAI, usado por fal.ai)
     * @param string|null $referenceMimeType MIME type de la imagen de referencia
     * @return array Respuesta de la API con imagen en base64
     * @throws Exception Si hay error en la API
     */
    public function generateImage($prompt, $imageBase64 = null, $imageMimeType = 'image/jpeg', $referenceImageBase64 = null, $referenceMimeType = 'image/jpeg') {
        $prompt = trim($prompt);
        if (empty($prompt)) {
            return ['success' => false, 'error' => 'El prompt no puede estar vacío'];
        }

        // Caso GPT-Image-1 (Transformación/Edición nativa usando v1/images/edits)
        // Soporta gpt-image-1, gpt-image-1.5, gpt-image-1-mini
        if ($imageBase64 && strpos($this->model, 'gpt-image') !== false) {
            return $this->generateWithEditEndpoint($prompt, $imageBase64, $imageMimeType);
        }

        // Flujo estándar DALL-E 3 (Generación desde cero)
        return $this->generateStandard($prompt);
    }

    /**
     * Usa el endpoint /v1/images/edits para transformar imágenes
     */
    private function generateWithEditEndpoint($prompt, $imageBase64, $imageMimeType) {
        $endpoint = 'https://api.openai.com/v1/images/edits';
        
        // Crear archivo temporal para la imagen (cURL multipart requiere archivo real)
        $tempFile = tempnam(sys_get_temp_dir(), 'openai_img_');
        file_put_contents($tempFile, base64_decode($imageBase64));
        
        // Determinar extensión correcta para nombre (importante para mime type detection de cURL)
        $ext = ($imageMimeType === 'image/png') ? '.png' : '.jpg';
        $renamedFile = $tempFile . $ext;
        rename($tempFile, $renamedFile);
        
        $cfile = new CURLFile($renamedFile, $imageMimeType, 'image');

        $headers = [
            'Authorization: Bearer ' . $this->apiKey
        ];

        // Mapeo de calidad para GPT-Image-1 (low, medium, high)
        // Nuestra config guarda 'standard' o 'hd'
        $qualityMap = [
            'standard' => 'medium', // Default equilibrado
            'hd' => 'high',         // Alta calidad
            'medium' => 'medium',
            'high' => 'high',
            'low' => 'low'
        ];
        $quality = $qualityMap[$this->imageQuality] ?? 'medium';

        // Mapeo tamaños soportados por gpt-image-1 (API estricta)
        // Soporta: 1024x1024, 1024x1536, 1536x1024, auto
        $size = $this->imageSize;
        if ($size == '1024x1792') $size = '1024x1536'; // Mapear 9:16 -> 2:3
        if ($size == '1792x1024') $size = '1536x1024'; // Mapear 16:9 -> 3:2

        $postFields = [
            'image' => $cfile,
            'prompt' => $prompt,
            'model' => $this->model, // 'gpt-image-1' etc
            'n' => 1,
            'size' => $size,
            'quality' => $quality,
            'input_fidelity' => $this->inputFidelity // Dinámico desde config
            // 'response_format' no soportado para gpt-image-1 (siempre devuelve base64)
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Limpiar archivos temporales
        @unlink($renamedFile);
        if (file_exists($tempFile)) @unlink($tempFile);

        if ($curlError) {
            return ['success' => false, 'error' => 'Curl Error: ' . $curlError];
        }

        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? "HTTP Error: $httpCode";
            // Log para debug si es necesario
            error_log("OpenAI Error Response ($httpCode): " . $response);
            return ['success' => false, 'error' => $errorMsg, 'data' => $result];
        }

        // GPT-Image-1 puede devolver la imagen en 'b64_json' o simplemente 'image'
        $imageData = $result['data'][0]['b64_json'] ?? $result['data'][0]['image'] ?? null;

        if ($imageData) {
            return [
                'success' => true,
                'imageData' => $imageData,
                'revised_prompt' => $prompt, 
                'description' => "Editado con {$this->model}"
            ];
        }

        return ['success' => false, 'error' => 'No image data returned from API'];
    }

    /**
     * Generación estándar con DALL-E 3 (Text-to-Image)
     */
    private function generateStandard($prompt) {
        $validQualities = ['standard', 'hd'];
        $quality = $this->imageQuality;
        
        // Sanitizar calidad para DALL-E 3
        if ($quality === 'medium' || $quality === 'low') $quality = 'standard';
        if ($quality === 'high' || $quality === 'best') $quality = 'hd';
        if (!in_array($quality, $validQualities)) $quality = 'standard';

        $data = [
            'model' => 'dall-e-3',
            'prompt' => substr($prompt, 0, 4000),
            'n' => 1,
            'size' => ($this->imageSize == '1024x1792' || $this->imageSize == '1792x1024') ? $this->imageSize : '1024x1024',
            'quality' => $quality,
            'response_format' => 'b64_json',
            'style' => 'vivid'
        ];
        
        return $this->makeRequest('https://api.openai.com/v1/images/generations', $data);
    }

    private function makeRequest($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Curl Error: ' . $curlError];
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error']['message'] ?? "HTTP Error: $httpCode";
            return ['success' => false, 'error' => $errorMsg, 'data' => $result];
        }

        return ['success' => true, 'data' => $result];
    }
    
    /**
     * Verificar que la API key es válida
     * @return bool True si la API key es válida
     */
    public function testConnection() {
        try {
            // Test simple generación standard
            $this->generateStandard('A simple test image');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
