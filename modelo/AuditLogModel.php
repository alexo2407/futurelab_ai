<?php

require_once __DIR__ . '/conexion.php';

class AuditLogModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Registra una acción en el log de auditoría
     * @param int|null $userId ID del usuario que realiza la acción (puede ser null para acciones del sistema)
     * @param string $action Acción realizada (ej: 'create', 'update', 'delete', 'login')
     * @param string $entity Entidad afectada (ej: 'participant', 'user')
     * @param int|null $entityId ID de la entidad afectada
     * @param array|null $meta Metadata adicional en formato array (se convertirá a JSON)
     * @return bool
     */
    public function registrar($userId, $action, $entity, $entityId = null, $meta = null) {
        try {
            $db = $this->conexion->conectar();
            
            $metaJson = null;
            if ($meta !== null && is_array($meta)) {
                $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);
            }
            
            $stmt = $db->prepare("
                INSERT INTO audit_log (
                    user_id, action, entity, entity_id, meta, created_at
                ) VALUES (
                    :user_id, :action, :entity, :entity_id, :meta, NOW()
                )
            ");
            
            return $stmt->execute([
                'user_id' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'meta' => $metaJson
            ]);
            
        } catch (PDOException $e) {
            error_log('Error registrando audit log: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene logs de auditoría con filtros
     * @param array $filtros Filtros opcionales: user_id, entity, entity_id, desde, hasta
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function obtener($filtros = [], $limit = 50, $offset = 0) {
        try {
            $db = $this->conexion->conectar();
            
            $where = [];
            $params = [];
            
            if (isset($filtros['user_id'])) {
                $where[] = "user_id = :user_id";
                $params['user_id'] = $filtros['user_id'];
            }
            
            if (isset($filtros['entity'])) {
                $where[] = "entity = :entity";
                $params['entity'] = $filtros['entity'];
            }
            
            if (isset($filtros['entity_id'])) {
                $where[] = "entity_id = :entity_id";
                $params['entity_id'] = $filtros['entity_id'];
            }
            
            if (isset($filtros['desde'])) {
                $where[] = "created_at >= :desde";
                $params['desde'] = $filtros['desde'];
            }
            
            if (isset($filtros['hasta'])) {
                $where[] = "created_at <= :hasta";
                $params['hasta'] = $filtros['hasta'];
            }
            
            $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
            
            $sql = "
                SELECT 
                    a.*,
                    u.username
                FROM audit_log a
                LEFT JOIN users u ON a.user_id = u.id
                $whereClause
                ORDER BY a.created_at DESC
                LIMIT :limit OFFSET :offset
            ";
            
            $stmt = $db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo audit logs: ' . $e->getMessage());
            return [];
        }
    }
}
