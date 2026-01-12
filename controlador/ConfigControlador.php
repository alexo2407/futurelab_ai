<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../modelo/ConfigModel.php';
require_once __DIR__ . '/../modelo/AuditLogModel.php';

class ConfigControlador {
    
    private $configModel;
    private $auditModel;
    
    public function __construct() {
        $this->configModel = new ConfigModel();
        $this->auditModel = new AuditLogModel();
    }
    
    /**
     * Muestra el panel de configuración
     */
    public function mostrarPanel() {
        requireRole('admin'); // Solo admin puede ver configuración
        
        $configs = $this->configModel->getAll();
        $user = currentUser();
        
        include __DIR__ . '/../vista/admin/config.php';
    }
    
    /**
     * API: Guarda la configuración
     */
    public function guardar() {
        header('Content-Type: application/json');
        
        requireRole('admin');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $userId = currentUser()['id'];
            $updated = 0;
            
            // Selector de proveedor de IA
            if (isset($_POST['ai_provider'])) {
                $this->configModel->set('ai_provider', $_POST['ai_provider'], $userId);
                
                // === CONTINGENCY MODE (UNIFIED) ===
                $fallback = isset($_POST['ai_fallback_mode']) ? '1' : '0';
                $this->configModel->set('ai_fallback_mode', $fallback, $userId);
                
                $lockFile = __DIR__ . '/../config/fallback.lock';
                if ($fallback === '1') touch($lockFile);
                else if (file_exists($lockFile)) unlink($lockFile);
                
                $updated++;

                $updated++;
            }
            
            // Campos de OpenAI
            $openaiKeys = [
                'openai_api_key',
                'openai_model',
                'openai_image_size',
                'openai_image_quality',
                'openai_input_fidelity'
            ];
            
            foreach ($openaiKeys as $key) {
                if (isset($_POST[$key])) {
                    $this->configModel->set($key, $_POST[$key], $userId);
                    $updated++;
                }
            }
            
            // Checkbox OpenAI
            if (isset($_POST['openai_enabled'])) {
                $enabled = $_POST['openai_enabled'] ? '1' : '0';
                $this->configModel->set('openai_enabled', $enabled, $userId);
            }
            
            // Campos de fal.ai
            $falaiKeys = [
                'falai_api_key',
                'falai_model',
                'falai_image_size',
                'falai_resolution',
                'falai_output_format',
                'falai_num_images'
            ];
            
            foreach ($falaiKeys as $key) {
                if (isset($_POST[$key])) {
                    $this->configModel->set($key, $_POST[$key], $userId);
                    $updated++;
                }
            }
            
            // Checkboxes de fal.ai
            $falaiCheckboxes = ['falai_enabled', 'falai_enable_web_search', 'falai_sync_mode'];
            foreach ($falaiCheckboxes as $key) {
                if (isset($_POST[$key])) {
                    $enabled = $_POST[$key] ? '1' : '0';
                    $this->configModel->set($key, $enabled, $userId);
                }
            }
            
            // Log de auditoría
            $this->auditModel->registrar(
                $userId,
                'update',
                'config',
                null,
                ['type' => 'ai_config', 'updated_count' => $updated]
            );
            
            echo json_encode([
                'ok' => true,
                'message' => 'Configuración guardada exitosamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Prueba la conexión con OpenAI
     */
    public function testOpenAI() {
        header('Content-Type: application/json');
        
        requireRole('admin');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $apiKey = $_POST['api_key'] ?? '';
            
            if (empty($apiKey)) {
                // Intentar obtener de BD si no se envía
                $apiKey = $this->configModel->get('openai_api_key');
            }
            
            if (empty($apiKey)) {
                throw new Exception('API Key es requerida');
            }
            
            // Probar conexión con OpenAI
            $ch = curl_init('https://api.openai.com/v1/models');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new Exception('Error de conexión: ' . $curlError);
            }
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                $modelCount = count($data['data'] ?? []);
                
                echo json_encode([
                    'ok' => true,
                    'message' => "Conexión exitosa! Se encontraron $modelCount modelos disponibles."
                ]);
            } else {
                $errorData = json_decode($response, true);
                $errorMsg = $errorData['error']['message'] ?? 'API Key inválida o sin permisos';
                throw new Exception($errorMsg);
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Prueba la conexión con fal.ai
     */
    public function testFalAI() {
        header('Content-Type: application/json');
        
        requireRole('admin');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $apiKey = $_POST['api_key'] ?? '';
            
            if (empty($apiKey)) {
                // Intentar obtener de BD si no se envía
                $apiKey = $this->configModel->get('falai_api_key');
            }
            
            if (empty($apiKey)) {
                throw new Exception('API Key es requerida');
            }
            
            // Probar conexión con fal.ai (Verificando historial de uso)
            // Este endpoint requiere autenticación válida
            $ch = curl_init('https://api.fal.ai/v1/usage');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Key ' . $apiKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                throw new Exception('Error de conexión: ' . $curlError);
            }
            
            // 200 OK significa que la Key es válida y se pudo leer el uso
            if ($httpCode === 200) {
                echo json_encode([
                    'ok' => true,
                    'message' => '¡Conexión verificada! API Key válida.'
                ]);
            } else if ($httpCode === 401 || $httpCode === 403) {
                throw new Exception('API Key inválida o sin permisos');
            } else {
                throw new Exception("Error al verificar (HTTP $httpCode): " . $response);
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
