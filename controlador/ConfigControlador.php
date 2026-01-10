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
            
            // Campos permitidos de OpenAI
            $configKeys = [
                'openai_api_key',
                'openai_model',
                'openai_image_size',
                'openai_image_quality',
                'openai_input_fidelity'
            ];
            
            foreach ($configKeys as $key) {
                if (isset($_POST[$key])) {
                    $this->configModel->set($key, $_POST[$key], $userId);
                    $updated++;
                }
            }
            
            // Manejar checkbox separately
            $enabled = isset($_POST['openai_enabled']) ? '1' : '0';
            $this->configModel->set('openai_enabled', $enabled, $userId);
            
            // Log de auditoría
            $this->auditModel->registrar(
                $userId,
                'update',
                'config',
                null,
                ['type' => 'openai_config']
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
}
