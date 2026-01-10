<?php

require_once __DIR__ . '/conexion.php';

class UsuarioModel {
    
    private $conexion;
    
    public function __construct() {
        $this->conexion = new Conexion();
    }
    
    /**
     * Autentica un usuario con username y password
     * @param string $username
     * @param string $password
     * @return array|false Datos del usuario con roles o false si falla
     */
    public function autenticar($username, $password) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT id, username, password_hash, is_active 
                FROM users 
                WHERE username = :username
            ");
            
            $stmt->execute(['username' => trim($username)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            if (!$user['is_active']) {
                return false;
            }
            
            // Verificar password
            if (!password_verify($password, $user['password_hash'])) {
                return false;
            }
            
            // Obtener roles del usuario
            $roles = $this->obtenerRoles($user['id']);
            
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'roles' => $roles
            ];
            
        } catch (PDOException $e) {
            error_log('Error en autenticación: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene los roles de un usuario
     * @param int $userId
     * @return array Array de nombres de roles
     */
    public function obtenerRoles($userId) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT r.name
                FROM roles r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id
            ");
            
            $stmt->execute(['user_id' => $userId]);
            $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            return $roles ?: [];
            
        } catch (PDOException $e) {
            error_log('Error obteniendo roles: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un usuario por su ID
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                SELECT id, username, is_active, last_login_at, created_at
                FROM users 
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $user['roles'] = $this->obtenerRoles($user['id']);
            }
            
            return $user;
            
        } catch (PDOException $e) {
            error_log('Error obteniendo usuario: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualiza la fecha de último login
     * @param int $userId
     * @return bool
     */
    public function actualizarUltimoLogin($userId) {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->prepare("
                UPDATE users 
                SET last_login_at = NOW()
                WHERE id = :id
            ");
            
            return $stmt->execute(['id' => $userId]);
            
        } catch (PDOException $e) {
            error_log('Error actualizando último login: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene todos los usuarios
     * @return array
     */
    public function obtenerTodos() {
        try {
            $db = $this->conexion->conectar();
            
            $stmt = $db->query("
                SELECT id, username, is_active, last_login_at, created_at
                FROM users
                ORDER BY username
            ");
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar roles a cada usuario
            foreach ($users as &$user) {
                $user['roles'] = $this->obtenerRoles($user['id']);
            }
            
            return $users;
            
        } catch (PDOException $e) {
            error_log('Error obteniendo usuarios: ' . $e->getMessage());
            return [];
        }
    }
}
