<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../modelo/CarreraModel.php';
require_once __DIR__ . '/../modelo/AuditLogModel.php';

class CarreraControlador {
    
    private $carreraModel;
    private $auditModel;
    
    public function __construct() {
        $this->carreraModel = new CarreraModel();
        $this->auditModel = new AuditLogModel();
    }
    
    /**
     * Muestra el panel de gestión de carreras
     */
    public function mostrarPanel() {
        requireRole('admin');
        
        $carreras = $this->carreraModel->obtenerTodas();
        $user = currentUser();
        
        include __DIR__ . '/../vista/admin/careers.php';
    }
    
    /**
     * Muestra el formulario de edición de una carrera
     */
    public function mostrarEditar() {
        requireRole('admin');
        
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/admin/careers');
            exit;
        }
        
        $carrera = $this->carreraModel->obtenerPorId($id);
        
        if (!$carrera) {
            header('Location: ' . BASE_URL . '/admin/careers');
            exit;
        }
        
        $user = currentUser();
        
        include __DIR__ . '/../vista/admin/career_edit.php';
    }
    
    /**
     * API: Actualiza una carrera
     */
    public function actualizar() {
        header('Content-Type: application/json');
        
        requireRole('admin');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = (int)($_POST['id'] ?? 0);
            $prompt = trim($_POST['ai_prompt'] ?? '');
            $referenceUrl = trim($_POST['reference_image_url'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $datos = [
                'ai_prompt' => $prompt,
                'reference_image_url' => $referenceUrl,
                'is_active' => $isActive
            ];
            
            // Manejar upload de imagen de referencia
            error_log("DEBUG: Verificando archivo subido...");
            error_log("DEBUG: FILES array: " . print_r($_FILES, true));
            
            if (isset($_FILES['reference_image'])) {
               $file = $_FILES['reference_image'];
                error_log("DEBUG: Archivo detectado - Error code: " . $file['error']);
                
                if ($file['error'] === UPLOAD_ERR_OK) {
                    error_log("DEBUG: Archivo sin errores, procesando...");
                    
                    // Validar tipo
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);
                    
                    error_log("DEBUG: MIME type detectado: $mimeType");
                    
                    if (!in_array($mimeType, $allowedTypes)) {
                        throw new Exception("Tipo de imagen no permitido: $mimeType. Use JPG, PNG o WebP");
                    }
                    
                    // Crear directorio si no existe
                    $referencePath = STORAGE_PATH . '/references';
                    error_log("DEBUG: Ruta de referencias: $referencePath");
                    
                    if (!file_exists($referencePath)) {
                        error_log("DEBUG: Creando directorio...");
                        if (!mkdir($referencePath, 0777, true)) {
                            throw new Exception("No se pudo crear el directorio de referencias");
                        }
                    }
                    
                    // Verificar permisos
                    if (!is_writable($referencePath)) {
                        throw new Exception("El directorio de referencias no tiene permisos de escritura");
                    }
                    
                    // Guardar archivo
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = 'career_' . $id . '_' . time() . '.' . $extension;
                    $uploadPath = $referencePath . '/' . $filename;
                    
                    error_log("DEBUG: Intentando guardar en: $uploadPath");
                    
                    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                        $datos['reference_image_path'] = '/storage/references/' . $filename;
                        error_log("DEBUG: Archivo guardado exitosamente. Ruta BD: " . $datos['reference_image_path']);
                    } else {
                        $uploadError = error_get_last();
                        error_log("DEBUG: Error al mover archivo - " . print_r($uploadError, true));
                        throw new Exception("No se pudo guardar la imagen. Verifica permisos del directorio.");
                    }
                } elseif ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Hay un error pero no es "no file"
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'El archivo excede upload_max_filesize en php.ini',
                        UPLOAD_ERR_FORM_SIZE => 'El archivo excede MAX_FILE_SIZE del formulario',
                        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                        UPLOAD_ERR_NO_TMP_DIR => 'Falta directorio temporal',
                        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir en disco',
                        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga'
                    ];
                    $errorMsg = $errorMessages[$file['error']] ?? 'Error desconocido: ' . $file['error'];
                    error_log("DEBUG: Error de upload - $errorMsg");
                    throw new Exception("Error al subir archivo: $errorMsg");
                }
            }
            
            error_log("DEBUG: Datos a actualizar: " . print_r($datos, true));
            
            $resultado = $this->carreraModel->actualizar($id, $datos);
            
            if (!$resultado) {
                throw new Exception('Error al actualizar la carrera');
            }
            
            // Log
            $this->auditModel->registrar(
                currentUser()['id'],
                'update',
                'career',
                $id,
                ['prompt_updated' => !empty($prompt)]
            );
            
            echo json_encode([
                'ok' => true,
                'message' => 'Carrera actualizada exitosamente'
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
     * API: Elimina la imagen de referencia de una carrera
     */
    public function eliminarImagen() {
        header('Content-Type: application/json');
        
        requireRole('admin');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $carrera = $this->carreraModel->obtenerPorId($id);
            
            if (!$carrera) {
                throw new Exception('Carrera no encontrada');
            }
            
            // Eliminar archivo físico
            if (!empty($carrera['reference_image_path'])) {
                $filePath = STORAGE_PATH . str_replace('/storage', '', $carrera['reference_image_path']);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            // Actualizar BD
            $this->carreraModel->actualizar($id, ['reference_image_path' => null]);
            
            echo json_encode([
                'ok' => true,
                'message' => 'Imagen eliminada'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
