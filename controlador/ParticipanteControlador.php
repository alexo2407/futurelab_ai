<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/phpqrcode.php';
require_once __DIR__ . '/../modelo/ParticipanteModel.php';
require_once __DIR__ . '/../modelo/CarreraModel.php';
require_once __DIR__ . '/../modelo/AuditLogModel.php';

class ParticipanteControlador {
    
    private $participanteModel;
    private $carreraModel;
    private $auditModel;
    
    public function __construct() {
        $this->participanteModel = new ParticipanteModel();
        $this->carreraModel = new CarreraModel();
        $this->auditModel = new AuditLogModel();
    }
    
    /**
     * Muestra el formulario de generación de participantes
     */
    public function mostrarFormulario() {
        requireRole(['admin', 'operator']);
        
        $carreras = $this->carreraModel->obtenerActivas();
        
        include __DIR__ . '/../vista/admin/generate.php';
    }
    
    /**
     * API: Crea un nuevo participante
     */
    public function crear() {
        header('Content-Type: application/json');
        
        try {
            // Verificar autenticación primero
            requireRole(['admin', 'operator']);
        } catch (Exception $e) {
            // Error de autenticación/autorización
            http_response_code(403);
            echo json_encode([
                'ok' => false,
                'error' => 'No tienes permisos para esta acción',
                'debug' => $e->getMessage()
            ]);
            error_log('Error de autorización en crear participante: ' . $e->getMessage());
            return;
        }
        
        try {
            // Validar método
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            // Log para debugging
            error_log('=== CREAR PARTICIPANTE ===');
            error_log('POST: ' . print_r($_POST, true));
            error_log('FILES: ' . print_r($_FILES, true));
            
            // Validar campos
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $careerId = (int)($_POST['career_id'] ?? 0);
            
            if (empty($firstName)) {
                throw new Exception('El nombre es requerido');
            }
            
            if (empty($lastName)) {
                throw new Exception('El apellido es requerido');
            }
            
            if ($careerId <= 0) {
                throw new Exception('Debe seleccionar una carrera');
            }
            
            // Validar imagen
            if (!isset($_FILES['photo'])) {
                throw new Exception('No se recibió el archivo de foto');
            }
            
            if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
                $uploadErrors = [
                    UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize',
                    UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE',
                    UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
                    UPLOAD_ERR_EXTENSION => 'Extensión PHP detuvo la subida'
                ];
                $errorMsg = $uploadErrors[$_FILES['photo']['error']] ?? 'Error desconocido al subir archivo';
                throw new Exception($errorMsg);
            }
            
            $file = $_FILES['photo'];
            
            // Validar tipo mime
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
                throw new Exception('Tipo de imagen no permitido. Use JPG o PNG. Tipo detectado: ' . $mimeType);
            }
            
            // Validar tamaño
            if ($file['size'] > MAX_UPLOAD_SIZE) {
                throw new Exception('La imagen es demasiado grande. Máximo 5MB');
            }
            
            // Crear directorios si no existen
            if (!file_exists(UPLOADS_PATH)) {
                mkdir(UPLOADS_PATH, 0777, true);
            }
            
            if (!file_exists(QR_PATH)) {
                mkdir(QR_PATH, 0777, true);
            }
            
