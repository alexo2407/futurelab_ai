<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../modelo/ParticipanteModel.php';

class WallControlador {
    
    private $participanteModel;
    
    public function __construct() {
        $this->participanteModel = new ParticipanteModel();
    }
    
    /**
     * Muestra el muro público (no requiere login)
     */
    public function mostrarMuro() {
        // El muro es público, no requiere autenticación
        include __DIR__ . '/../vista/wall.php';
    }
    
    /**
     * API: Obtiene los últimos participantes completados
     */
    public function obtenerUltimos() {
        header('Content-Type: application/json');
        
        try {
            $sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            
            // Limitar el límite máximo
            if ($limit > 50) {
                $limit = 50;
            }
            
            $participantes = $this->participanteModel->obtenerUltimosCompletados($limit, $sinceId);
            
            // Formatear datos para el cliente
            $items = [];
            $lastId = $sinceId ?? 0;
            
            foreach ($participantes as $p) {
                $items[] = [
                    'id' => (int)$p['id'],
                    'name' => $p['first_name'] . ' ' . $p['last_name'],
                    'career' => $p['career_name'],
                    'result_image_url' => STORAGE_URL . str_replace('/storage', '', $p['result_image_path']),
                    'created_at' => $p['created_at']
                ];
                
                if ($p['id'] > $lastId) {
                    $lastId = $p['id'];
                }
            }
            
            echo json_encode([
                'ok' => true,
                'last_id' => $lastId,
                'items' => $items,
                'count' => count($items)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
