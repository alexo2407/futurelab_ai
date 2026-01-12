<?php

require_once __DIR__ . '/conexion.php';

class ParticipanteModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Genera un código público único de 12 caracteres alfanuméricos
     * @return string
     */
    public function generarPublicCode() {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789'; // Sin I, O, 0 para evitar confusión
        $intentos = 0;
        $maxIntentos = 10;
        
        do {
            $code = '';
            for ($i = 0; $i < 12; $i++) {
                $code .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
            
            // Verificar que no existe
            if (!$this->existePublicCode($code)) {
                return $code;
            }
            
            $intentos++;
        } while ($intentos < $maxIntentos);
        
        // Fallback con timestamp
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
    }
    
    /**
     * Verifica si un código público ya existe
     * @param string $code
     * @return bool
     */
    private function existePublicCode($code) {
        try {
            $db = $this->conexion->conectar();
            $stmt = $db->prepare("SELECT COUNT(*) FROM participants WHERE public_code = :code");
            $stmt->execute(['code' => $code]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return true; // En caso de error, asumimos que existe para generar otro
        }
    }
    
    /**
 * Crea un nuevo participante
 * @param array $datos Array con: first_name, last_name, career_id, photo_original_path, photo_original_mime, photo_original_sha256, created_by_user_id
 * @return array|false Array con id y public_code, o false si falla
 */
public function crear($datos) {
    try {
        $db = $this->conexion->conectar();
        
        $publicCode = $this->generarPublicCode();
        
        $stmt = $db->prepare("
            INSERT INTO participants (
                public_code, first_name, last_name, phone, career_id,
                photo_original_path, photo_original_mime, photo_original_sha256,
                qr_payload, qr_image_path, created_by_user_id,
                status, created_at, updated_at
            ) VALUES (
                :public_code, :first_name, :last_name, :phone, :career_id,
                :photo_original_path, :photo_original_mime, :photo_original_sha256,
                :qr_payload, :qr_image_path, :created_by_user_id,
                'queued', NOW(), NOW()
            )
        ");
        
        $resultado = $stmt->execute([
            'public_code' => $publicCode,
            'first_name' => $datos['first_name'],
            'last_name' => $datos['last_name'],
            'phone' => $datos['phone'] ?? null,
            'career_id' => $datos['career_id'],
            'photo_original_path' => $datos['photo_original_path'],
            'photo_original_mime' => $datos['photo_original_mime'] ?? null,
            'photo_original_sha256' => $datos['photo_original_sha256'] ?? null,
            'qr_payload' => $datos['qr_payload'] ?? null,
            'qr_image_path' => $datos['qr_image_path'] ?? null,
            'created_by_user_id' => $datos['created_by_user_id']
        ]);
        
        if ($resultado) {
            return [
                'id' => $db->lastInsertId(),
                'public_code' => $publicCode
            ];
        }
        
        return false;
        
    } catch (PDOException $e) {
        error_log('Error creando participante: ' . $e->getMessage());
        return false;
    }
}
    
    /**
     * Actualiza la información del QR de un participante
     * @param int $id ID del participante
     * @param string $qrPayload Payload del QR
     * @param string|null $qrImagePath Ruta de la imagen del QR
     * @return bool
     */
    public function actualizarQR($id, $qrPayload, $qrImagePath) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE participants 
                SET qr_payload = :qr_payload,
                    qr_image_path = :qr_image_path,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'id' => $id,
                'qr_payload' => $qrPayload,
                'qr_image_path' => $qrImagePath
            ]);
            
        } catch (PDOException $e) {
            error_log('Error actualizando QR: ' . $e->getMessage());
            return false;
        }
    }
    

    /**
 * Crea un nuevo participante con un código público específico
 * @param array $datos Array con: public_code, first_name, last_name, career_id, photo_original_path, qr_payload, qr_image_path, created_by_user_id
 * @return array|false Array con id y public_code, o false si falla
 */
