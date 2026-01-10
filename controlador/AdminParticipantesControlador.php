<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../modelo/ParticipanteModel.php';

class AdminParticipantesControlador {
    
    private $participanteModel;
    
    public function __construct() {
        $this->participanteModel = new ParticipanteModel();
    }
    
    /**
     * Muestra la lista de participantes con DataTables
     */
    public function mostrarLista() {
        requireRole(['admin', 'operator']);
        
        include __DIR__ . '/../vista/admin/participants.php';
    }
    
    /**
     * API: DataTables server-side processing
     */
    public function datatables() {
        header('Content-Type: application/json');
        
        requireRole(['admin', 'operator']);
        
        try {
            // Obtener parámetros de DataTables
            $draw = (int)($_GET['draw'] ?? 1);
            $start = (int)($_GET['start'] ?? 0);
            $length = (int)($_GET['length'] ?? 10);
            
            // Búsqueda
            $searchValue = $_GET['search']['value'] ?? '';
            
            // Ordenamiento
            $orderColumnIndex = (int)($_GET['order'][0]['column'] ?? 0);
            $orderDir = $_GET['order'][0]['dir'] ?? 'desc';
            
            $params = [
                'draw' => $draw,
                'start' => $start,
                'length' => $length,
                'search' => ['value' => $searchValue],
                'order' => [
                    ['column' => $orderColumnIndex, 'dir' => $orderDir]
                ]
            ];
            
            $resultado = $this->participanteModel->obtenerParaDatatables($params);
            
            // Formatear datos para DataTables
            $data = [];
            
            foreach ($resultado['data'] as $row) {
                // Badge de status
                $statusBadges = [
                    'queued' => '<span class="badge bg-warning">En Cola</span>',
                    'processing' => '<span class="badge bg-info">Procesando</span>',
                    'done' => '<span class="badge bg-success">Completado</span>',
                    'error' => '<span class="badge bg-danger">Error</span>'
                ];
                
                $statusBadge = $statusBadges[$row['status']] ?? '<span class="badge bg-secondary">Desconocido</span>';
                
                // Imagen resultado
                $resultImage = '';
                if ($row['status'] === 'done' && !empty($row['result_image_path'])) {
                    $imageUrl = STORAGE_URL . str_replace('/storage', '', $row['result_image_path']);
                    $resultImage = '<img src="' . htmlspecialchars($imageUrl) . '" alt="Resultado" style="max-width: 60px; max-height: 60px; cursor: pointer;" onclick="verImagenModal(\'' . htmlspecialchars($imageUrl) . '\')">';
                }
                
                // QR
                $qrButton = '';
                if (!empty($row['qr_image_path'])) {
                    $qrUrl = STORAGE_URL . str_replace('/storage', '', $row['qr_image_path']);
                    $qrButton = '<button class="btn btn-sm btn-outline-primary" onclick="verQRModal(\'' . htmlspecialchars($qrUrl) . '\', \'' . htmlspecialchars($row['public_code']) . '\')"><i class="bi bi-qr-code"></i></button>';
                }
                
                // Acciones
                $acciones = '';
                
                // Botón ver detalle
                $acciones .= '<button class="btn btn-sm btn-info me-1" onclick="verDetalle(' . $row['id'] . ')" title="Ver Detalle"><i class="bi bi-eye"></i></button>';
                
                // Botón retry (solo si está en error o queued)
                if (in_array($row['status'], ['error', 'queued'])) {
                    $acciones .= '<button class="btn btn-sm btn-warning me-1" onclick="reintentar(' . $row['id'] . ')" title="Reintentar"><i class="bi bi-arrow-clockwise"></i></button>';
                }
                
                // Botón delete (solo admin)
                if (isAdmin()) {
                    $acciones .= '<button class="btn btn-sm btn-danger" onclick="eliminarParticipante(' . $row['id'] . ')" title="Eliminar"><i class="bi bi-trash3"></i></button>';
                }
                
                $data[] = [
                    $row['id'],
                    date('Y-m-d H:i', strtotime($row['created_at'])),
                    htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
                    htmlspecialchars($row['career_name'] ?? 'N/A'),
                    $statusBadge,
                    $resultImage,
                    $qrButton,
                    htmlspecialchars($row['created_by_username'] ?? 'N/A'),
                    $acciones
                ];
            }
            
            echo json_encode([
                'draw' => $resultado['draw'],
                'recordsTotal' => $resultado['recordsTotal'],
                'recordsFiltered' => $resultado['recordsFiltered'],
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Muestra el detalle de un participante
     */
    public function mostrarDetalle() {
        header('Content-Type: application/json');
        
        requireRole(['admin', 'operator']);
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $participante = $this->participanteModel->obtenerPorId($id);
            
            if (!$participante) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'No encontrado']);
                return;
            }
            
            // Formatear URLs completas
            $participante['photo_original_url'] = !empty($participante['photo_original_path']) ? 
                STORAGE_URL . str_replace('/storage', '', $participante['photo_original_path']) : null;
            
            $participante['result_image_url'] = !empty($participante['result_image_path']) ? 
                STORAGE_URL . str_replace('/storage', '', $participante['result_image_path']) : null;
            
            $participante['qr_image_url'] = !empty($participante['qr_image_path']) ? 
                STORAGE_URL . str_replace('/storage', '', $participante['qr_image_path']) : null;
            
            echo json_encode([
                'ok' => true,
                'data' => $participante
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
