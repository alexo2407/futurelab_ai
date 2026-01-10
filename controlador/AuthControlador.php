<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../modelo/UsuarioModel.php';
require_once __DIR__ . '/../modelo/AuditLogModel.php';

class AuthControlador {
    
    private $usuarioModel;
    private $auditModel;
    
    public function __construct() {
        $this->usuarioModel = new UsuarioModel();
        $this->auditModel = new AuditLogModel();
    }
    
    /**
     * Muestra el formulario de login
     */
    public function mostrarLogin() {
        // Si ya está logueado, redirigir al dashboard
        if (isLoggedIn()) {
            $this->redirigirSegunRol();
            return;
        }
        
        include __DIR__ . '/../vista/login.php';
    }
    
    /**
     * Procesa el formulario de login
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validar campos
        if (empty($username) || empty($password)) {
            $_SESSION['error_login'] = 'Por favor ingresa usuario y contraseña';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Intentar autenticar
        $user = $this->usuarioModel->autenticar($username, $password);
        
        if (!$user) {
            $_SESSION['error_login'] = 'Usuario o contraseña incorrectos';
            
            // Log de intento fallido
            $this->auditModel->registrar(
                null,
                'login_failed',
                'user',
                null,
                ['username' => $username, 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
            );
            
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Login exitoso
        loginUser($user);
        
        // Actualizar último login
        $this->usuarioModel->actualizarUltimoLogin($user['id']);
        
        // Log de login exitoso
        $this->auditModel->registrar(
            $user['id'],
            'login',
            'user',
            $user['id'],
            ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );
        
        // Redirigir según rol
        $this->redirigirSegunRol();
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            $this->auditModel->registrar(
                $userId,
                'logout',
                'user',
                $userId,
                []
            );
        }
        
        logoutUser();
        
        $_SESSION['success_message'] = 'Has cerrado sesión correctamente';
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    /**
     * Redirige al usuario según su rol
     */
    private function redirigirSegunRol() {
        // Verificar si hay una URL de redirección guardada
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
        
        // Redirigir según rol
        if (userHasAnyRole(['admin', 'operator'])) {
            header('Location: ' . BASE_URL . '/admin/generate');
        } else {
            header('Location: ' . BASE_URL . '/wall');
        }
        exit;
    }
}
