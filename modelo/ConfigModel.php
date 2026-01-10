<?php

require_once __DIR__ . '/conexion.php';

class ConfigModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Obtiene una configuración por su clave
     * @param string $key
     * @return string|null
     */
    public function get($key) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT config_value 
                FROM system_config 
                WHERE config_key = :key
            ");
            
            $stmt->execute(['key' => $key]);
            $result = $stmt->fetchColumn();
            
            return $result !== false ? $result : null;
            
        } catch (PDOException $e) {
            error_log('Error obteniendo configuración: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Guarda o actualiza una configuración
     * @param string $key
     * @param string $value
     * @param int|null $userId
     * @return bool
     */
    public function set($key, $value, $userId = null) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                INSERT INTO system_config (config_key, config_value, updated_by, updated_at)
                VALUES (:key, :value, :user_id, NOW())
                ON DUPLICATE KEY UPDATE 
                    config_value = :value,
                    updated_by = :user_id,
                    updated_at = NOW()
            ");
            
            return $stmt->execute([
                'key' => $key,
                'value' => $value,
                'user_id' => $userId
            ]);
            
        } catch (PDOException $e) {
            error_log('Error guardando configuración: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todas las configuraciones de un grupo
     * @param string $group
     * @return array
     */
    public function getByGroup($group) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT config_key, config_value, description
                FROM system_config 
                WHERE config_group = :group
                ORDER BY config_key
            ");
            
            $stmt->execute(['group' => $group]);
            
            $configs = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $configs[$row['config_key']] = [
                    'value' => $row['config_value'],
                    'description' => $row['description']
                ];
            }
            
            return $configs;
            
        } catch (PDOException $e) {
            error_log('Error obteniendo configuraciones por grupo: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene todas las configuraciones
     * @return array
     */
    public function getAll() {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->query("
                SELECT config_key, config_value, config_group, description
                FROM system_config
                ORDER BY config_group, config_key
            ");
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log('Error obteniendo todas las configuraciones: ' . $e->getMessage());
            return [];
        }
    }
}
