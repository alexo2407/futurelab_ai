<?php

require_once __DIR__ . '/conexion.php';

class RolModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Obtiene todos los roles
     * @return array
     */
    public function obtenerTodos() {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->query("
                SELECT id, name, description
                FROM roles
                ORDER BY name
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo roles: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un rol por nombre
     * @param string $nombre
     * @return array|false
     */
    public function obtenerPorNombre($nombre) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT id, name, description
                FROM roles
                WHERE name = :name
            ");
            
            $stmt->execute(['name' => $nombre]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo rol: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene un rol por ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT id, name, description
                FROM roles
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo rol: ' . $e->getMessage());
            return false;
        }
    }
}