public function crearConCodigo($datos) {
    try {
        $db = $this->conexion->conectar();
        
        $stmt = $db->prepare("
            INSERT INTO participants (
                public_code, first_name, last_name, phone, career_id,
                photo_original_path, photo_original_mime, photo_original_sha256,
                qr_payload, qr_image_path, created_by_user_id,
                status, created_at, updated_at
            ) VALUES (
                :public_code, :first_name, :last_name, :phone, :career_id,
                :photo_original_path, :photo_original_mime, :photo_original_sha256,
                :qr_payload, :qr_image_path, :created_by_user_id,
                'queued', NOW(), NOW()
            )
        ");
        
        $resultado = $stmt->execute([
            'public_code' => $datos['public_code'],
            'first_name' => $datos['first_name'],
            'last_name' => $datos['last_name'],
            'phone' => $datos['phone'] ?? null,
            'career_id' => $datos['career_id'],
            'photo_original_path' => $datos['photo_original_path'],
            'photo_original_mime' => $datos['photo_original_mime'] ?? null,
            'photo_original_sha256' => $datos['photo_original_sha256'] ?? null,
            'qr_payload' => $datos['qr_payload'],
            'qr_image_path' => $datos['qr_image_path'] ?? null,
            'created_by_user_id' => $datos['created_by_user_id']
        ]);
        
        if ($resultado) {
            return [
                'id' => $db->lastInsertId(),
                'public_code' => $datos['public_code']
            ];
        }
        
        return false;
        
    } catch (PDOException $e) {
        error_log('Error creando participante con código: ' . $e->getMessage());
        return false;
    }
}


    /**
     * Obtiene un participante por ID con datos de carrera
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT 
                    p.*,
                    c.name as career_name,
                    c.category as career_category,
                    u.username as created_by_username
                FROM participants p
                LEFT JOIN careers c ON p.career_id = c.id
                LEFT JOIN users u ON p.created_by_user_id = u.id
                WHERE p.id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo participante: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un participante por código público
     * @param string $code
     * @return array|false
     */
    public function obtenerPorPublicCode($code) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT 
                    p.*,
                    c.name as career_name,
                    c.category as career_category
                FROM participants p
                LEFT JOIN careers c ON p.career_id = c.id
                WHERE p.public_code = :code
            ");
            
            $stmt->execute(['code' => $code]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo participante: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene participantes en cola para procesar
     * @param int $limit
     * @return array
     */
    public function obtenerEnCola($limit = 5) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT *
                FROM participants
                WHERE status = 'queued'
                ORDER BY created_at ASC
                LIMIT :limit
            ");
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo cola: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marca un participante como en procesamiento
     * @param int $id
     * @return bool
     */
    public function marcarComoProcesando($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE participants
                SET status = 'processing', updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log('Error marcando como procesando: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marca un participante como completado
     * @param int $id
     * @param string $resultPath
     * @param int|null $width
     * @param int|null $height
     * @param string|null $sha256
     * @return bool
     */
    public function marcarComoCompletado($id, $resultPath, $width = null, $height = null, $sha256 = null) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE participants
                SET status = 'done',
                    result_image_path = :result_path,
                    result_image_width = :width,
                    result_image_height = :height,
                    result_image_sha256 = :sha256,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'result_path' => $resultPath,
                'width' => $width,
                'height' => $height,
                'sha256' => $sha256,
                'id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log('Error marcando como completado: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marca un participante con error
     * @param int $id
     * @param string $mensaje
     * @return bool
     */
    public function marcarComoError($id, $mensaje) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE participants
                SET status = 'error',
                    error_message = :mensaje,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute([
                'mensaje' => $mensaje,
                'id' => $id
            ]);
            
        } catch (PDOException $e) {
            error_log('Error marcando error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los últimos participantes completados
     * @param int $limit
     * @param int|null $sinceId Obtener solo IDs mayores a este (para polling)
     * @return array
     */
    public function obtenerUltimosCompletados($limit = 20, $sinceId = null) {
        try {
            $db = $this->conexion->conectar();
            
            $sql = "
                SELECT 
                    p.id,
                    p.first_name,
                    p.last_name,
                    p.result_image_path,
                    p.created_at,
                    c.name as career_name
                FROM participants p
                LEFT JOIN careers c ON p.career_id = c.id
                WHERE p.status = 'done'
            ";
            
            if ($sinceId !== null) {
                $sql .= " AND p.id > :since_id";
            }
            
            // Ordenar por updated_at DESC para ver las que se acaban de generar primero
            $sql .= " ORDER BY p.updated_at DESC LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            
            if ($sinceId !== null) {
                $stmt->bindValue(':since_id', (int)$sinceId, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo completados: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene datos para DataTables con paginación, búsqueda y ordenamiento
     * @param array $params Parámetros de DataTables
     * @return array
     */
    public function obtenerParaDatatables($params) {
        try {
            $db = $this->conexion->conectar();
            
            // Columnas para búsqueda y ordenamiento
            $columns = ['p.id', 'p.created_at', 'p.first_name', 'c.name', 'p.status'];
            
            // Query base
            $baseQuery = "
                FROM participants p
                LEFT JOIN careers c ON p.career_id = c.id
                LEFT JOIN users u ON p.created_by_user_id = u.id
            ";
            
            // Total de registros
            $stmtTotal = $db->query("SELECT COUNT(*) " . $baseQuery);
            $totalRecords = $stmtTotal->fetchColumn();
            
            // Búsqueda
            $searchQuery = "";
            $searchValue = $params['search']['value'] ?? '';
            
            if (!empty($searchValue)) {
                $searchQuery = " WHERE (
                    p.first_name LIKE :search OR
                    p.last_name LIKE :search OR
                    p.phone LIKE :search OR
                    p.public_code LIKE :search OR
                    c.name LIKE :search OR
                    p.status LIKE :search
                )";
            }
            
            // Total filtrado
            if (!empty($searchValue)) {
                $stmtFiltered = $db->prepare("SELECT COUNT(*) " . $baseQuery . $searchQuery);
                $stmtFiltered->execute(['search' => "%$searchValue%"]);
                $filteredRecords = $stmtFiltered->fetchColumn();
            } else {
                $filteredRecords = $totalRecords;
            }
            
            // Ordenamiento
            $orderColumn = $columns[$params['order'][0]['column'] ?? 0] ?? 'p.id';
            $orderDir = ($params['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            
            // Query principal
            $sql = "
                SELECT 
                    p.id,
                    p.public_code,
                    p.first_name,
                    p.last_name,
                    p.phone,
                    p.status,
                    p.result_image_path,
                    p.qr_image_path,
                    p.created_at,
                    c.name as career_name,
                    u.username as created_by_username
                " . $baseQuery . $searchQuery . "
                ORDER BY $orderColumn $orderDir
                LIMIT :start, :length
            ";
            
            $stmt = $db->prepare($sql);
            
            if (!empty($searchValue)) {
                $stmt->bindValue(':search', "%$searchValue%");
            }
            
            $stmt->bindValue(':start', (int)($params['start'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':length', (int)($params['length'] ?? 10), PDO::PARAM_INT);
            
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'draw' => (int)($params['draw'] ?? 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ];
            
        } catch (PDOException $e) {
            error_log('Error en datatables: ' . $e->getMessage());
            return [
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Elimina un participante
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("DELETE FROM participants WHERE id = :id");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log('Error eliminando participante: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reiniciar procesamiento (marcar como queued nuevamente)
     * @param int $id
     * @return bool
     */
    public function reintentarProcesamiento($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE participants
                SET status = 'queued',
                    error_message = NULL,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log('Error reintentando: ' . $e->getMessage());
            return false;
        }
    }
}
