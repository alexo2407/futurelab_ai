<?php

require_once __DIR__ . '/conexion.php';

class CarreraModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Obtiene todas las carreras activas
     * @return array
     */
    public function obtenerActivas() {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->query("
                SELECT id, name, category, sort_order
                FROM careers
                WHERE is_active = 1
                ORDER BY sort_order ASC, name ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo carreras: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene una carrera por ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT id, name, category, is_active, sort_order,
                       ai_prompt, reference_image_path, reference_image_url
                FROM careers
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo carrera: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todas las carreras (incluidas inactivas)
     * @return array
     */
    public function obtenerTodas() {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->query("
                SELECT id, name, category, is_active, sort_order,
                       ai_prompt, reference_image_path, reference_image_url
                FROM careers
                ORDER BY sort_order ASC, name ASC
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo carreras: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualiza una carrera
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizar($id, $datos) {
        try {
            $db = $this->conexion->conectar();
            
            $campos = [];
            $valores = ['id' => $id];
            
            foreach ($datos as $campo => $valor) {
                $campos[] = "$campo = :$campo";
                $valores[$campo] = $valor;
            }
            
            if (empty($campos)) {
                return false;
            }
            
            $sql = "UPDATE careers SET " . implode(', ', $campos) . " WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            
            return $stmt->execute($valores);
            
        } catch (PDOException $e) {
            error_log('Error actualizando carrera: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crea una nueva carrera
     * @param array $datos
     * @return int|false ID de la carrera creada o false si falla
     */
    public function crear($datos) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                INSERT INTO careers (name, category, is_active, sort_order)
                VALUES (:name, :category, :is_active, :sort_order)
            ");
            
            $resultado = $stmt->execute([
                'name' => $datos['name'],
                'category' => $datos['category'] ?? null,
                'is_active' => $datos['is_active'] ?? 1,
                'sort_order' => $datos['sort_order'] ?? 999
            ]);
            
            if ($resultado) {
                return $db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log('Error creando carrera: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Elimina una carrera
     * @param int $id
     * @return bool
     */
    public function eliminar($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("DELETE FROM careers WHERE id = :id");
            
            return $stmt->execute(['id' => $id]);
            
        } catch (PDOException $e) {
            error_log('Error eliminando carrera: ' . $e->getMessage());
            return false;
        }
    }
}