            // Generar nombre de archivo único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $filename = uniqid('participant_') . '_' . time() . '.' . $extension;
            $uploadPath = UPLOADS_PATH . '/' . $filename;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Error al guardar la imagen en el servidor');
            }
            
            // Calcular SHA256
            $sha256 = hash_file('sha256', $uploadPath);
            
            // Ruta relativa para BD
            $relativePath = '/storage/uploads/' . $filename;
            
            // Generar código QR payload
            $publicCode = $this->participanteModel->generarPublicCode();
            $qrPayload = BASE_URL . '/p/' . $publicCode;
            
            // Generar QR code
            $qrFilename = $publicCode . '.png';
            $qrPath = QR_PATH . '/' . $qrFilename;
            $qrRelativePath = '/storage/qr/' . $qrFilename;
            
            $qrGenerated = generarQR($qrPayload, $qrPath, 10);
            
            if (!$qrGenerated) {
                error_log('No se pudo generar QR code, continuando sin QR');
                $qrRelativePath = null;
            }
            
            // Crear participante en BD
            $resultado = $this->participanteModel->crear([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'career_id' => $careerId,
                'photo_original_path' => $relativePath,
                'photo_original_mime' => $mimeType,
                'photo_original_sha256' => $sha256,
                'qr_payload' => $qrPayload,
                'qr_image_path' => $qrRelativePath,
                'created_by_user_id' => currentUser()['id']
            ]);
            
            if (!$resultado) {
                throw new Exception('Error al registrar el participante en la base de datos');
            }
            
            // Log de auditoría
            $this->auditModel->registrar(
                currentUser()['id'],
                'create',
                'participant',
                $resultado['id'],
                ['name' => "$firstName $lastName", 'career_id' => $careerId]
            );
            
            error_log('Participante creado exitosamente: ID=' . $resultado['id']);
            
            // Respuesta exitosa
            echo json_encode([
                'ok' => true,
                'participant_id' => $resultado['id'],
                'public_code' => $resultado['public_code'],
                'qr_url' => STORAGE_URL . '/qr/' . $qrFilename,
                'status_url' => BASE_URL . '/api/participants/status?id=' . $resultado['id']
            ]);
            
        } catch (Exception $e) {
            error_log('Error en crear participante: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ]);
        }
    }
    
    /**
     * API: Obtiene el estado de un participante
     */
    public function obtenerStatus() {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            $publicCode = $_GET['public_code'] ?? '';
            
            if ($id > 0) {
                $participante = $this->participanteModel->obtenerPorId($id);
            } elseif (!empty($publicCode)) {
                $participante = $this->participanteModel->obtenerPorPublicCode($publicCode);
            } else {
                throw new Exception('ID o código público requerido');
            }
            
            if (!$participante) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'Participante no encontrado']);
                return;
            }
            
            echo json_encode([
                'ok' => true,
                'id' => $participante['id'],
                'public_code' => $participante['public_code'],
                'status' => $participante['status'],
                'result_image_url' => $participante['result_image_path'] ? STORAGE_URL . str_replace('/storage', '', $participante['result_image_path']) : null,
                'qr_url' => $participante['qr_image_path'] ? STORAGE_URL . str_replace('/storage', '', $participante['qr_image_path']) : null,
                'error_message' => $participante['error_message'] ?? null
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Reintentar procesamiento de un participante
     */
    public function reintentarProcesamiento() {
        header('Content-Type: application/json');
        
        requireRole(['admin', 'operator']);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $resultado = $this->participanteModel->reintentarProcesamiento($id);
            
            if (!$resultado) {
                throw new Exception('Error al reintentar');
            }
            
            // Log
            $this->auditModel->registrar(
                currentUser()['id'],
                'retry',
                'participant',
                $id,
                []
            );
            
            echo json_encode(['ok' => true, 'message' => 'Reintento programado']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Eliminar participante
     */
    public function eliminar() {
        header('Content-Type: application/json');
        
        requireRole('admin'); // Solo admin puede eliminar
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            // Obtener datos antes de eliminar para los archivos
            $participante = $this->participanteModel->obtenerPorId($id);
            
            if (!$participante) {
                throw new Exception('Participante no encontrado');
            }
            
            $resultado = $this->participanteModel->eliminar($id);
            
            if (!$resultado) {
                throw new Exception('Error al eliminar');
            }
            
            // Eliminar archivos físicos (opcional)
            if (!empty($participante['photo_original_path'])) {
                $filePath = STORAGE_PATH . str_replace('/storage', '', $participante['photo_original_path']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            if (!empty($participante['result_image_path'])) {
                $filePath = STORAGE_PATH . str_replace('/storage', '', $participante['result_image_path']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            if (!empty($participante['qr_image_path'])) {
                $filePath = STORAGE_PATH . str_replace('/storage', '', $participante['qr_image_path']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            // Log
            $this->auditModel->registrar(
                currentUser()['id'],
                'delete',
                'participant',
                $id,
                ['name' => $participante['first_name'] . ' ' . $participante['last_name']]
            );
            
            echo json_encode(['ok' => true, 'message' => 'Participante eliminado']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
}
